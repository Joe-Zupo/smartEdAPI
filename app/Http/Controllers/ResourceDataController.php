<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResourceData\IndexResourceRequest;
use App\Http\Resources\ResourceDataResource;
use App\Models\AcademicYear;
use App\Models\ResourceData;
use App\Models\School;
use Illuminate\Http\Request;

class ResourceDataController extends Controller
{
    /**
     * Display a listing of the resource.
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
        $query = ResourceData::query()->with('submission');
        $query->whereHas('submission', function ($q) use ($academicYear, $request){
            $q->where('status','approved')
            ->where('academic_year_id', $academicYear->id);

            if($request->has('school_name')){
                $school = School::where('school_name', $request['school_name'])->first();
                $q->where('school_id', $school->id);
            }
        });

        //Totals Query [testing]
        $totalsQuery = ResourceData::whereHas('submission', function ($q) use ($academicYear, $request) {
            $q->where('status', 'approved')
                ->where('academic_year_id', $academicYear->id);

            if ($request->filled('school_name')) {
                $schoolName = $request->input('school_name');
                $q->whereHas('school', function ($q2) use ($schoolName) {
                    $q2->where('school_name', $schoolName);
                });
            }
        });

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
    public function show(ResourceData $resourceData)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ResourceData $resourceData)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ResourceData $resourceData)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ResourceData $resourceData)
    {
        //
    }
}
