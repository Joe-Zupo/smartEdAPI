<?php

namespace App\Http\Controllers;

use App\Models\KpiData;
use Illuminate\Http\Request;
use App\Http\Requests\KPI\IndexKpiDataRequest;
use App\Http\Resources\KpiDataResource;
use Illuminate\Support\Facades\DB;
use App\Models\KpiRateData;
use App\Models\AcademicYear;
use Illuminate\Validation\Rule;
use App\Helpers\calculateTotal;
use App\Models\SchoolType;

class KpiDataController extends Controller
{
    use calculateTotal;
    /**
     * KPI Data Index
     */
    public function index(IndexKpiDataRequest $request)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        $sortBy = $request->input('sortBy', 'id');
        $sortOrder = $request->input('sortOrder', 'desc');
        $request->validated();

         $query = KpiData::query()->with('academicYear', 'kpiRate');
        // ✅ VALIDATION
        $request->validate([
            'kpi_rate' => [
                'string',
                'exists:kpi_rate_data,name',
                Rule::in(KpiRateData::pluck('name')->toArray()),
            ],
            'academic_year' => [
                'exists:academic_years,academic_year',
                Rule::in(AcademicYear::whereIn('status', ['active', 'default'])->pluck('academic_year')->toArray()),
            ],
            'trend_school_type' => ['string', 'exists:school_types,name', Rule::in(SchoolType::pluck('name')->toArray())],
            'school_type' => ['string', 'exists:school_types,name', Rule::in(SchoolType::pluck('name')->toArray())],
            'per_page' => ['integer'],
            'page' => ['integer'],
            'all' => ['in:true,false'],
        ]);
        $getAll = $request->boolean('all');

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
            $query->where('school_type', 'like', '%'. $request->school_type . '%');
        }

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', 'like', '%'. $request->academic_year_id . '%');
        }

        if ($request->filled('kpi_id')) {
            $query->where('kpi_id', 'like', '%'. $request->kpi_id . '%');
        }

        $query->orderBy($sortBy, $sortOrder);
        $paginatedData = $query
            ->paginate($perPage);

        if(!$paginatedData->count()){
            return $this->success('No more users available');
        }

        //Main Query
        $query->with(['kpiRate', 'academicYear']);
        $items = $getAll ? $query->get() : $query->paginate($perPage)->appends($request->query());
        if ($items->isEmpty()) {
                return response()->json(['message' => 'No KPI data found']);
            }

        //TREND PER SCHOOL LEVEL (FILTERED BY TREND SCHOOL LEVEL & KPI RATE)
        if ($request->filled('academic_year')) {
            // Get the requested year
            $startYear = AcademicYear::query()->where('academic_year', $request->input('academic_year'))->first();
            } else {
                // Get the default year
                $startYear = AcademicYear::query()->where('status', 'default')->first();
            }
            if (!$startYear) {
                return $this->error('Academic year not found.', 404);
            }

        // Get this year + 4 previous years (by ID)
        $academicYearIds = AcademicYear::query()->where('id', '<=', $startYear->id)
            ->whereNotIn('status', ['upcoming', 'archived'])
            ->orderBy('id', 'desc')
            ->limit(5)
            ->pluck('id');

        $kpiId = null;
        if($request->filled('kpi_rate')){
            $kpiId = KpiRateData::query()->where('name', $request->input('kpi_rate'))->value('id');
        }
        $totalsItems5Years = KpiData::with(['academicYear', 'kpiRate'])
            ->when($kpiId, function ($q) use ($kpiId) {
                $q->where('kpi_id', $kpiId);
            })
            ->whereIn('academic_year_id', $academicYearIds)
            ->when($request->filled('trend_school_type'), function ($q) use ($request) {
                $q->where('school_type', $request->input('trend_school_type'));
            })
            ->get();

        $groupedTrends = $totalsItems5Years->groupBy(['kpi_id','school_type','academic_year_id'])
            ->map(function ($schoolTypes) {
                return $schoolTypes->map(function ($yearGroups) {
                    return $yearGroups->map(function ($items) {
                        $first = $items->first();
                        return [
                            'trend_school_type' => $first->school_type,
                            'academic_year_id'  => $first->academicYear->id,
                            'academic_year'     => $first->academicYear->academic_year,
                            'kpirate'           => $first->kpiRate->name,
                            'male'              => (float) $first->male,
                            'female'            => (float) $first->female,
                            'total'             => (float) $first->total,
                        ];
                    })->sortByDesc('academic_year_id')->values();
                });
            });
        
        $trendOutput = collect();

        foreach ($groupedTrends as $kpiId => $schoolTypes) {
            $years = $schoolTypes
                ->flatten(1)
                ->pluck('academic_year_id')
                ->unique();
            $kpiTrends = collect();

            foreach ($years as $yearId) {
                $records = $schoolTypes
                    ->flatten(1)
                    ->where('academic_year_id', $yearId);
                $first = $records->first();
                $kpiTrends->push([
                    'academic_year' => $first['academic_year'],
                    'kpirate'       => $first['kpirate'],
                    'male'          => round($records->avg('male'), 1),
                    'female'        => round($records->avg('female'), 1),
                    'total'         => round($records->avg('total'), 1),
                ]);
            }
            $trendOutput->push([
                'kpirate' => $kpiTrends->first()['kpirate'],
                'trends' => $kpiTrends->values(),
            ]);
        }

        $fiveYearStats = $trendOutput->map(function ($kpi) {

            return [
                'kpirate' => $kpi['kpirate'],
                'male_five_year_avg' => round(
                    collect($kpi['trends'])->avg('male'),1),
                'female_five_year_avg' => round(
                    collect($kpi['trends'])->avg('female'),1),
                'total_five_year_avg' => round(
                    collect($kpi['trends'])->avg('total'),1),
            ];
        });
        
        return $this->success('KPI data retrieved successfully', [
            'data' => [
                'items' => KpiDataResource::collection($items),
                'kpi_trends' => $groupedTrends->toArray(),
                'kpi_trends_total' => $fiveYearStats,
            ],
            'pagination' => $getAll ? null : $this->paginateReturn($items),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * KPI Data Show
     */
    public function show(KpiData $kpiData)
    {
        return $this->success('KPI data fetched successfully',
        ['kpi_data' => new KpiDataResource($kpiData->load([
                        'academicYear',
                        'kpiRate'
                    ]))]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, KpiData $kpiData)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(KpiData $kpiData)
    {
        //
    }
}
