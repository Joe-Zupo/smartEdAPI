<?php

namespace App\Http\Controllers;

use App\Http\Requests\EnrollmentData\IndexEnrollmentDataRequest;
use App\Models\EnrollmentData;
use Illuminate\Http\Request;
use App\Models\AcademicYear;
use App\Models\School;
use App\Http\Resources\EnrollmentDataResource;
use App\Http\Resources\SchoolResource;
use App\Helpers\EnrollmentData\GradesDisplay;
use Illuminate\Support\Facades\DB;

class EnrollmentDataController extends Controller
{
    use GradesDisplay;

    /**
     * Index Enrollment Data
     */
    public function index(IndexEnrollmentDataRequest $request)
    {
        $request->validated();

        $perPage = $request['per_page'] ?? 5;
        // $sortBy = $request['sortBy'] ?? 'id';
        // $sortOrder = $request['sortOrder'] ?? 'asc';
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
        $query = EnrollmentData::with(['gradeLevel'])
            ->where('academic_year_id', $academic_year->id);

                        if($user->hasRole('School Account')){ //Regardless of whats the query; if user is a school account they will only see their school's data
                            $school = School::query()->where('id', $user->school_id)->value('school_name');
                            $request['school_name'] = $school;
                        }

                        if ($request->filled('school_name')){
                            $school = School::query()->where('school_name', $request['school_name'])->first();
                            $query->where('school_id', $school->id);
                        }

        //Total Computation
        $totalsQuery = EnrollmentData::query()->where('academic_year_id', $academic_year->id);

            if ($request->filled('school_name')) {
                $school = School::query()->where('school_name', $request['school_name'])->first();
                $totalsQuery->where('school_id', $school->id);
            }

        $totals = $totalsQuery->selectRaw('
        SUM(male_count) as total_male,
        SUM(female_count) as total_female,
        SUM(total_count) as total_students
    ')->first();

    // Five-year trend
        $fiveYearTrend = $this->getFiveYearTrend($user, $academic_year, $request);

    // Enrollment by educational level
        $enrollmentByLevel = $this->getEnrollmentByLevel($user, $academic_year, $request);

    // Enrollment by grade level
        $enrollmentByGrade = $this->getEnrollmentByGrade($user, $academic_year, $request);

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
                    'enrollments_totals' => [
                        'total_male' => (int) ($totals->total_male ?? 0),
                        'total_female' => (int) ($totals->total_female ?? 0),
                        'total_students' => (int) ($totals->total_students ?? 0),
                    ],
                    'five_year_trend' => $fiveYearTrend,
                    'enrollment_by_level' => $enrollmentByLevel,
                    'enrollment_by_grade' => $enrollmentByGrade,
                ],
                'pagination' => $getAll ? null : $this->paginateReturn($items),
            ]);
        } else {
            $school = School::query()->where('school_name', $request['school_name'])->first();

            $displayedItems = $this->displayRelevant($school, EnrollmentDataResource::collection($items));
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
                    'items' => $displayedItems,
                    'school_totals' => [
                        'total_male' => (int) ($totals->total_male ?? 0),
                        'total_female' => (int) ($totals->total_female ?? 0),
                        'total_students' => (int) ($totals->total_students ?? 0),
                    ],
                    'five_year_trend' => $fiveYearTrend,
                    'enrollment_by_level' => $enrollmentByLevel,
                    'enrollmentByGrade' => $enrollmentByGrade,
                ],
                'pagination' => $getAll ? null : $this->paginateReturn($items),

            ]);
        }
    }


    /**
     * Show Enrollment Data.
     */
    public function show($id)
    {

        $enrollmentData = EnrollmentData::find($id);

        return $this->success('Enrollment data retrieved successfully', [
            'data' => new EnrollmentDataResource($enrollmentData->load([ 'gradeLevel'])),
        ]);
        
    }

    /**
     * Update Enrollment Data.
     * Used only for testing
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        // basic update and fire totals change
        $validated = $request->validate([
            'male_count' => ['required','numeric', 'min:0'],
            'female_count' => ['required','numeric', 'min:0'],
        ]);

        $enrollmentData = EnrollmentData::find($id);
        $enrollmentData->update($validated);

        // dispatch totals changed for the related academic year
        // $yearId = $enrollmentData->submission->academic_year_id;
        // event(new \App\Events\EnrollmentTotalsChanged($yearId));

        DB::commit();
        return $this->success('Enrollment data updated successfully', [
        'data' => new EnrollmentDataResource($enrollmentData->load('gradeLevel')),
        ]);
    }

    /**
     * Delete Enrollment Data
     */
    public function destroy($id)
    {
        $enrollmentData = EnrollmentData::find($id);

        $yearId = $enrollmentData->academic_year_id;
        $enrollmentData->delete();

        // event(new \App\Events\EnrollmentTotalsChanged($yearId));

        return $this->success('Enrollment data deleted successfully');
    }

    /**
     * Get five-year enrollment trend
     */
    private function getFiveYearTrend($user, $currentAcademicYear, $request)
    {
        // Get last 5 academic years (current + 4 previous)
        $academicYears = AcademicYear::query()->where('id', '<=', $currentAcademicYear->id)
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        $trend = [];

        foreach ($academicYears as $year) {
            $trendQuery = EnrollmentData::query()->where('academic_year_id', $year->id);

                if ($user && $user->hasRole('School Account')) {
                    $trendQuery->where('school_id', $user->school_id);
                } elseif ($request->filled('school_name')) {
                    $school = School::query()->where('school_name', $request['school_name'])->first();
                    $trendQuery->where('school_id', $school->id);
                }

            $yearTotals = $trendQuery->selectRaw('
                SUM(male_count) as total_male,
                SUM(female_count) as total_female,
                SUM(total_count) as total_students
            ')->first();

            $trend[] = [
                'academic_year' => $year->academic_year,
                'academic_year_id' => $year->id,
                'total_male' => (int) ($yearTotals->total_male ?? 0),
                'total_female' => (int) ($yearTotals->total_female ?? 0),
                'total_students' => (int) ($yearTotals->total_students ?? 0),
            ];
        }

        return $trend;
    }

    /**
     * Get enrollment by educational level (5 years)
     */
    private function getEnrollmentByLevel($user, $currentAcademicYear, $request)
    {
        //Define Grade Groups
        $allGradeGroups = [
            'Kinder' => ['Kinder'],
            'Elementary' => ['Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6'],
            'Junior High' => ['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'],
            'Senior High' => ['Grade 11', 'Grade 12'],
        ];

        $schoolType = 'All';

        // Determine school type if user is a School Account
        if ($user) {
            $user = $user->load('school.schoolType');
            if ($user->hasRole('School Account')) {
                $schoolType = $user->school->schoolType->name;
            } elseif ($user->hasAnyRole(['System Admin']) && $request->filled('school_name')) {
                $schoolName = $request->input('school_name');
                $school = School::query()->where('school_name', $schoolName)->with('schoolType')->first();
                $schoolType = $school ? $school->schoolType->name : 'All';
            }
        }
        // Filter grade groups based on school type
        $gradeGroups = [];

       $gradeGroups = match ($schoolType) {

                'Elementary' => [
                    'Kinder' => ['Kinder'],
                    'Elementary' => ['Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6'],
                ],

                'Junior High School' => ['Junior High' => ['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'],],

                'Standalone SHS' => ['Senior High' => ['Grade 11', 'Grade 12'],],

                'Integrated School',
                'Science High School',
                'ALS',
                'Junior High School with SHS' => [
                    'Kinder' => ['Kinder'],
                    'Elementary' => ['Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6'],
                    'Junior High' => ['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'],
                    'Senior High' => ['Grade 11', 'Grade 12'],
                ],

                default => $allGradeGroups,
            };


        // Get last 5 academic years
        $academicYears = AcademicYear::query()->where('id', '<=', $currentAcademicYear->id)
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        $byLevel = [];

        foreach ($academicYears as $year) {
            // Fetch enrollment data
            $enrollments = EnrollmentData::with('gradeLevel')
                    ->where('academic_year_id', $year->id);

                    if ($user && $user->hasRole('School Account')) {
                        $enrollments->where('school_id', $user->school_id);
                    } elseif ($request->filled('school_name')) {
                        $schoolName = $request->input('school_name');
                        $school = School::query()->where('school_name', $schoolName)->first();

                        $enrollments->where('school_id', $school->id);
                    }

            $enrollments = $enrollments->get();
            // Initialize category totals
            $levelTotals = [];
            foreach ($gradeGroups as $category => $grades) {
                $levelTotals[$category] = ['total_male' => 0, 'total_female' => 0, 'total_students' => 0];
            }

            // Sum enrollments into categories
            foreach ($enrollments as $enrollment) {
                $gradeName = $enrollment->grade_level ?? 'Unknown';
                foreach ($gradeGroups as $category => $grades) {
                    if (in_array($gradeName, $grades)) {
                        $levelTotals[$category]['total_male'] += $enrollment->male_count;
                        $levelTotals[$category]['total_female'] += $enrollment->female_count;
                        $levelTotals[$category]['total_students'] += $enrollment->total_count;
                        break;
                    }
                }
            }

            // Convert to array format
            $byLevel[] = [
                'academic_year' => $year->academic_year,
                'levels' => [],
                ];
                $index = count($byLevel) - 1;
            foreach ($levelTotals as $category => $totals) {
                $byLevel[$index]['levels'][] = [
                    'grade_level' => $category,
                    'total_male' => $totals['total_male'],
                    'total_female' => $totals['total_female'],
                    'total_students' => $totals['total_students'],
                ];
            }
        }

            return $byLevel;
    }

    private function getEnrollmentByGrade($user, $currentAcademicYear, $request)
    {
        $school = School::query()->where('school_name', $request->input('school_name'))->first();
        $type = $school->schoolType->name ?? null;

        $allowedGrades = match ($type) {  
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

                default => ['Kinder','Grade 1','Grade 2','Grade 3','Grade 4','Grade 5','Grade 6','Grade 7','Grade 8','Grade 9','Grade 10','Grade 11','Grade 12'],
            };
        $academicYears = AcademicYear::query()->where('id', '<=', $currentAcademicYear->id)
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        $byGrade = [];

        foreach ($academicYears as $year) {

            $enrollments = EnrollmentData::query()
                ->where('academic_year_id', $year->id);

                if ($user && $user->hasRole('School Account')) {
                    $enrollments->where('school_id', $user->school_id);
                }
                else if ($request->filled('school_name')) {
                    $schoolName = $request->input('school_name');
                    $school = School::query()->where('school_name', $schoolName)->first();

                    $enrollments->where('school_id', $school->id);
                }

            $enrollments = $enrollments->get();

            $gradeTotals = [];

            foreach ($enrollments as $enrollment) {

                $grade = $enrollment->grade_level;

                if (!in_array($grade, $allowedGrades)) {
                    continue;
                }
                if (!isset($gradeTotals[$grade])) {
                    $gradeTotals[$grade] = [
                        'grade_level' => $grade,
                        'total_male' => 0,
                        'total_female' => 0,
                        'total_students' => 0,
                    ];
                }

                $gradeTotals[$grade]['total_male'] += $enrollment->male_count;
                $gradeTotals[$grade]['total_female'] += $enrollment->female_count;
                $gradeTotals[$grade]['total_students'] += $enrollment->total_count;
            }

                $byGrade[] = [
                'academic_year' => $year->academic_year,
                'levels' => array_values($gradeTotals),
            ];
        }

        return $byGrade;
    }
}
