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
use App\Policies\ResourceDataPolicy;
use App\Services\ResourceDataService;
use App\Http\Resources\ResourceService\ResourceDataIndexResource;
use App\Http\Resources\ResourceService\PublicResourceDataResource;
use App\Http\Resources\ResourceService\DashboardResourceDataResource;

class ResourceDataController extends Controller
{
    /**
     * Index Resource Data
     */
    public function index(IndexResourceRequest $request, ResourceDataService $service)
    {
        $this->authorize('viewAny', ResourceData::class);
        $request->validated();

        $user = $request->user();
        if ($user->hasRole('School Account')){
            if($request->filled('school_name')){
                $schoolName = School::query()->where('id', $user->school_id)->value('school_name');
                if ($schoolName !== $request->school_name)
                    return $this->error('This School Account can only access data of the school they are under.', 401);
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

        return $this->success(
            'Resource data retrieved successfully',
            new ResourceDataIndexResource($service->getIndex($request->user(), $request))
        );
    }

    /**
     * Public Index Resource Data
     */
    public function publicIndex(Request $request, ResourceDataService $service)
    {
         $academicYear = AcademicYear::query()->where('status', 'default')->first();
         if (!$academicYear) {
            return response()->json(['message' => 'Academic year not found'], 404);
        }

         return response()->json([
        'message' => 'Resource data retrieved successfully',

        'data' => new PublicResourceDataResource($service->getPublicResource($request))]);
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
        $this->authorize('view', ResourceData::class);
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
            return $this->error('You cannot update resource data that is not under the default year', 409);
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
    public function dashboardResourceData(Request $request, ResourceDataService $service)
    {
        $this->authorize('dashboard', ResourceData::class);
        $request->validate([
            'academic_year' => [
                'exists:academic_years,academic_year',
                Rule::in(AcademicYear::pluck('academic_year')->toArray())
            ]
        ]);

       return $this->success(
        'Comparative Resource Data fetched successfully.',
        DashboardResourceDataResource::collection($service->getDashboardData($request->user(), $request)));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ResourceData $resourceData)
    {
        //
    }
}
