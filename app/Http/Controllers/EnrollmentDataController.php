<?php

namespace App\Http\Controllers;

use App\Http\Requests\EnrollmentData\IndexEnrollmentDataRequest;
use App\Models\EnrollmentData;
use Illuminate\Http\Request;
use App\Models\AcademicYear;
use App\Models\School;
use App\Http\Resources\EnrollmentDataResource;
use App\Http\Resources\SchoolResource;

class EnrollmentDataController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(IndexEnrollmentDataRequest $request)
    {
        $request->validated();

        $perPage = $request['per_page'] ?? 5;
        $sortBy = $request['sortBy'] ?? 'id';
        $sortOrder = $request['sortOrder'] ?? 'asc';
        $getAll = $request['all'] ?? false;

        $user = auth()->user();

        //Academic Year
        if ($request->has('academic_year')){
            $academic_year = AcademicYear::query()->where('academic_year', $request['academic_year'])->first();
        }else{
            $academic_year = AcademicYear::query()->where('status', 'default')->first();
        }
        if(!$academic_year){
            return $this->error('Academic Year not Found!', 404);
        }

        //Base Query [Gets Approved Enrollment Data and (Optional:Specific) School they're under]
        $query = EnrollmentData::with(['gradeLevel','submission'])
            ->whereHas('submission', function ($q) use ($academic_year, $request){
                    $q->where('status', 'approved') // gets only approved enrollment data under submissions
                            ->where('academic_year_id', $academic_year->id);

                        if ($request->filled('school_name')){
                            $school = School::query()->where('school_name', $request['school_name'])->first();
                            $q->where('school_id', $school->id);
                        }
            });
        
        //Maybe add Extra validation (Normalize Function);

        //Total Computation
        $totalsQuery = EnrollmentData::whereHas('submission', function ($q) use ($academic_year, $request) {
            $q->where('status', 'approved')
                ->where('academic_year_id', $academic_year->id);

            if ($request->filled('school_name')) {
                $schoolName = $request->input('school_name');
                $q->whereHas('school', function ($q2) use ($schoolName) {
                    $q2->where('school_name', $schoolName);
                });
            }
        });

        $totals = $totalsQuery->selectRaw('
        SUM(male_count) as total_male,
        SUM(female_count) as total_female,
        SUM(total_count) as total_students
    ')->first();


    // Fetch items
        $items = $getAll
            ? $query->get()
            : $query->paginate($perPage)->appends($request->query());

    if(!$request->input('school_name')){ // just gets the total

        return $this->success('Enrollment data retrieved successfully', [
            'message' => 'Enrollment data retrieved successfully',
                'data' => [
                    'academic_year' => [
                        'id' => $academic_year->id,
                        'name' => $academic_year->academic_year,
                    ],
                    'global_totals' => [
                        'total_male' => (int) ($totals->total_male ?? 0),
                        'total_female' => (int) ($totals->total_female ?? 0),
                        'total_students' => (int) ($totals->total_students ?? 0),
                    ],
                    // 'five_year_trend' => $fiveYearTrend,
                    // 'enrollment_by_level' => $enrollmentByLevel,
                ],
                'pagination' => $getAll ? null : $this->paginateReturn($items),
            ]);
        } else {
            $school = School::query()->where('school_name', $request['school_name'])->first();
            return $this->success('Enrollment data retrieved successfully', [

                'message' => 'Enrollment data retrieved successfully',
                'data' => [
                    'academic_year' => [
                        'id' => $academic_year->id,
                        'name' => $academic_year->academic_year,
                    ],
                    'school' => [
                        'id' => $school->id,
                        'name' => $school->school_name,
                        'school_type' => $school->schoolType->name
                    ],
                    'items' => EnrollmentDataResource::collection($items),
                    'school_totals' => [
                        'total_male' => (int) ($totals->total_male ?? 0),
                        'total_female' => (int) ($totals->total_female ?? 0),
                        'total_students' => (int) ($totals->total_students ?? 0),
                    ],
                    // 'five_year_trend' => $fiveYearTrend,
                    // 'enrollment_by_level' => $enrollmentByLevel,
                ],
                'pagination' => $getAll ? null : $this->paginateReturn($items),

            ]);
        }
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
    public function show(EnrollmentData $enrollmentData)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EnrollmentData $enrollmentData)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EnrollmentData $enrollmentData)
    {
        //
    }

    private function normalizeGrades(){
        $academic_years = AcademicYear::all();
        
        $query = EnrollmentData::with(['gradeLevel','submission'])
            ->whereHas('submission', function ($q) use ($academic_years){
                    $q->where('status', 'approved');
            });
        
        
    }
}
