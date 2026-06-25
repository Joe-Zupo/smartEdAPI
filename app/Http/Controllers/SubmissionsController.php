<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use Illuminate\Http\Request;
use App\Http\Resources\SubmissionResource;
use App\Models\AcademicYear;
use App\Http\Requests\Submissions\IndexSubmissionsRequest;
use App\Http\Requests\Submissions\StoreSubmissionsRequest;
use App\Http\Requests\Submissions\UpdateSubmissionsRequest;
use Illuminate\Support\Facades\DB;
use App\Models\SchoolInformationDraft;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\School;
use App\Models\EnrollmentData;
use App\Models\EnrollmentDataDraft;
use App\Models\SchoolType;
use App\Models\ResourceData;
use App\Models\ResourceDataDraft;
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

        if($user->school_id){ //Restricts School Accounts from searching other schools
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

        $defaultYear = AcademicYear::query()->where('status', 'default')->first();

        if (!$defaultYear) {
            return response()->json(['message' => 'No default academic year found.'], 422);
        }

        $query = Submission::query()->where('school_id', $user->school_id)
            ->where('user_id', $user->id)
            ->where('academic_year_id', $defaultYear->id)
            ->where('type', $validated['type']);

        //CAN BE REVISED
        $exists = false;
        if ($validated['type'] === 'information') {
            // Only block if there is a submission still in workflow
            $exists = $query->whereIn('status', ['pending', 'returned'])->exists();
        }
        if ($exists) {
            return $this->error("A {$validated['type']} submission is still under review or needs revision.", 409);
        }else if ($query->exists()){
            return $this->error("A {$validated['type']} submission already exists. You can only have one submission for this type, once per year.", 409); // Still needs to be asked, for now stick to this temporarily
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
                EnrollmentDataDraft::insert($details);


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
                ResourceDataDraft::insert($details);


            } elseif ($validated['type'] === 'information') {

                $info = $validated['details'][0];

                $school_type = SchoolType::query()->where('name', $info['school_type'])->first();
                $info['school_type_id'] = $school_type->id ?? null;

                $info['address'] = implode(', ', [
                    $info['street'],
                    $info['barangay'],
                    $info['city'],
                    $info['province'],
                ]);
                $draft = SchoolInformationDraft::create([
                    'submission_id' => $submission->id,
                    'school_id' => $user->school_id,
                    'school_name' => $info['school_name'] ?? null,
                    'school_code' => $info['school_code'] ?? null,
                    'year_established' => $info['year_established'] ?? null,
                    'school_type_id' => $info['school_type_id'] ?? null,
                    'address' => $info['address'] ?? null,
                    'district' => $info['district'] ?? null,
                    'latitude' => $info['latitude'] ?? null,
                    'longitude' => $info['longitude'] ?? null,

                ]);

                if ($request->hasFile('details.0.image')) {

                    $path = $request->file('image')->store('school_images', 'public');

                    $draft->image = $path;
                    $draft->save();
                }
            }

            return $submission;
        });


        $submission->notifications()->create([
            'title' => "New Submission from {$submission->school->school_name}",
            'message' => ucfirst($submission->type) . " data for {$submission->academicYear->name} has been submitted by {$submission->user->name} and requires validation.",
        ]);

        $submission->notifications()->create([
            'title' => "Pending Review",
            'message' => "Your submission {$submission->submission_number} has been successfully submitted and is awaiting approval.",
        ]);

        activity('Submitted Data')
            ->causedBy($user)
            ->performedOn($submission)
            ->withProperties([
                'datetime' => now()->format('Y-m-d h:i:s A'),
            ])
            ->log("{$user->name} submitted {$submission->type} data for {$submission->school->school_name}.");

        $submission->load([
            'enrollmentDraft.gradeLevel',
            'resourceDraft',
            'school',
            'academicYear',
            'user',
            'schoolInformationDraft',
        ]);

        return $this->success('Submission created successfully', ['data' => new SubmissionResource($submission)]);
    }

    

    /**
     * Fetch a submission
     */
    public function show($id)
    {
        $submission = Submission::find($id);
        $defaultYear = AcademicYear::query()->where('status', 'default')->first();

        if(!$submission){
            return $this->error('Submission not found', 404);
        }
        if (!$defaultYear || $submission->academic_year_id !== $defaultYear->id) {
            return $this->error('You can only view submissions for the current default school year.', 403);
        }

        $submission->load([
            'enrollmentDraft.gradeLevel',
            'resourceDraft',
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
     * Approve Submission
     * 
     * Approve the submission.
     */
    public function approve(Request $request, Submission $submission)
    {
            if ($submission->academicYear->status !== 'default') {
                return $this->error('You can only approve submissions for the current default school year.', 403);
            }
            if($submission->editable === false){
                return $this->error('You can only approve submissions that have edit access.', 403);
            }

            DB::transaction(function () use ($submission, $request) {

            if($submission->status === 'pending'){
                if($submission->editable === true){
                        $submission->update([
                        'status' => 'approved',
                        'editable' => false,
                    ]);

                    $submission->notifications()->create([
                        'title' => 'Approved Submission',
                        'message' => "Your submission for {$submission->submission_number} has been approved.",
                        'is_read' => false,
                    ]);

                    $actor = $request->user();
                    if ($actor) {
                        activity('Approved Data')
                            ->causedBy($actor)
                            ->performedOn($submission)
                            ->withProperties([
                                'datetime' => now()->format('Y-m-d h:i:s A'),
                            ])
                            ->log($actor->name . ' has approved submission ' . $submission->submission_number . '.');
                        }
                    }
                }
            });

         $submission->load([
                        'school',
                        'academicYear',
                        'user',
                    ]);

        return $this->success('Submission approved successfully', ['data' => new SubmissionResource($submission)]);
    }

    /**
     * Return Submission Request
     * 
     * Returns submissions that only have the pending status and are still editable (prior to being approved)
     * 
     */
     public function return(Request $request, Submission $submission)
    {
        if ($submission->academicYear->status !== 'default') {
            return $this->error('You can only return submissions for the current default school year.');
        }

        if($submission->status !== 'pending'){
            return $this->error('Submissions must be pending to be returned.', 403);
        }

        if($submission->status === 'pending' && $submission->editable === false){
            return $this->error('Wrong function used. Cannot return submission for edit request with this function.', 403);
        }

        $validated = $request->validate([
                'comment' => ['required', 'string', 'max:1000'],
        ]);      

        DB::transaction(function () use ($submission, $request, $validated) {

            if($submission->status === 'pending' && $submission->editable === true){
                $submission->update([
                    'status' => 'returned',
                ]);

                $submission->comments()->create([
                    'submission_id' => $submission->id,
                    'user_id' => $request->user()->id,
                    'comment' => $validated['comment'],
                ]);

                $comment = rtrim($validated['comment'], '.');

                $submission->notifications()->create([
                    'title' => 'Submission Returned',
                    'message' => "Your submission for {$submission->submission_number} has been returned. Reason: {$comment} Please review and resubmit.",
                    'is_read' => false,
                ]);

                $actor = $request->user();
                if ($actor) {
                    activity('Returned Data')
                        ->causedBy($actor)
                        ->performedOn($submission)
                        ->withProperties([
                            'datetime' => now()->format('Y-m-d h:i:s A'),
                        ])
                        ->log($actor->name . ' has returned submission ' . $submission->submission_number . '.');
                }

            }
        });

        $submission->load([
                'school',
                'academicYear',
                'user',
                'comments.user',
            ]);

                return $this->success('Submission returned successfully', ['data' => new SubmissionResource($submission)]);

    }



    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSubmissionsRequest $request, Submission $submission)
    {
        $user = $request->user();
        if ($submission->user_id !== $user->id || $submission->school_id !== $user->school_id) {
            return $this->error(['message' => 'Unauthorized to update this submission.'], 403);
        }

        if ($submission->status === 'approved') {
            return $this->error(['Only returned submissions can be edited, Please request for edit access.'], 403);
            }
       if ($submission->status === 'pending') {
                return $this->error(['message' => 'Only returned submissions can be edited.'], 403);
            }

        $validated = $request->validated();

        DB::beginTransaction();
        $school = School::query()->where('id', $user->school_id)->first();
        if($submission->status === 'returned'){
                if ($submission->type === 'enrollment') {

                    foreach ($validated['details'] as $row) {

                        if (
                            !isset($row['grade_level']) ||
                            !isset($row['male_count']) ||
                            !isset($row['female_count'])
                        ) {
                            continue;
                        }

                        $submission->enrollmentDraft()
                            ->where('grade_level', $row['grade_level'])
                            ->update([
                                'male_count' => $row['male_count'],
                                'female_count' => $row['female_count'],
                            ]);
                    }
                }
                elseif ($submission->type === 'resource') {

                    foreach ($validated['details'] as $row) {

                        if (
                            !isset($row['resource_name']) ||
                            !isset($row['inventory']) ||
                            !isset($row['requirement'])
                        ) { continue; }

                        $submission->resourceDraft()
                            ->where('resource_name', $row['resource_name'])
                            ->update([
                                'inventory' => $row['inventory'],
                                'requirement' => $row['requirement'],
                            ]);
                    }
                } 

                elseif ($submission->type === 'information') {

                    $draft = $submission->schoolInformationDraft;

                    if (!$draft) {
                        throw new \Exception('Information draft not found.');
                    }

                    $info = $validated['details'][0] ?? [];

                    $schoolType = null;

                    if (!empty($info['school_type'])) {
                        $schoolType = SchoolType::query()->where('name', $info['school_type'])->first();
                    }

                    //address appending
                        $address = explode(', ', $draft->address);

                        $street = $info['street'] ?? ($address[0] ?? '');
                        $barangay = $info['barangay'] ?? ($address[1] ?? '');
                        $city = $info['city'] ?? ($address[2] ?? '');
                        $province = $info['province'] ?? ($address[3] ?? '');

                        $info['address'] = implode(', ', [
                            $street,
                            $barangay,
                            $city,
                            $province,
                        ]);

                    $draft->update([
                        'school_name' => $info['school_name'] ?? $draft->school_name,
                        'school_code' => $info['school_code'] ?? $draft->school_code,
                        'year_established' => $info['year_established'] ?? $draft->year_established,
                        'school_type_id' => $schoolType?->id ?? $draft->school_type_id,
                        'address' => $info['address'] ?? $draft->address,
                        'district' => $info['district'] ?? $draft->district,
                        'latitude' => $info['latitude'] ?? $draft->latitude,
                        'longitude' => $info['longitude'] ?? $draft->longitude,
                    ]);
                    $path = $draft->image;
                    if ($request->hasFile('details.0.image')) {
                        if ($info->image && Storage::disk('public')->exists($school->image)) {
                                Storage::disk('public')->delete($school->image);
                        }
                            $path = $request->file('image')->store('school_images', 'public');
                        }

                        if ($path) {
                            $draft->image = $path ?? $draft->image;
                        }
                        $draft->save();
                }

                    $submission->update(['status' => 'pending']);
                    $submission->touch();

                    $submission->notifications()->create([
                    'title' => "New Submission from {$submission->school->school_name}",
                    'message' => ucfirst($submission->type) . " data for {$submission->academicYear->academic_year} has been resubmitted by {$submission->user->name} and requires validation.",
                    ]);

                    $submission->notifications()->create([
                        'title' => "Pending Review",
                        'message' => "Your submission {$submission->submission_number} has been successfully resubmitted and is awaiting approval.",
                    ]);

                    activity('Resubmitted Data')
                    ->causedBy($user)
                    ->performedOn($submission)
                    ->withProperties([
                        'datetime' => now()->format('Y-m-d h:i:s A'),
                    ])
                    ->log("{$user->name} resubmitted {$submission->type} data for {$submission->school->school_name}.");

                    $submission->load(['enrollmentDraft.gradeLevel', 'resourceDraft', 'school', 'academicYear', 'comments', 'schoolInformationDraft',]);

                    DB::commit();
                    return $this->success('Submission updated successfully', ['data' => new SubmissionResource($submission)]);

                }
        }
    /**
     * Send Edit Request for submission
     * 
     * Sends a request to gain edit access again (sent by an assigned school account), 
     * Turns Request status into pending but editable is still false
     * 
     */
    public function requestEdit(Request $request , Submission $submission){
        $user = $request->user();

        if(!$user->hasRole('School Account')){
            return $this->error('Submission cannot be accessed by user, User is not a school account.', 403);
        }
        if($user->school_id !== $submission->school->id){
            return $this->error('Submission cannot be accessed by user, User is not under the school of the submission', 403);
        }

        DB::beginTransaction();

        if($submission->status !== 'approved'){
            return $this->error('Cannot request for edit access for unapproved submission.', 403);
            DB::rollBack();
        }

        if($submission->status === 'approved'){
            $submission->update(['status' => 'pending']);

        $submission->notifications()->create([
            'title' => "New Submission from {$submission->school->school_name}",
            'message' => ucfirst($submission->type) . " data for {$submission->academicYear->academic_year} has been submitted by {$submission->user->name} and requires validation.",
        ]);

        $submission->notifications()->create([
            'title' => "Pending Request for Edit Access",
            'message' => "Your submission for edit access {$submission->submission_number} has been successfully submitted and is awaiting approval.",
        ]);

        activity('Edit Request')
            ->causedBy($user)
            ->performedOn($submission)
            ->withProperties([
                'datetime' => now()->format('Y-m-d h:i:s A'),
            ])
            ->log("{$user->name} submitted request to edit {$submission->type} data for {$submission->school->school_name}.");

            DB::commit();

            return $this->success('Request for edit access for this submission has been submitted, please await for approval.', ['data' => new SubmissionResource($submission)]);
        }
    }

    
    /**
     * Approve Edit Request
     * 
     * Returns submissions that have the pending status AND are not editable (after being approved)
     * 
     */
    public function approveRequest(Request $request, Submission $submission){
        $user = $request->user();

        if($user->hasRole('School Account')){
            return $this->error('Submission cannot be approved by user, User must be an admin', 403);
        }
        
        if ($submission->academicYear->status !== 'default') {
                return $this->error('You can only return submissions for the current default school year.');
        }
        if($submission->status !== 'pending' || $submission->editable !== false){
                return $this->error('Cannot approve submission for it is not a edit request.', 403);
        }

        DB::beginTransaction();
            if($submission->status === 'pending' && $submission->editable === false){

                    $submission->update([
                        'status' => 'returned',
                        'editable' => true,
                    ]);

                    $submission->notifications()->create([
                        'title' => 'Submission Returned',
                        'message' => "Your submission for {$submission->submission_number}, for the request of edit access has been returned. Please review and resubmit.",
                        'is_read' => false,
                    ]);

                    $actor = $request->user();
                    if ($actor) {
                        activity('Returned Data')
                            ->causedBy($actor)
                            ->performedOn($submission)
                            ->withProperties([
                                'datetime' => now()->format('Y-m-d h:i:s A'),
                            ])
                            ->log($actor->name . ' has returned submission ' . $submission->submission_number . '.');
                    }
            }
        DB::commit();

        $submission->load([
                    'school',
                    'academicYear',
                    'user',
                    'comments.user',
                ]);

        return $this->success('Submission returned successfully! Edit access has been restored.', ['data' => new SubmissionResource($submission)]);
        
    }

    public function declineRequest(Request $request, Submission $submission){
        $user = $request->user();

        if($user->hasRole('School Account')){
            return $this->error('Submission cannot be approved by user, User must be an admin', 403);
        }
        if($submission->status !== 'pending' || $submission->editable !== false){
            return $this->error('Wrong function used. Cannot return decline request for submission is not a pending edit request', 403);
        }

        DB::transaction(function () use ($submission, $request) {

                        $submission->update([
                        'status' => 'approved',
                        'editable' => false,
                    ]);

                    $submission->notifications()->create([
                        'title' => 'Declined Edit Request',
                        'message' => "Your submission for {$submission->submission_number} to request for edit access has been declined.",
                        'is_read' => false,
                    ]);

                    $actor = $request->user();
                    if ($actor) {
                        activity('Decline Edit Request')
                            ->causedBy($actor)
                            ->performedOn($submission)
                            ->withProperties([
                                'datetime' => now()->format('Y-m-d h:i:s A'),
                            ])
                            ->log($actor->name . ' has approved submission ' . $submission->submission_number . '.');
                        }
            });

         $submission->load([
                        'school',
                        'academicYear',
                        'user',
                    ]);

        return $this->success('Edit request declined successfully', ['data' => new SubmissionResource($submission)]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Submission $submissions)
    {
        //
    }
}
