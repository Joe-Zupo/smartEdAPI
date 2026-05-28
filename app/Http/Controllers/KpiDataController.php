<?php

namespace App\Http\Controllers;

use App\Models\KpiData;
use Illuminate\Http\Request;
use App\Http\Requests\KPI\IndexKpiDataRequest;
use App\Http\Resources\KpiDataResource;
use Illuminate\Support\Facades\DB;

class KpiDataController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexKpiDataRequest $request)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('perPage', 10);
        $sortBy = $request->input('sortBy', 'id');
        $sortOrder = $request->input('sortOrder', 'desc');

        $request->validated();

         $query = KpiData::query();

        if ($request->filled('school_type')) {
            $query->where('schoolType', 'like', '%'. $request->school_type . '%');
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

        $transformedData = $paginatedData->map(function($user){
            return new KpiDataResource($user);
        });

        $paginaton = $this->paginateReturn($paginatedData);

        return $this->success('KPI data fetched successfully',[
           'users' => $transformedData,
           'pagination' => $paginaton 
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
     * Display the specified resource.
     */
    public function show(KpiData $kpiData)
    {
        return $this->success('KPI data fetched successfully',
        ['kpi_data' => new KpiDataResource($kpiData)]);
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
