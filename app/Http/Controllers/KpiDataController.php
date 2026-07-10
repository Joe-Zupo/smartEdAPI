<?php

namespace App\Http\Controllers;

use App\Events\PublicKpiDataChanged;
use App\Events\PublicKPITrendsChanged;
use App\Models\KpiData;
use Illuminate\Http\Request;
use App\Http\Resources\KpiDataResource;
use Illuminate\Support\Facades\DB;
use App\Models\KpiRateData;
use App\Models\AcademicYear;
use Illuminate\Validation\Rule;
use App\Http\Requests\KPI\StoreKpiDataRequest;
use App\Helpers\calculateTotal;
use App\Http\Requests\KPI\UpdateKpiDataRequest;
use App\Services\KpiDataService;
use App\Http\Resources\KPIDataService\KPIIndexResource;
use App\Http\Resources\KPIDataService\KPITrendResource;

class KpiDataController extends Controller
{
    use calculateTotal;
    /**
     * KPI Data Index
     */
    public function index(Request $request, KpiDataService $service)
    {
        $this->authorize('viewAny', KpiData::class);
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

        $data = $service->getIndex($request, false, null);

        if($data === 'Empty Query'){
            return $this->success('No KPI data Records Found');
        }

            return $this->success(
            'KPI data retrieved successfully',
            new KpiIndexResource(
                $data
            )
        );
    }

    public function publicIndex(Request $request, KpiDataService $service){
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
                'position' => Rule::in(['Schools Division Superintendent', 'Assistant Schools Division Superintendent'])
            ]);

            return $this->success(
            'KPI data retrieved successfully',
            new KpiIndexResource(
                $service->getIndex($request, true, null)
            )
        ); 
    }

    /**
     * Store KPI data record
     */
    public function store(StoreKpiDataRequest $request)
    {
        $this->authorize('create', KpiData::class);
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
        $this->authorize('view', KpiData::class);
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
        $this->authorize('update', KpiData::class);
        $validated = $request->validated();

        $updated = DB::transaction(function () use ($validated) {

            $results = [];
            $yearID = null;
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

                $yearID = $model->academic_year_id;
            }
            //PublicKpiDataChanged::dispatch($yearID);
            //PublicKPITrendsChanged::dispatch($yearID);
            return $results;
        });

        return $this->success('KPI data updated successfully', ['data' => KpiDataResource::collection(collect($updated))]);
    }

    /**
     * KPI Trends
     */
    public function kpiTrends(Request $request, KpiDataService $service){

        $this->authorize('viewAny', KpiData::class);
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

        if (!$startYear) {
            return $this->error('Academic year not found.', 404);
        }

        $kpiId = null;
        if($request->filled('kpi_rate')){
            $kpiId = KpiRateData::query()->where('name', $request->input('kpi_rate'))->value('id');
            if(!$kpiId){
                return $this->error('KPI Rate not found!', 404);
            }
        }

            return $this->success(
            'KPI trends and totals retrieved successfully',
            new KpiTrendResource(
                $service->getKpiTrends($request, false, null)
            )
        );


    }

    /**
     * Public KPI Trends
     */
    public function publicKPITrends(Request $request, KpiDataService $service){
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

            return $this->success(
            'KPI trends and totals retrieved successfully',
            new KpiTrendResource(
                $service->getKpiTrends($request, true, null)
            )
        );

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(KpiData $kpiData)
    {
        //
    }
}
