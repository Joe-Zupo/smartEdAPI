<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResourceData\IndexResourceRequest;
use App\Http\Resources\ResourceDataResource;
use App\Models\AcademicYear;
use App\Models\ResourceData;
use App\Models\Submission;
use App\Models\School;
use Illuminate\Validation\Rule;
use App\Models\EnrollmentData;
use App\Models\DivisionLeadership;
use App\Http\Resources\DivisionLeadershipResource;
use Illuminate\Http\Request;

class ResourceDataController extends Controller
{
    /**
     * Index Resource Data
     */
    public function index(IndexResourceRequest $request)
    {
        $request->validated();

        $perPage = $request['per_page'] ?? 5;
        $sortBy = $request['sortBy'] ?? 'id';
        $sortOrder = $request['sortOrder'] ?? 'desc';
        $getAll = $request->boolean('all') ?? false;

        $user = $request->user();
        if ($user->hasRole('School Account')){
            if($request->filled('school_name')){
                $schoolName = School::query()->where('id', $user->school_id)->value('school_name');
                if ($schoolName !== $request->school_name)
                    return $this->error('This School Account can only access data of the school they are under.');
            }else{
                $schoolName = School::query()->where('id', $user->school_id)->value('school_name');
                $request['school_name'] = $schoolName;
            }
        }

         if ($request->filled('academic_year')){
            $academicYear = AcademicYear::query()->where('academic_year', $request['academic_year'])->first();
         }else{
            $academicYear = AcademicYear::query()->where('status', 'default')->first();
         }

        if(!$academicYear){
            return $this->error('Academic Year Not Found', 404);
        } 

        //Base Query
        $query = ResourceData::query()->where('academic_year_id', $academicYear->id);
            if($request->has('school_name')){
                $school = School::query()->where('school_name', $request['school_name'])->first();
                $query->where('school_id', $school->id);
            }

        //Totals Query [testing]
        $totalsQuery = ResourceData::query()->where('academic_year_id', $academicYear->id);

            if ($request->filled('school_name')) {
                $schoolName = $request->input('school_name');
                $schoolID = School::query()->where('school_name', $schoolName)->value('id');
                $totalsQuery->where('school_id', $schoolID);
            }

        $totals = $totalsQuery->selectRaw('
            resource_name,
            SUM(inventory) as total_inventory,
            SUM(requirement) as total_requirement,
            SUM(need) as total_need
        ')
            ->groupBy('resource_name')
            ->get()
            ->map(function ($item) {
                return [
                    'resource_name' => $item->resource_name,
                    'inventory' => (int) $item->total_inventory,
                    'requirement' => (int) $item->total_requirement,
                    'need' => (int) $item->total_need,
                ];
            });

        if ($sortBy) {

            $allowedSorts = [
                'id',
                'resource_name',
                'updated_at',
                'created_at',
            ];
            if (!in_array($sortBy, $allowedSorts)) {
                $sortBy = 'updated_at';
            }
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('updated_at', 'desc');
        }

        $resources = $getAll ? $query->get() : $query->paginate($perPage)->appends($request->query());

        if (!$request->input('school_name')) {
            return $this->success('Resource data retrieved successfully',[
                'data' => [
                    'academic_year' => [
                        'id' => $academicYear->id,
                        'name' => $academicYear->academic_year,
                    ],
                    'items' => ResourceDataResource::collection($resources),
                    'totals_by_resource' => $totals,
                ],
                'pagination' => $getAll ? null : $this->paginateReturn($resources)
            ]);
        } else {
            return $this->success('Resource data retrieved successfully',[
                'data' => [
                    'academic_year' => [
                        'id' => $academicYear->id,
                        'name' => $academicYear->academic_year,
                    ],
                    'items' => ResourceDataResource::collection($resources),
                    'totals_by_resource' => $totals,
                ],
                'pagination' => $getAll ? null : $this->paginateReturn($resources)
            ]);
        }

    }

    /**
     * Public Index Resource Data
     */
    public function publicIndex(Request $request)
    {
        $academicYear = AcademicYear::query()->where('status', 'default')->first();

        if (!$academicYear) {
            return response()->json(['message' => 'Academic year not found'], 404);
        }

        $filterPosition = $request->validate(['position' => Rule::in(['Schools Division Superintendent', 'Assistant Schools Division Superintendent'])]);

        if (isset($filterPosition['position'])) {
            $divisionLeaderships = DivisionLeadership::query()->where('position', $filterPosition['position'])
                ->orderByRaw('CASE WHEN term_end IS NULL THEN 0 ELSE 1 END')
                ->orderBy('term_end', 'desc')
                ->orderBy('term_start', 'desc')
                ->get();
        } else {
            $divisionLeaderships = DivisionLeadership::orderByRaw('CASE WHEN term_end IS NULL THEN 0 ELSE 1 END')
                ->orderBy('term_end', 'desc')
                ->orderBy('term_start', 'desc')
                ->get();
        }
        // Base query
        $query = ResourceData::query()->where('academic_year_id', $academicYear->id);

        // Totals by resource type
        $totalsQuery = ResourceData::query()->where('academic_year_id', $academicYear->id);

        $totals = $totalsQuery->selectRaw('
            resource_name,
            SUM(inventory) as total_inventory,
            SUM(requirement) as total_requirement,
            SUM(need) as total_need
        ')
            ->groupBy('resource_name')
            ->get()
            ->map(function ($item) {
                return [
                    'resource_name' => $item->resource_name,
                    'total_inventory' => (int) $item->total_inventory,
                    'total_requirement' => (int) $item->total_requirement,
                    'total_need' => (int) $item->total_need,
                ];
            });

        $items = $query->get();

        return response()->json([
            'message' => 'Resource data retrieved successfully',
            'data' => [
                'academic_year' => [
                    'id' => $academicYear->id,
                    'name' => $academicYear->academic_year,
                ],
                'totals_by_resource' => $totals,
                // 'items' => ResourceDataResource::collection($items),
                'office_of_the_superintendent' => DivisionLeadershipResource::collection($divisionLeaderships),
            ],
        ]);
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
    public function show($id)
    {
        $resourceData = ResourceData::find($id);

        return $this->success('Resource Data fetched successfully', ['data' => $resourceData]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $resourceData = ResourceData::find($id);
        $academicYear = AcademicYear::query()->where('id', $resourceData->academic_year_id)->first();

        if(!$academicYear->status === 'default'){
            return $this->error('You cannot update resource data that is not under the default year');
        }

        // only allow numeric fields to be modified
        $validated = $request->validate([
            'inventory' => ['nullable', 'integer', 'min:0'],
            'requirement' => ['nullable', 'integer', 'min:0'],
            //'need' => ['nullable', 'integer', 'min:0'],
        ]);

        $resourceData->update(array_filter($validated, fn($v) => !is_null($v)));

        return $this->success('Resource Data updated successfully', ['data' => $resourceData]);
    }

    /**
     * Dashboard Resource Data
     */
    public function dashboardResourceData(Request $request)
    {
        $request->validate([
            'academic_year' => [
                'exists:academic_years,academic_year',
                Rule::in(AcademicYear::pluck('academic_year')->toArray())
            ]
        ]);

        $academicYearId = $request->filled('academic_year')
            ? AcademicYear::where('academic_year', $request->academic_year)->value('id')
            : AcademicYear::where('status', 'default')->value('id');

        $academicYears = AcademicYear::where('id', '<=', $academicYearId)
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $results = [];

        foreach ($academicYears as $year) {

            // Enrollment Summary
            $summary = EnrollmentData::query()
                ->join('schools', 'enrollment_data.school_id', '=', 'schools.id')
                ->where('enrollment_data.academic_year_id', $year->id)
                ->selectRaw('
                    COUNT(DISTINCT schools.id) as total_schools,
                    SUM(enrollment_data.total_count) as total_students
                ')
                ->first();

            // Resource Totals
            $resources = ResourceData::query()
                ->where('academic_year_id', $year->id)
                ->selectRaw('
                    resource_name,
                    SUM(inventory) as total_inventory
                ')
                ->groupBy('resource_name')
                ->get();

            $row = [
                'year' => $year->academic_year,
                'total_schools' => (int) ($summary->total_schools ?? 0),
                'total_students' => (int) ($summary->total_students ?? 0),
            ];

            foreach ($resources as $resource) {

                $key = str_replace(' ', '_', strtolower($resource->resource_name));

                $row[$key] = (int) $resource->total_inventory;
            }

            $results[] = $row;
        }

        return $this->success(
            'Comparative Resource Data fetched successfully.',
            [
                'data' => $results
            ]
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ResourceData $resourceData)
    {
        //
    }
}
