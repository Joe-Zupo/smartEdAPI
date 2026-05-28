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

        public function changeStatus(Request $request, AcademicYear $academicYear)
        {
            //$this->authorize('create', User::class);
            $validated = $request->validate([
                'status' => 'required|string|in:active,default,upcoming,archived'
            ]);

            DB::beginTransaction();

        try {

            $newStatus = $validated['status'];

            // Prevent manual changing of current default
            if ($academicYear->status === 'default') {

                DB::rollBack();

                return $this->error(
                    'Default academic year cannot be manually changed.'
                );
            }

            // ONLY upcoming can become default
            if ($newStatus === 'default') {

                if ($academicYear->status !== 'upcoming') {

                    DB::rollBack();

                    return $this->error(
                        'Only upcoming academic years can become default.'
                    );
                }

                $currentDefault = AcademicYear::where('status', 'default')->first();

                if (!$currentDefault) {

                    DB::rollBack();

                    return $this->error(
                        'No current default academic year found.'
                    );
                }

                $today = now();

                $defaultEndDate = Carbon::parse($currentDefault->end_date);
                $upcomingStartDate = Carbon::parse($academicYear->start_date);

                // Current default must already be finished
                if ($today->lt($defaultEndDate)) {

                    DB::rollBack();
                    return $this->error(
                        'Current default academic year is not yet finished.'
                    );
                }

                // Upcoming year must match current year
                if ($today->year !== $upcomingStartDate->year) {

                    DB::rollBack();

                    return $this->error(
                        'Upcoming academic year cannot yet become default.'
                    );
                }

                // Demote old default
                $currentDefault->update([
                    'status' => 'active'
                ]);

                // Promote upcoming
                $academicYear->update([
                    'status' => 'default'
                ]);

            } else {

                // Prevent manually setting default
                if ($academicYear->status === 'default') {

                    DB::rollBack();

                    return $this->error(
                        'Default academic year cannot be modified.'
                    );
                }

                $academicYear->update([
                    'status' => $newStatus
                ]);
            }

            DB::commit();

            return $this->success(
                'Successfully changed status',
                [
                    'academic_year' => new AcademicYearResource($academicYear->fresh())
                ]
            );

        } catch (\Exception $e) {

            DB::rollBack();

            return $this->error(
                'Cannot change current status'
            );
        }
    }

    public function destroy(AcademicYear $academicYear)
    {
        //
    }
}
