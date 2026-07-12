<?php

namespace App\Services;
use Illuminate\Support\Facades\DB;
use App\Models\EnrollmentData;
use App\Models\AcademicYear;
use App\Helpers\autoPaginator;
use App\Helpers\EnrollmentData\GradesDisplay;
use App\Models\KpiData;
use App\Http\Resources\KpiDataResource;
use App\Models\DivisionLeadership;
use App\Http\Resources\DivisionLeadershipResource;
use App\Models\KpiRateData;


class KpiDataService
{
    use autoPaginator, GradesDisplay;

    public function getIndex($request, $public, $academicYearId){
        if(!is_null($request)){
            $perPage = $request->get('per_page', 10);
            $getAll  = $request->boolean('all');
            $query = KpiData::query();

        //Query Params
        if($request->filled('kpi_rate')){
            $kpiRate = $query->whereHas('kpiRate', function($q) use ($query, $request){
                $q->where('name', $request->input('kpi_rate'));
            });
        }

        if($request->filled('academic_year')){
            $academicYear = AcademicYear::query()->where('academic_year', $request->input('academic_year'))->first();
            $query->where('academic_year_id', $academicYear->id);
            }else{
                $academicYear = AcademicYear::query()->where('status', 'default')->first();
                $query->where('academic_year_id', $academicYear->id);
            }

        if ($request->filled('school_type')) {
            $query->where('school_type', $request->school_type);
        }

        //Main Query
        $query->with(['kpiRate', 'academicYear']);


        $items = $getAll ? $query->get() : $query->paginate($perPage)->appends($request->query());
        if ($items->isEmpty()) {
            return 'Empty Query';
        }

    if(!$public){
            $data = [ 'data' =>
                        [
                            'items' => KpiDataResource::collection($items),
                        ],
                    'pagination' => $getAll ? null : $this->paginateReturn($items),
                    ];

                }else{

                $filterPosition = $request['position'];

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

                $data = ['data' =>
                    [
                        'items' => KpiDataResource::collection($items),
                        // 'kpi_trends' => $groupedTrends->toArray(),
                        // 'kpi_trends_total' => $fiveYearStats,
                        'office_of_the_superintendent' => DivisionLeadershipResource::collection($divisionLeaderships),
                    ],
                    'pagination' =>  $getAll ? null : $this->paginateReturn($items),
                ];
            }
            return $data;

    }else{ //Broadcast
        $academicYear = AcademicYear::findOrFail($academicYearId);

        $query = KpiData::query()
            ->where('academic_year_id', $academicYear->id)
            ->with(['kpiRate', 'academicYear']);

        $items = $query->get();

            return [

                'data' => [

                    'items' => KpiDataResource::collection($items),
                ],

                'pagination' => null,

            ];
        }
    }

    public function getKpiTrends($request = null, bool $public = false, ?int $academicYearId = null)
    { // 4 Uses - Controller - Private & Public || Broadcast - Private & Public
        /*
        |--------------------------------------------------------------------------
        | Controller
        |--------------------------------------------------------------------------
        */
        if (!is_null($request)) {

         //TREND PER SCHOOL LEVEL (FILTERED BY TREND SCHOOL LEVEL & KPI RATE)
        if ($request->filled('academic_year')) {
            // Get the requested year
            $startYear = AcademicYear::query()->where('academic_year', $request->input('academic_year'))->first();
            } else {
                // Get the default year
                $startYear = AcademicYear::query()->where('status', 'default')->first();
            }
        
         $kpiId = null;
        if($request->filled('kpi_rate')){
            $kpiId = KpiRateData::query()->where('name', $request->input('kpi_rate'))->value('id');
        }

        // Get this year + 4 previous years (by ID)
        $academicYearIds = AcademicYear::query()->where('id', '<=', $startYear->id)
            ->orderBy('id', 'desc')
            ->limit(5)
            ->pluck('id');

        $totalsItems5Years = KpiData::with(['academicYear', 'kpiRate'])
            ->when($kpiId, function ($q) use ($kpiId) {
                $q->where('kpi_id', $kpiId);
            })
            ->whereIn('academic_year_id', $academicYearIds)
            ->when($request->filled('school_type'), function ($q) use ($request) {
                $q->where('school_type', $request->input('school_type'));
            })
            ->get();

          $groupedTrends = $totalsItems5Years
            ->sortBy([
                ['kpi_id', 'asc'],
                ['school_type', 'asc'],
                ['academic_year_id', 'desc'],
            ])
            ->values()
            ->map(function ($item) {

                return [
                    'id' => $item->id,

                    'kpi_id' => $item->kpiRate->id,

                    'kpi_rate' => [
                        'id' => $item->kpiRate->id,
                        'name' => $item->kpiRate->name,
                    ],

                    'academic_year_id' => $item->academicYear->id,

                    'academic_year' => [
                        'id' => $item->academicYear->id,
                        'name' => $item->academicYear->academic_year,
                    ],

                    'male' => (float) $item->male,
                    'female' => (float) $item->female,
                    'total' => (float) $item->total,

                    'school_type' => $item->school_type,
                ];
            });

        $fiveYearStats = $groupedTrends
            ->groupBy('kpi_id')
            ->map(function ($records) {

                $first = $records->first();

                return [
                    'kpi_id' => $first['kpi_id'],
                    'kpi_rate' => $first['kpi_rate'],

                    'male_five_year_avg' => round(
                        $records->avg('male'),
                        1
                    ),

                    'female_five_year_avg' => round(
                        $records->avg('female'),
                        1
                    ),

                    'total_five_year_avg' => round(
                        $records->avg('total'),
                        1
                    ),
                ];
            })
            ->values();

            if($public){

                $divisionLeaderships = DivisionLeadership::query()
                    ->orderByRaw('CASE WHEN term_end IS NULL THEN 0 ELSE 1 END')
                    ->orderBy('term_end', 'desc')
                    ->orderBy('term_start', 'desc')
                    ->get();

                $data = ['data' =>
                    [
                        'kpi_trends' => $groupedTrends->toArray(),
                        'kpi_trends_total' => $fiveYearStats,
                    ],
                    'office_of_the_superintendent' => $divisionLeaderships,
                ];

            }else{
                $data = ['data' =>
                    [
                        'kpi_trends' => $groupedTrends->toArray(),
                        'kpi_trends_total' => $fiveYearStats,
                    ]
                ];
            }

            return $data;
        }

        /*
        |--------------------------------------------------------------------------
        | Broadcast
        |--------------------------------------------------------------------------
        */
        else {

            $startYear = AcademicYear::findOrFail($academicYearId);

            $kpiId = null;

            $schoolType = null;
        }

        $academicYearIds = AcademicYear::query()
            ->where('id', '<=', $startYear->id)
            ->orderByDesc('id')
            ->limit(5)
            ->pluck('id');

        $totalsItems5Years = KpiData::with([
                'academicYear',
                'kpiRate'
            ])
            ->when($kpiId, function ($q) use ($kpiId) {
                $q->where('kpi_id', $kpiId);
            })
            ->whereIn('academic_year_id', $academicYearIds)
            ->when($schoolType, function ($q) use ($schoolType) {
                $q->where('school_type', $schoolType);
            })
            ->get();

        $groupedTrends = $totalsItems5Years
            ->sortBy([
                ['kpi_id', 'asc'],
                ['school_type', 'asc'],
                ['academic_year_id', 'desc'],
            ])
            ->values()
            ->map(function ($item) {

                return [

                    'id' => $item->id,

                    'kpi_id' => $item->kpiRate->id,

                    'kpi_rate' => [
                        'id' => $item->kpiRate->id,
                        'name' => $item->kpiRate->name,
                    ],

                    'academic_year_id' => $item->academicYear->id,

                    'academic_year' => [
                        'id' => $item->academicYear->id,
                        'name' => $item->academicYear->academic_year,
                    ],

                    'male' => (float) $item->male,

                    'female' => (float) $item->female,

                    'total' => (float) $item->total,

                    'school_type' => $item->school_type,
                ];
            });

        $fiveYearStats = $groupedTrends
            ->groupBy('kpi_id')
            ->map(function ($records) {

                $first = $records->first();

                return [

                    'kpi_id' => $first['kpi_id'],

                    'kpi_rate' => $first['kpi_rate'],

                    'male_five_year_avg' => round(
                        $records->avg('male'),
                        1
                    ),

                    'female_five_year_avg' => round(
                        $records->avg('female'),
                        1
                    ),

                    'total_five_year_avg' => round(
                        $records->avg('total'),
                        1
                    ),

                ];
            })
            ->values();

            

        $data = [
                'data' => [
                    'kpi_trends' => $groupedTrends,
                    'kpi_trends_total' => $fiveYearStats,
                        ]
                ];

                
        return $data;
    }
}