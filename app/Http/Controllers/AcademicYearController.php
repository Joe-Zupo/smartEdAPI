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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AcademicYearController extends Controller
//Next Task
{
    /**
     * Index of Academic Years
     */
    public function index(IndexAcademicYearsRequest $request)
    {
        $perPage = $request->get('per_page', 5);
        $sortBy = $request->input('sortBy', 'id');
        $sortOrder = $request->input('sortOrder', 'desc');
        $displayPractical = $request->boolean('withoutUA', false);

        $request->validated();

        $query = AcademicYear::query();

        $user = $request->user();
        //Query Parameters
        if(!$user->hasRole('System Admin') || $displayPractical){
            $query->whereNotIn('status', ['upcoming','archived']);
        }

        if ($request->filled('status') && $user->hasRole('System Admin')){
            $query->where('status', 'like' , '%' . $request->input('status') . "%");
        }
        
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
    //$this->authorize('create', User::class);
    $validated = $request->validated();

    DB::beginTransaction();

    try {

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $today = now();

        $status = 'active';

        // Determine status
        if ($today->between($startDate, $endDate)) {

            // Only one default allowed
            if (AcademicYear::where('status', 'default')->exists()) {

                DB::rollBack();

                return $this->error(
                    'There is already a default academic year.'
                );
            }

            $status = 'default';

        } elseif ($startDate->gt($today)) {
            $status = 'upcoming';
        }

        $academicYear = AcademicYear::create([
            ...$validated,
            'status' => $status
        ]);

        DB::commit();

        return $this->success(
            'Academic Year Created Successfully!',
            [
                'academic_year' => new AcademicYearResource($academicYear)
            ]
        );

    } catch (\Exception $e) {

        DB::rollBack();

        return $this->error(
            'Academic Year could not be created'
        );
    }
}

    /**
     * Display the specified resource.
     */
    public function show(AcademicYear $academicYear)
    {
       // $this->authorize('view', User::class);
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
        //$this->authorize('update', User::class);
        $validated = $request->validated();

        DB::beginTransaction();

        try {
            unset($validated['status']);
            $academicYear->update($validated);
            DB::commit();

            return $this->success(
                'Academic Year Updated Successfully',
                [
                    'academic_year' => new AcademicYearResource($academicYear)
                ]
            );

        } catch (\Exception $e) {

            DB::rollBack();

            return $this->error(
                'Failed to Update Academic Year'
            );
        }
    }

    /**
     * Change Year Status
     */
        public function changeStatus(AcademicYear $academicYear)
        {
            //End Goals
            //Upcoming to Default and Default to Active-> with checking if now between or on start and end date of upcoming
            //Default to Active and Upcoming to new Default -> checks if Upcoming is eligible to be the new default
            //Active to Archived DONE
            //Archived to Active DONE

            DB::beginTransaction();

            $currentDefault = AcademicYear::where('status', 'default')->first();
            $today = now();

            $defaultEndDate = Carbon::parse($currentDefault->end_date);


            //NEW
            
            //Upcoming to Default and Default to Active

            if($academicYear->status === 'upcoming'){
                    if($today->between($academicYear->start_date, $academicYear->end_date)){
                        $currentDefault->update(['status' => 'active']);
                        $academicYear->update(['status' => 'default']);
                        $action = "Changed from Upcoming to Default and Old Default to Active";
                        DB::commit();
                        return $this->success('Academic Year successfully changed status', [
                            'action' => $action,
                            'academic_year' => new AcademicYearResource($academicYear),
                            'old_default' => new AcademicYearResource($currentDefault)
                        ]);
                }else{
                    DB::rollBack();
                    return $this->error('Changing the default does not match current time');
                }
            }

            // Default to Active and Upcoming to Default

            if($academicYear->status === 'default'){
                //check if ended
                if($today->lte($defaultEndDate)){
                    DB::rollBack();
                    return $this->error('Default year has still not concluded');
                }else{
                    $upcomingYear = AcademicYear::where('status', 'upcoming')
                    ->orderBy('start_date')
                    ->first();
                
                    if(!$upcomingYear){
                        DB::rollBack();
                        return $this->error("Could not update, no eligible upcoming years found");
                    }else{
                        $academicYear->update(['status' => 'active']);
                        $upcomingYear->update(['status' => 'default']);
                        $action = "Changed from Default to Active and Upcoming to Default";
                        DB::commit();
                        return $this->success('Academic Year successfully changed status', [
                            'action' => $action,
                            'academic_year' => new AcademicYearResource($academicYear),
                            'new_default' => new AcademicYearResource($upcomingYear)
                        ]);
                    }
                }
            }

            if($academicYear->status === 'active'){
                $academicYear->update(['status' => 'archived']);
                $action = "Changed from Active to Archived";
                DB::commit();
                return $this->success('Academic Year successfully changed status', [
                    'action' => $action,
                    'academic_year' => new AcademicYearResource($academicYear)
                ]);
            }

            if($academicYear->status === 'archived'){
                $academicYear->update(['status' => 'active']);
                $action = "Changed from Archived to Active";
                DB::commit();
                return $this->success('Academic Year successfully changed status', [
                    'action' => $action,
                    'academic_year' => new AcademicYearResource($academicYear)
                ]);
            }

            if(!$academicYear){
                DB::rollBack();
                return $this->error('Could not find Academic Year Status');
            }
    }

    public function destroy(AcademicYear $academicYear)
    {
        //
    }
}
