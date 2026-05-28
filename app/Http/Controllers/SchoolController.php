<?php

namespace App\Http\Controllers;

use App\Http\Requests\Schools\IndexSchoolRequest;
use App\Http\Requests\Schools\StoreSchoolRequest;
use App\Http\Requests\Schools\UpdateSchoolRequest;
use App\Http\Resources\SchoolResource;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SchoolController extends Controller
{
    /**
     * Index Schools
     */
    public function index(IndexSchoolRequest $request)
    {
        $validated = $request->validated();

        $perPage = $validated['per_page'] ?? 5;
        $sortBy = $validated['sortBy'] ?? null;
        $sortOrder = $validated['sortOrder'] ?? null;

        $query = School::query()
            ->with([
                'schoolType',
                'barangay'
            ]);

        // Query Parameters
        if ($request->filled('school_name')) {
            $query->where('school_name', 'like', '%' . $request->school_name . '%');
        }

        if ($request->filled('school_code')) {
            $query->where('school_code', 'like', '%' . $request->school_code . '%');
        }

        if ($request->filled('school_type')) {
            $query->whereHas('schoolType', function($q) use ($request){
                $q->where('name',$request->school_type);
            });
        }

        if ($request->filled('barangay_id')) {
            $query->where('barangay_id', $request->barangay_id);
        }

        // Sorting
        if ($sortBy && $sortOrder) {

            $allowedSorts = [
                'id',
                'school_name',
                'school_code',
                'created_at',
            ];

            if (!in_array($sortBy, $allowedSorts)) {
                $sortBy = 'id';
            }

            $query->orderBy($sortBy, $sortOrder);

        } else {

            $query->orderBy('created_at', 'desc');
        }

        $schools = $query
            ->paginate($perPage)
            ->appends($request->query());

        return $this->success(
            'Schools fetched successfully',
            [
                'schools' => SchoolResource::collection($schools),
                'pagination' => $this->paginateReturn($schools)
            ]
        );
    }

    /**
     * Store School
     */
    public function store(StoreSchoolRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();

        try {

            if ($request->hasFile('image')) {
                $validated['image'] = $request
                    ->file('image')
                    ->store('school_images', 'public');
            }

            $school = School::create($validated);

            DB::commit();

            return $this->success(
                'School created successfully',
                [
                    'school' => new SchoolResource(
                        $school->load([
                            'schoolType',
                            'barangay'
                        ])
                    )
                ]
            );

        } catch (\Exception $e) {

            DB::rollBack();

            return $this->error(
                'Failed to create school'
            );
        }
    }

    /**
     * Show School
     */
    public function show(School $school)
    {
        return $this->success(
            'School fetched successfully',
            [
                'school' => new SchoolResource(
                    $school->load([
                        'schoolType',
                        'barangay'
                    ])
                )
            ]
        );
    }

    /**
     * Update School
     */
    public function update(UpdateSchoolRequest $request, School $school)
    {
        $validated = $request->validated();

        DB::beginTransaction();

        try {

            if ($request->hasFile('image')) {

                // Optional old image deletion
                // if ($school->image && Storage::disk('public')->exists($school->image)) {
                //     Storage::disk('public')->delete($school->image);
                // }

                $validated['image'] = $request
                    ->file('image')
                    ->store('school_images', 'public');
            }

            $school->update($validated);

            DB::commit();

            return $this->success(
                'School updated successfully',
                [
                    'school' => new SchoolResource(
                        $school->refresh()->load([
                            'schoolType',
                            'barangay'
                        ])
                    )
                ]
            );

        } catch (\Exception $e) {

            DB::rollBack();

            return $this->error(
                'Failed to update school'
            );
        }
    }

    /**
     * Delete School
     */
    public function destroy(School $school)
    {
        DB::beginTransaction();

        try {

            $school->delete();

            DB::commit();

            return $this->success(
                'School deleted successfully'
            );

        } catch (\Exception $e) {

            DB::rollBack();

            return $this->error(
                'Failed to delete school'
            );
        }
    }
}