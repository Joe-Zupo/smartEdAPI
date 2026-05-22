<?php

namespace App\Http\Controllers;

use App\Http\Requests\AcademicYears\IndexAcademicYearsRequest;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use App\Policies\AcademicYearPolicy;
use App\Models\User;
use App\Http\Resources\AcademicYearResource;
use App\Http\Requests\AcademicYears\StoreAcademicYearRequest;
use App\Http\Requests\AcademicYears\UpdateAcademicYearRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AcademicYearController extends Controller
//Next Task
{
    /**
     * Index of Academic Years
     */
    public function index(IndexAcademicYearsRequest $request)
    {
        $this->authorize('viewAny', User::class);
        $perPage = $request->get('per_page', 5);
        $sortBy = $request->input('sortBy', 'id');
        $sortOrder = $request->input('sortOrder', 'desc');

        $request->validated();

        $query = AcademicYear::query();

        //Query Parameters

        if ($request->has('status'))
            $query->where('status', 'like' , '%' . $request->input('status') . "%");
        
        if ($request->has('academic_year'))
            $query->where('academic_year', 'like' , '%' . $request->input('academic_year') . "%");

            $query->orderBy($sortBy, $sortOrder);
            $academicYears = $query->paginate($perPage)->appends($request->query());

        return $this->success('Academic Years Retrieved Successfully!', [
            'data' => AcademicYearResource::collection($academicYears),
            'pagination' => $this->paginateReturn($academicYears)
        ]);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAcademicYearRequest $request)
    {
        $validatedRequest = $request->validated();

        DB::beginTransaction();

        $startDate = Carbon::parse($validatedRequest['start_date']);
        $endDate = Carbon::parse($validatedRequest['end_date']);
        $today = now();

        $status = 'active';

        try{

            if($today->between($startDate,$endDate)){
                $status = 'default';
            }else if($startDate->gt($today)){
                $status = 'upcoming';
            }

            if($status === 'default' && AcademicYear::where('status', 'default')->exists()){
                $defaultYear = AcademicYear::where('status', 'default')->get();
                return $this->error('Could not create year, for there should only be one academic year with default status',
                ['default_year' => new AcademicYearResource($defaultYear)]
                );
            }

            $mergedRequest = array_merge($validatedRequest, ['status' => $status]);
            $academicYear = AcademicYear::create($mergedRequest);

            DB::commit();
            return $this->success('Academic Year Created Successfully!', [
                'academic_year' => new AcademicYearResource($academicYear)
            ]);
        }catch(\Exception $e){
            DB::rollback();
            return $this->error('Academic Year could not be created');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(AcademicYear $academicYear)
    {
        try{
            return $this->success('Successfully fetched Academic Year',[
                'academic_year' => new AcademicYearResource($academicYear)
            ]);
        }catch(\Exception $e){
            return $this->error('Could not fetch the Academic Year');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAcademicYearRequest $request, AcademicYear $academicYear)
    {
        $validatedRequest = $request->validated();
        DB::beginTransaction();

        try{
            $academicYear->update($validatedRequest);

            DB::commit();
            return $this->success('Academic Year Updated Successfully',
            ['academic_year' => new AcademicYearResource($academicYear)]);
        }catch(\Exception $e){

            DB::rollBack();
            return $this->error('Failed to Update Academic Year');
        }
    }

    public function changeStatus(AcademicYear $academicYear, Request $request){

        $validatedRequest = $request->validate(['status' => 'required|string|in:active,default.upcoming,archived']);
        DB::beginTransaction();
        
        try{
            switch($academicYear->status){
            case 'default': 
                $startDate = Carbon::parse($academicYear['start_date']);
                $endDate = Carbon::parse($academicYear['end_date']);
                $today = now();
                if($today->between($startDate,$endDate)){ //Checks if eligible for change
                    DB::rollBack();
                    return $this->error('Cannot change current status');
                    break;
                }else{
                    $academicYear['status'] = $validatedRequest['status'];
                    break;
                }
            
            case 'active':
            case 'upcoming':
            case 'archived':
            default:
                $academicYear['status'] = $validatedRequest['status'];
                break;
            }

            DB::commit();
            return $this->success('Successfully changed status', [
                        'academic_year' => new AcademicYearResource($academicYear)]);
        }catch(\Exception $e){
            DB::rollBack();
            return $this->error('Cannot change current status');
        }
            
    }

    public function destroy(AcademicYear $academicYear)
    {
        //
    }
}
