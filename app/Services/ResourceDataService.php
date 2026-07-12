<?php

namespace App\Services;

use App\Helpers\autoPaginator;
use App\Http\Resources\ResourceDataResource;
use App\Http\Resources\DivisionLeadershipResource;
use App\Models\AcademicYear;
use App\Models\ResourceData;
use App\Models\EnrollmentData;
use App\Models\DivisionLeadership;
use App\Models\School;
use Illuminate\Http\Request;

class ResourceDataService
{
    use autoPaginator;
    /**
     * Authenticated Resource Index
     */
    public function getIndex($user, Request $request)
    {
        $perPage = $request['per_page'] ?? 5;
        $sortBy = $request['sortBy'] ?? 'id';
        $sortOrder = $request['sortOrder'] ?? 'desc';
        $getAll = $request->boolean('all');

        if ($user->hasRole('School Account')) {

            if ($request->filled('school_name')) {

                $schoolName = School::query()->where('id', $user->school_id)
                    ->value('school_name');

                if ($schoolName !== $request->school_name) {
                    abort(403, 'This School Account can only access data of the school they are under.');
                }

            } else {

                $request['school_name'] = School::query()->where('id', $user->school_id)
                    ->value('school_name');
            }
        }

        $academicYear = $request->filled('academic_year')
            ? AcademicYear::query()->where('academic_year', $request->academic_year)->first()
            : AcademicYear::query()->where('status', 'default')->first();

        if (!$academicYear) {
            abort(404, 'Academic Year Not Found');
        }

        $query = ResourceData::query()->where(
            'academic_year_id',
            $academicYear->id
        );

        if ($request->filled('school_name')) {

            $school = School::query()->where(
                'school_name',
                $request->school_name
            )->first();

            $query->where('school_id', $school->id);
        }

        $totalsQuery = ResourceData::query()->where(
            'academic_year_id',
            $academicYear->id
        );

        if ($request->filled('school_name')) {

            $schoolId = School::query()->where(
                'school_name',
                $request->school_name
            )->value('id');

            $totalsQuery->where('school_id', $schoolId);
        }

        $totals = $totalsQuery
            ->selectRaw('
                resource_name,
                SUM(inventory) as total_inventory,
                SUM(requirement) as total_requirement,
                SUM(need) as total_need
            ')
            ->groupBy('resource_name')
            ->get()
            ->map(fn ($item) => [
                'resource_name' => $item->resource_name,
                'inventory' => (int) $item->total_inventory,
                'requirement' => (int) $item->total_requirement,
                'need' => (int) $item->total_need,
            ]);

        $allowedSorts = [
            'id',
            'resource_name',
            'updated_at',
            'created_at',
        ];

        if (! in_array($sortBy, $allowedSorts)) {
            $sortBy = 'updated_at';
        }

        $query->orderBy($sortBy, $sortOrder);

        $resources = $getAll
            ? $query->get()
            : $query->paginate($perPage)
                ->appends($request->query());

        return [
            'data' => [
                'academic_year' => [
                    'id' => $academicYear->id,
                    'name' => $academicYear->academic_year,
                ],

                'items' => $resources,

                'totals_by_resource' => $totals,
            ],

            'pagination' => $getAll
                ? null
                : $this->paginateReturn($resources),
        ];
    }

        /**
         * Public Landing Page Resource Data
         */
        public function getPublicResource($request = null, $academicYearId = null)
        {
            /*
            |--------------------------------------------------------------------------
            | Controller---------------------------------------------------------------
            |--------------------------------------------------------------------------
            */
            if ($request && !$academicYearId) {

                $academicYear = AcademicYear::query()
                    ->where('status', 'default')
                    ->first();

                $divisionLeaderships = $request->filled('position')
                    ? DivisionLeadership::query()->where(
                        'position',
                        $request->position
                    )
                    : DivisionLeadership::query();

                $divisionLeaderships = $divisionLeaderships
                    ->orderByRaw('CASE WHEN term_end IS NULL THEN 0 ELSE 1 END')
                    ->orderBy('term_end', 'desc')
                    ->orderBy('term_start', 'desc')
                    ->get();

                $totals = ResourceData::query()
                    ->where('academic_year_id', $academicYear->id)
                    ->selectRaw('
                        resource_name,
                        SUM(inventory) as total_inventory,
                        SUM(requirement) as total_requirement,
                        SUM(need) as total_need
                    ')
                    ->groupBy('resource_name')
                    ->get()
                    ->map(fn ($item) => [

                        'resource_name' => $item->resource_name,

                        'total_inventory' => (int) $item->total_inventory,

                        'total_requirement' => (int) $item->total_requirement,

                        'total_need' => (int) $item->total_need,

                    ]);

                return [

                    'academic_year' => [

                        'id' => $academicYear->id,

                        'name' => $academicYear->academic_year,

                    ],

                    'totals_by_resource' => $totals,

                    'office_of_the_superintendent' =>
                        $divisionLeaderships,
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | Broadcast
            |--------------------------------------------------------------------------
            */
            else if ($academicYearId && !$request) {

                $academicYear = AcademicYear::findOrFail($academicYearId);

                $totals = ResourceData::query()
                    ->where('academic_year_id', $academicYearId)
                    ->selectRaw('
                        resource_name,
                        SUM(inventory) as total_inventory,
                        SUM(requirement) as total_requirement,
                        SUM(need) as total_need
                    ')
                    ->groupBy('resource_name')
                    ->get()
                    ->map(fn ($item) => [

                        'resource_name' => $item->resource_name,

                        'total_inventory' => (int) $item->total_inventory,

                        'total_requirement' => (int) $item->total_requirement,

                        'total_need' => (int) $item->total_need,

                    ]);

                return [

                    'academic_year' => [

                        'id' => $academicYear->id,

                        'name' => $academicYear->academic_year,

                    ],

                    'totals_by_resource' => $totals,

                    'office_of_the_superintendent' =>
                        null,
                ];
            }
        }

    /**
     * Dashboard Resource Data
     */
    public function getDashboardData($user, Request $request)
    {
         $academicYearId = $request->filled('academic_year')
        ? AcademicYear::query()->where(
            'academic_year',
            $request->academic_year
        )->value('id')
        : AcademicYear::query()->where('status', 'default')->value('id');

    $academicYears = AcademicYear::where('id', '<=', $academicYearId)
        ->orderByDesc('id')
        ->limit(5)
        ->get();

    $results = [];

    foreach ($academicYears as $year) {

        if ($user->hasRole('School Account')) {

            $summary = EnrollmentData::query()
                ->where('school_id', $user->school_id)
                ->where('academic_year_id', $year->id)
                ->selectRaw('
                    SUM(total_count) as total_students
                ')
                ->first();

            $resources = ResourceData::query()
                ->where('academic_year_id', $year->id)
                ->where('school_id', $user->school_id)
                ->whereIn('resource_name', [
                    'Classrooms',
                    'Teachers',
                ])
                ->selectRaw('
                    resource_name,
                    SUM(inventory) as total_inventory
                ')
                ->groupBy('resource_name')
                ->get();

            $row = [

                'year' => $year->academic_year,

                'total_students' => (int) ($summary->total_students ?? 0),

            ];

        } else {

            $summary = EnrollmentData::query()
                ->join('schools', 'enrollment_data.school_id', '=', 'schools.id')
                ->where('academic_year_id', $year->id)
                ->selectRaw('
                    COUNT(DISTINCT schools.id) as total_schools,
                    SUM(total_count) as total_students
                ')
                ->first();

            $resources = ResourceData::query()
                ->where('academic_year_id', $year->id)
                ->whereIn('resource_name', [
                    'Classrooms',
                    'Teachers',
                ])
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
        }

        foreach ($resources as $resource) {

            $key = str_replace(
                ' ',
                '_',
                strtolower($resource->resource_name)
            );

            $row[$key] = (int) $resource->total_inventory;
        }

        $results[] = $row;
    }

    return $results;
    }
}