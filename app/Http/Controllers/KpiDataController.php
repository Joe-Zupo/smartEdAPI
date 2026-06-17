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


class KpiDataController extends Controller
{
    /**
     * KPI Data Index
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $getAll  = $request->boolean('all');

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
            'trend_school_level' => ['string', 'exists:school_types,name'],
            'school_type' => ['string', 'exists:school_types,name'],
            'per_page' => ['integer'],
            'page' => ['integer'],
            'all' => ['in:true,false'],
        ]);

        $query = KpiData::query();

        //Query Params
        if($request->filled('kpi_rate')){
            $kpiRate = $query->where('name', $request->input('kpi_rate'))->first();
            if ($kpiRate) {
                $kpiId = $kpiRate->id;
            }
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

        //Main Query
        $query->with(['kpiRate', 'academicYear']);


        $items = $getAll ? $query->get() : $query->paginate($perPage)->appends($request->query());
        if ($items->isEmpty()) {
            return response()->json(['message' => 'No KPI data found']);
        }

        $items = $getAll ? $query->get() : $query->paginate($perPage)->appends($request->query());

        if ($items->isEmpty()) {
            return response()->json(['message' => 'No KPI data found']);
        }

        //TREND PER SCHOOL LEVEL (FILTERED BY TREND SCHOOL LEVEL & KPI RATE)
        if ($request->filled('filter.academic_year')) {
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
            ->orderBy('id', 'desc')
            ->limit(5)
            ->pluck('id');

        $kpiId = null;
        $totalsItems5Years = KpiData::with(['academicYear', 'kpiRate'])
            ->when($kpiId, function ($q) use ($kpiId) {
                $q->where('kpi_id', $kpiId);
            })
            ->whereIn('academic_year_id', $academicYearIds)
            ->when($request->filled('filter.trend_school_level'), function ($q) use ($request) {
                $q->where('school_type', $request->input('filter.trend_school_level'));
            })
            ->get();

         $groupedTrends = $totalsItems5Years
            ->groupBy(['school_type', 'academic_year_id'])
            ->map(fn($yearGroups) => $yearGroups->map(fn($items) => [
                'trend_school_level' => $items->first()->school_type,
                'academic_year_id'   => $items->first()->academicYear->id, 
                'academic_year'    => $items->first()->academicYear->academic_year,
                'kpirate'          => $items->first()->kpiRate->name,
                'male'             => (float) $items->first()->male,
                'female'           => (float) $items->first()->female,
                'total'            => (float) $items->first()->total,
            ])->sortByDesc('academic_year_id')->values());

        $hasSchoolLevel = $groupedTrends->hasAny(['Elementary','Integrated School','Junior High School','Junior High School with SHS','Standalone SHS','Science High School','ALS']);
        return $hasSchoolLevel;
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
