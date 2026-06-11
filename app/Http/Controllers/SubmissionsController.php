<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use Illuminate\Http\Request;
use App\Http\Resources\SubmissionResource;
use App\Models\AcademicYear;
use App\Http\Requests\Submissions\IndexSubmissionsRequest;

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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Submission $submission)
    {
        $defaultYear = AcademicYear::where('status', 'default')->first();

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
     * Show the form for editing the specified resource.
     */
    public function edit(Submissions $submissions)
    {
        //
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
