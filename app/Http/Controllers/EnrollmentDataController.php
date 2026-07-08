<?php

namespace App\Http\Controllers;

use App\Http\Requests\EnrollmentData\IndexEnrollmentDataRequest;
use App\Models\EnrollmentData;
use Illuminate\Http\Request;
use App\Models\DivisionLeadership;
use App\Http\Resources\DivisionLeadershipResource;
use App\Models\AcademicYear;
use App\Models\School;
use App\Http\Resources\EnrollmentDataResource;
use App\Http\Resources\SchoolResource;
use Illuminate\Validation\Rule;
use App\Helpers\EnrollmentData\GradesDisplay;
use App\Http\Resources\EnrollmentService\DashboardEnrollmentResource;
use App\Http\Resources\EnrollmentService\IndexEnrollmentResource;
use App\Http\Resources\EnrollmentService\PublicEnrollmentResource;
use Illuminate\Support\Facades\DB;
use App\Policies\EnrollmentDataPolicy;
use App\Services\EnrollmentDataService;

class EnrollmentDataController extends Controller
{
    use GradesDisplay;

    /**
     * Index Enrollment Data
     */
    public function index(IndexEnrollmentDataRequest $request, EnrollmentDataService $service)
    {
        $this->authorize('viewAny', EnrollmentData::class);
        $request->validated();
        $user = auth()->user();

        if ($request->has('academic_year')){
            $academic_year = AcademicYear::query()->where('academic_year', $request['academic_year'])->first();
        }else{
            $academic_year = AcademicYear::query()->where('status', 'default')->first();
        }
        if(!$academic_year){
            return $this->error('Academic Year not Found!', 404);
        }

        return $this->success('Enrollment data retrieved successfully', new IndexEnrollmentResource($service->getIndex($user, $request)));
    }


    /**
     * Show Enrollment Data.
     */
    public function show($id)
    {
        $this->authorize('view', EnrollmentData::class);
        $enrollmentData = EnrollmentData::find($id);

        return $this->success('Enrollment data retrieved successfully', [
            'data' => new EnrollmentDataResource($enrollmentData->load([ 'gradeLevel'])),
        ]);
        
    }

    /**
     * Update Enrollment Data.
     * Used only for testing (OBSOLETE FUNCTION)
     */
    public function update(Request $request, $id, EnrollmentDataService $service)
    {
        $this->authorize('update', EnrollmentData::class);
        DB::beginTransaction();
        $data = $service->getUpdate($request, $id);
        DB::commit();

        return $this->success('Enrollment data updated successfully', [
        'data' => new EnrollmentDataResource($data->load('gradeLevel')),
        ]);
    }

    /**
     * Delete Enrollment Data
     * 
     * Obsolete Function
     */
    public function destroy($id)
    {
        $this->authorize('delete', EnrollmentData::class);
        $enrollmentData = EnrollmentData::find($id);

        $yearId = $enrollmentData->academic_year_id;
        $enrollmentData->delete();

        // event(new \App\Events\EnrollmentTotalsChanged($yearId));

        return $this->success('Enrollment data deleted successfully');
    }


    /**
     * Dashboard Enrollment Data
     */
        public function dashboardEnrollmentData(Request $request, EnrollmentDataService $service)
    {
        $this->authorize('dashboard', EnrollmentData::class);
        $yearLimit = 5;
        $request->validate([
            'academic_year' => ['exists:academic_years,academic_year', Rule::in(AcademicYear::pluck('academic_year')->toArray())]
        ]);
        $user = $request->user();
        if($user->hasRole('School Account') && !$user->school_id){
            return $this->error('School Account does not have a school id, please contact admin', 403);
        }

        $data = $service->getDashboardData($user, $request);
                
        return $this->success("Comparative Enrollment Data fetched succesfully.", [
                'data' => new DashboardEnrollmentResource($data)
            ]);
    }

    /**
     * Public Index Enrollment Data
     */
    public function publicIndex(Request $request, EnrollmentDataService $service)
    {
        $data = $service->getPublicEnrollment($request);

        return response()->json([
            'message' => 'Enrollment data retrieved successfully',
            'data' => new PublicEnrollmentResource($data)
        ]);
    }
}
