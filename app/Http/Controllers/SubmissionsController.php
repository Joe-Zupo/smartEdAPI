<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use Illuminate\Http\Request;
use App\Http\Resources\SubmissionResource;
use App\Models\AcademicYear;
use App\Http\Requests\Submissions\IndexSubmissionsRequest;
use App\Http\Requests\Submissions\StoreSubmissionsRequest;
use Illuminate\Support\Facades\DB;
use App\Models\SchoolInformationDraft;
use App\Models\EnrollmentData;
use App\Models\SchoolType;
use App\Models\ResourceData;
use App\Models\GradeLevel;

class SubmissionsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexSubmissionsRequest $request)
    {
        $request->validated();

        $user = $request->user();
        $perPage = $request['per_page'] ?? 5;
        $sortBy = $request['sortBy'] ?? 'id';
        $sortOrder = $request['sortOrder'] ?? 'asc';
        $getAll = $request['all'] ?? false;

        $searchRequest = $request->input('search');

        $academicYear = AcademicYear::query()->where('status', 'default')->first();


        $query = Submission::with(['school','academicYear','user',])->where('academic_year_id', $academicYear->id);

        if($request->filled('type')){
            $query->where('type', $request['type']);
        }

        if($request->filled('status')){
            $query->where('status', $request['status']);
        }

        if($user->school_id){
            $query->where('school_id', $user->school_id);
        }

        if($request->filled('search')){
            $query
                ->where('submission_number', $searchRequest)
                ->orWhereHas('school', function ($q) use ($searchRequest){
                    $q->where('school_name', $searchRequest);
                });
            }
        $countQuery = Submission::query()->where('academic_year_id', $academicYear->id);


        if ($user->school_id) {
            $countQuery->where('school_id', $user->school_id);
        }
        
        $items = $getAll
            ? $query->get()
            : $query->paginate($perPage)->appends($request->query());

        return $this->success('Submissions retrieved successfully', [
                'data' => [
                    'counts' => [
                        'submissions' => (clone $countQuery)->count(),
                        'approved' => (clone $countQuery)->where('status', 'approved')->count(),
                        'pending' => (clone $countQuery)->where('status', 'pending')->count(),
                        'returned' => (clone $countQuery)->where('status', 'returned')->count(),
                    ],
                    'submissions' => SubmissionResource::collection($items),
                ],
                'pagination' => $getAll ? null : $this->paginateReturn($items),
            ]
        );
    }
    /**
     * Store a new submission
     */
    public function store(StoreSubmissionsRequest $request)
    {
        $validated = $request->validated();
        $user = $request->user();

        if (!$user->school_id) {
            return response()->json(['message' => 'User is not assigned to a school.'], 403);
        }

        $defaultYear = AcademicYear::where('status', 'default')->first();

        if (!$defaultYear) {
            return response()->json(['message' => 'No default academic year found.'], 422);
        }

        $query = Submission::where('school_id', $user->school_id)
            ->where('user_id', $user->id)
            ->where('academic_year_id', $defaultYear->id)
            ->where('type', $validated['type']);

        if ($validated['type'] === 'information') {
            // Only block if there is a submission still in workflow
            $exists = $query->whereIn('status', ['pending', 'returned'])->exists();
        } else {

            // Other types: only one per year no matter the status
            $exists = $query->exists();
        }

        if ($exists) {
            return $this->error("A {$validated['type']} submission is still under review or needs revision.", 409);
        }

        $gradeMap = [];
        if ($validated['type'] === 'enrollment') {

            $schoolType = $user->school->schoolType?->name;

            // ✅ Allowed grades per school type
            $allowedGrades = match ($schoolType) {
                'Elementary' => [
                    'Kinder','Grade 1','Grade 2','Grade 3','Grade 4','Grade 5','Grade 6',
                ],

                'Junior High School' => [
                    'Grade 7','Grade 8','Grade 9','Grade 10',
                ],

                'Standalone SHS' => [
                    'Grade 11','Grade 12',
                ],

                'Integrated School',
                'Science High School',
                'ALS',
                'Junior High School with SHS' => [
                    'Kinder','Grade 1','Grade 2','Grade 3','Grade 4','Grade 5','Grade 6','Grade 7','Grade 8','Grade 9','Grade 10','Grade 11','Grade 12',
                ],

                default => [],
            };

            // ✅ Get input grades
            $inputGrades = collect($validated['details'])->pluck('grade_level')->toArray();

            // ❌ Check invalid grades
            $invalidGrades = array_diff($inputGrades, $allowedGrades);

            if (!empty($invalidGrades)) {
                return $this->error('Invalid grade levels for this school type', 422, ['invalid_grades' => array_values($invalidGrades)]);
            }
            $missingGrades = array_diff($allowedGrades, $inputGrades);

            if (!empty($missingGrades)) {
                return $this->error(['details' => ['Missing required grade levels: '. implode(', ', $missingGrades)]]);
            }

            // ✅ Map only allowed grades
            $gradeMap = GradeLevel::whereIn('name', $allowedGrades)->pluck('id', 'name');
        }

        if ($validated['type'] === 'resource'){
            $reqRes = ['Classrooms','Teachers','Seats','Learning Materials'];
                $inputResources = collect($validated['details'])->pluck('resource_name')->toArray();
                $invalidResources = array_diff($inputResources, $reqRes);
                $missingResources = array_diff($reqRes, $inputResources);

                $errors = [];

                if (!empty($missingResources)) {
                    $errors[] =
                        'Missing required resources: '
                        . implode(', ', $missingResources);
                }

                if (!empty($invalidResources)) {
                    $errors[] =
                        'Invalid resources: '
                        . implode(', ', $invalidResources);
                }

                if (!empty($errors)) {
                    return $this->error(['details' => $errors]);
                }
        }

        $submission = DB::transaction(function () use ($validated, $user, $defaultYear, $gradeMap, $request) {
            $submission = Submission::create([
                'user_id' => $user->id,
                'school_id' => $user->school_id,
                'academic_year_id' => $defaultYear->id,
                'type' => $validated['type'],
                'status' => 'pending',
            ]);

            if ($validated['type'] === 'enrollment') {
                $details = [];
                foreach ($validated['details'] as $row) {
                    $details[] = [
                        'submission_id' => $submission->id,
                        'grade_level' => $row['grade_level'] ?? null,
                        'male_count' => $row['male_count'],
                        'female_count' => $row['female_count'],
                        
                    ];
                }
                EnrollmentData::insert($details);


            } elseif ($validated['type'] === 'resource') {
                $details = [];
                foreach ($validated['details'] as $row) {
                    $details[] = [
                        'submission_id' => $submission->id,
                        'resource_name' => $row['resource_name'],
                        'inventory' => $row['inventory'],
                        'requirement' => $row['requirement'],
                    ];
                }
                ResourceData::insert($details);


            } elseif ($validated['type'] === 'information') {

                $info = $validated['details'][0];

                $school_type = SchoolType::where('name', $info['school_type'])->first();
                $info['school_type_id'] = $school_type->id ?? null;

                $draft = SchoolInformationDraft::create([
                    'submission_id' => $submission->id,
                    'school_id' => $user->school_id,
                    'name' => $info['name'] ?? null,
                    'code' => $info['code'] ?? null,
                    'year_established' => $info['year_established'] ?? null,
                    'school_type_id' => $info['school_type_id'] ?? null,
                    'address' => $info['address'] ?? null,
                    'district' => $info['district'] ?? null,
                    'latitude' => $info['latitude'] ?? null,
                    'longitude' => $info['longitude'] ?? null,

                ]);

                if ($request->hasFile('details.0.image')) {

                    $path = upload_image($request, 'details.0.image', 'school_images');

                    $draft->image = $path;
                    $draft->save();
                }
            }

            return $submission;
        });


        // $submission->notifications()->create([
        //     'title' => "New Submission from {$submission->school->name}",
        //     'message' => ucfirst($submission->type) . " data for {$submission->academicYear->name} has been submitted by {$submission->user->name} and requires validation.",
        // ]);

        // $submission->notifications()->create([
        //     'title' => "Pending Review",
        //     'message' => "Your submission {$submission->submission_number} has been successfully submitted and is awaiting approval.",
        // ]);

        activity('Submitted Data')
            ->causedBy($user)
            ->performedOn($submission)
            ->withProperties([
                'datetime' => now()->format('Y-m-d h:i:s A'),
            ])
            ->log("{$user->name} submitted {$submission->type} data for {$submission->school->name}.");

        $submission->load([
            'enrollmentData.gradeLevel',
            'resourceData',
            'school',
            'academicYear',
            'user',
            'schoolInformationDraft',
        ]);

        return response()->json([
            'message' => 'Submission created successfully',
            'data' => new SubmissionResource($submission),
        ], 201);
    }

    

    /**
     * Fetch a submission
     */
    public function show($id)
    {
        $submission = Submission::find($id);
        $defaultYear = AcademicYear::where('status', 'default')->first();

        if(!$submission){
            return $this->error('Submission not found', 404);
        }
        if (!$defaultYear || $submission->academic_year_id !== $defaultYear->id) {
            return $this->error('You can only view submissions for the current default school year.', 403);
        }

        $submission->load([
            'enrollmentData.gradeLevel',
            'resourceData',
            'school',
            'academicYear',
            'user',
            'comments',
            'schoolInformationDraft',
        ]);

        return $this->success('Submission retrieved successfully', [
            'data' => new SubmissionResource($submission)
            ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Submissions $submissions)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Submissions $submissions)
    {
        //
    }
}
