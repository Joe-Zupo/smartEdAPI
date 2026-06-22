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
use App\Http\Requests\KPI\StoreKpiDataRequest;
use App\Helpers\calculateTotal;
use App\Http\Requests\KPI\UpdateKpiDataRequest;
use App\Models\SchoolType;

class KpiDataController extends Controller
{
    use calculateTotal;
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
            'school_type' => ['string', 'exists:school_types,name'],
            'per_page' => ['integer'],
            'page' => ['integer'],
            'all' => ['in:true,false'],
        ]);

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
            return response()->json(['message' => 'No KPI data found']);
        }
        
        return $this->success('KPI data retrieved successfully', [
            'data' => [
                'items' => KpiDataResource::collection($items),
                // 'kpi_trends' => $groupedTrends->toArray(),
                // 'kpi_trends_total' => $fiveYearStats,
            ],
            'pagination' => $getAll ? null : $this->paginateReturn($items),
        ]);
    }

    /**
     * Store KPI data record
     */
    public function store(StoreKpiDataRequest $request)
    {
        $validated = $request->validated(); // user input

        $created = DB::transaction(function () use ($validated) {
            $records = [];

            //Create all KPI Records that are filled
            foreach ($validated['items'] as $item) {

                // Convert names to IDs
                $kpi = KpiRateData::query()->where('name', $item['kpi_rate_name'])->first();
                $item['kpi_id'] = $kpi->id;

                $academicYear = AcademicYear::query()->where('academic_year', $item['academic_year'])
                    ->where('status', 'default')
                    ->first();
                $item['academic_year_id'] = $academicYear->id;

                unset($item['kpi_rate_name'], $item['academic_year']);

                $total = $this->calculateTotal($item['male'],$item['female'],$academicYear->id, true);

                $record = KpiData::create([
                    'kpi_id' => $item['kpi_id'],
                    'academic_year_id' => $item['academic_year_id'],
                    'school_type' => $item['school_type'],
                    'male' => $item['male'],
                    'female' => $item['female'],
                    'total' => $total,
                ]);

                $records[] = $record->load(['kpiRate', 'academicYear']);

                $schoolType = $item['school_type'];
            }

            // Automatically create other KPI rates for this year & school type with 0 values
            $allKpiIds = KpiRateData::pluck('id')->toArray();
            $existingKpiIds = KpiData::query()->where('academic_year_id', $academicYear->id)
                ->where('school_type', $schoolType)
                ->pluck('kpi_id')
                ->toArray();

            $missingKpiIds = array_diff($allKpiIds, $existingKpiIds);

            foreach ($missingKpiIds as $kpiId) {
                    $record = KpiData::create([
                    'kpi_id' => $kpiId,
                    'academic_year_id' => $academicYear->id,
                    'school_type' => $schoolType,
                    'male' => 0,
                    'female' => 0,
                    'total' => $this->calculateTotal(0,0,$academicYear->id,true),
                ]);
                $records[] = $record->load(['kpiRate', 'academicYear']);
            }

            return collect($records);
        });

        // KpiDataChanged::dispatch('store', $created);
        // return response()->json([
        //     'message' => 'KPI data created successfully',
        //     'data' => KpiDataResource::collection($created),
        // ], 201);

        return $this->success('KPI data created successfully', ['data' => KpiDataResource::collection($created)]);
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
    public function update(UpdateKpiDataRequest $request)
    {
        $validated = $request->validated();

        $updated = DB::transaction(function () use ($validated) {

            $results = [];
            foreach ($validated['items'] as $item) {
                $model = KpiData::findOrFail($item['id']);
                $total = $this->calculateTotal($item['male'], $item['female'], $model->academic_year_id, true);

                $model->update([
                    'male'   => $item['male'],
                    'female' => $item['female'],
                    'total'  => $total
                ]);

                $results[] = $model->fresh()->load([
                    'kpiRate',
                    'academicYear',
                ]);
            }
            return $results;
        });

        return $this->success('KPI data updated successfully', ['data' => KpiDataResource::collection(collect($updated))]);
    }

    /**
     * KPI Trends
     */
    public function kpiTrends(Request $request){
        
        $request->validate([[
            'kpi_rate' => [
                'string',
                'exists:kpi_rate_data,name',
                Rule::in(KpiRateData::pluck('name')->toArray()),
            ],
            'academic_year' => [
                'exists:academic_years,academic_year',
                Rule::in(AcademicYear::whereIn('status', ['active', 'default'])->pluck('academic_year')->toArray()),
            ],
            'school_type' => ['string', 'exists:school_types,name'],
        ]]);

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

        if (!$startYear) {
            return $this->error('Academic year not found.', 404);
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

        

        return $this->success('KPI trends and totals retrieved successfully', [
            'data' => [
                'kpi_trends' => $groupedTrends->toArray(),
                'kpi_trends_total' => $fiveYearStats,
            ],
        ]);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(KpiData $kpiData)
    {
        //
    }
}
