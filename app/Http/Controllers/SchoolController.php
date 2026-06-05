<?php

namespace App\Http\Controllers;

use App\Http\Requests\Schools\IndexSchoolRequest;
use App\Http\Requests\Schools\StoreSchoolRequest;
use App\Http\Requests\Schools\UpdateSchoolRequest;
use App\Http\Resources\SchoolResource;
use App\Models\School;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\SchoolType;
use App\Models\User;
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
        $sortBy = $validated['sortBy'] ?? 'id';
        $sortOrder = $validated['sortOrder'] ?? 'asc';
        $getAll = $request['all'] ?? false;

        $query = School::query()
            ->with([
                'schoolType',
            ]);

        if ($request->filled('school_name')) {
            $query->where('school_name', 'like', '%' . $request->school_name . '%');
        }

        if ($request->filled('school_code')) {
            $query->where('school_code', 'like', '%' . $request->school_code . '%');
        }

        if ($request->filled('school_type')) {
            $query->whereHas('schoolType', function ($q) use ($request) {
                $q->where('name', $request->school_type);
            });
        }

        if ($request->filled('district')) {
            $query->where('district', 'like', '%' . $request->district . '%');
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

        if ($getAll) {
            $schools = $query->get();
        } else {
            $schools = $query->paginate($perPage)->appends($request->query());
        }

        if ($schools->isEmpty()) {
            return response()->json(['message' => 'No schools found']);
        }

        $response = [
            'schools' => SchoolResource::collection($schools),
        ];

        if (!$getAll) {
            $response['pagination'] = $this->paginateReturn($schools);
        }

        return $this->success(
            'Schools fetched successfully',
            $response
        );
    }

    /**
     * Store School
     * 
     */
    public function store(StoreSchoolRequest $request)
    {

        DB::beginTransaction();
        if ($request->school_type) {
            $typeID = SchoolType::where('name', $request['school_type'])->value('id');
            if (!$typeID) {
                DB::rollBack();
                return $this->error('School type not found for school type input');
            }
            $request->request->remove('school_type');
            $validated = $request->validated();
        }else{
            $validated = $request->validated();
            $typeID = $validated['school_type_id'];
        }

        $validated['school_type_id'] = $typeID;

        $street = $validated['street'];
        $barangay = $validated['barangay'];
        $city = $validated['city'];
        $province = $validated['province'];

        $validated['address'] = "{$street}, {$barangay}, {$city}, {$province}";

        $school = School::create($validated);

        DB::commit();

        return $this->success(
            'School created successfully',
            [
                'school' => new SchoolResource(
                    $school->load([
                        'schoolType'
                    ])
                )
            ]
        );
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
                    ])
                )
            ]
        );
    }

    /**
     * Update School
     * 
     */
    public function update(UpdateSchoolRequest $request, School $school)
    {
            DB::beginTransaction();
            $validated = $request->validated();

            if ($request->school_type) {
                $typeID = SchoolType::where('name', $request->school_type)->value('id');
                $validated['school_type_id'] = $typeID;
                unset($validated['school_type']);
            }
            
            //address appending
             $addressArray = Str::of($school->address)->explode(', ');
            if(isset($validated['street'])){
                $street = $validated['street'];
            }else{
                $street = $addressArray[0];
            }
            if(isset($validated['barangay'])){
                $barangay = $validated['barangay'];
            }else{
                $barangay =  $addressArray[1];
            }
            if(isset($validated['city'])){
                $city = $validated['city'];
            }else{
                $city = $addressArray[2];
            }
            if(isset($validated['province'])){
                $province = $validated['province'];
            }else{
                $province = $addressArray[3];
            }
            $validated['address'] = "{$street}, {$barangay}, {$city}, {$province}";


            $school->update($validated);
            DB::commit();

            return $this->success(
                'School updated successfully',
                [
                    'school' => new SchoolResource(
                        $school->refresh()->load([
                            'schoolType',
                        ])
                    )
                ]
            );
    }

    /**
     * Upload Image for School
     */
    public function uploadImage(Request $request, School $school)
    {
        $user = Auth::user();
        DB::beginTransaction();
        if ($request->hasFile('image')) {

            if ($user->hasRole('School Account') && $user->school_id !== $school->id) {
                DB::rollBack();
                return $this->error('Unauthorized access to this school', 403);
            } else {
                // Optional old image deletion
                if ($school->image && Storage::disk('public')->exists($school->image)) {
                    Storage::disk('public')->delete($school->image);
                }

                if (!$request->hasFile('image')) {
                    return $this->error('No valid image provided');
                }

                $validated['image'] = $request->file('image')->store('school_images', 'public');
                $school->image = $validated['image'];
                $school->save();
                DB::commit();
                return $this->success('Successfully updated school image', [
                    'data' => new SchoolResource($school->refresh()->load([
                        'schoolType',
                    ]))
                ]);
            }
        } else {
            return $this->error('No image provided');
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