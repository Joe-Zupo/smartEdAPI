<?php

namespace App\Http\Controllers;

use App\Http\Requests\Schools\IndexSchoolRequest;
use App\Http\Requests\Schools\StoreSchoolRequest;
use App\Http\Requests\Schools\UpdateSchoolRequest;
use App\Http\Resources\SchoolResource;
use App\Models\School;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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
        $getAll = $request->boolean('all')?? false;

        $query = School::query()
            ->with([
                'schoolType',
                'schoolHead'
            ]);

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

        if ($request->filled('district')){
            $query->where('district','like', '%' . $request->district . '%');
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
            'data' => [
                'schools' => SchoolResource::collection($schools),
            ],
        ];

        if (!$getAll) {
            $response['pagination'] = $this->paginateReturn($schools);
        }

        return $this->success(
            'Schools fetched successfully',
            [
                $response
            ]
        );
    }

    /**
     * Store School
     * 
     */
    public function store(StoreSchoolRequest $request)
    {

        DB::beginTransaction();

        // try {
            //Pre-req of request for validation
            if ($request->school_head){
                $user = User::where('name', 'like', '%' . $request->school_head . '%')->first();
                $headID = $user->value('id');
                if(!$headID){
                        DB::rollBack();
                        return $this->error('User not found for school head input');
                    }
                if($user['is_head']){
                        DB::rollBack();
                        return $this->error("User is already head of another school");
                    }else if(!$user->hasRole('School Account')){
                        DB::rollBack();
                        return $this->error("User is not eligible; because they are not a School Account");
                    }
                $request['school_head_id'] = $headID;
                unset($request['school_head']);
            }

            if($request->school_type){
                $typeID = SchoolType::where('name', $request->school_type)->value('id');
                    if(!$typeID){
                        DB::rollBack();
                        return $this->error('School type not found for school type input');
                    }
                $request['school_type_id'] = $typeID;
                unset($request['school_type']);
            }

            $validated = $request->validated();

            // if ($request->hasFile('image')) {
            //     $validated['image'] = $request
            //         ->file('image')
            //         ->store('school_images', 'public');
            // }

            $school = School::create($validated);
            $user['school_id'] = $school->id;
            $user['position'] = $validated['position'];
            $user['is_head'] = true;
            $user->save();

            DB::commit();

            return $this->success(
                'School created successfully',
                [
                    'school' => new SchoolResource(
                        $school->load([
                            'schoolType',
                            'schoolHead'
                        ])
                    )
                ]
            );

        // } catch (\Exception $e) {

        //     DB::rollBack();

        //     return $this->error(
        //         'Failed to create school'
        //     );
        // }
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
                        'schoolHead'
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
        $validated = $request->validated();

        DB::beginTransaction();

        try {

            if($request->school_type){
                $typeID = SchoolType::where('name', $request->school_type)->value('id');
                $validated['school_type_id'] = $typeID;
                unset($validated['school_type']);
            }
            if($request->filled('school_head')){
                $user = User::where('name', 'like', '%' . $request->school_head . '%')->first();

                    if(!$user->hasRole('School Account')){
                        DB::rollBack();
                        return $this->error('This user is not assigned as a School Account, therefore is not a eligible for school head'); //Ask if other accs can be heads
                    }

                $headID = $user->value('id');
                    if(!$headID){
                        DB::rollBack();
                        return $this->error('User not found for school head input');
                    }

                $prevHead = User::where('school_id', $school->id)->where('is_head',true)->first(); //resets previous head if any
                    if($prevHead){
                        $prevHead->is_head = false;
                        $prevHead->position = null;
                        $prevHead->save();
                    }

                $request['school_head_id'] = $headID; //updates school
                $user['school_id'] = $school->id; //updates user
                $user['position'] = $validated['position'];
                $user['is_head'] = true;
                $user->save();
            }

            $school->update($validated);
            DB::commit();

            return $this->success(
                'School updated successfully',
                [
                    'school' => new SchoolResource(
                        $school->refresh()->load([
                            'schoolType',
                            'schoolHead']))]);
        } catch (\Exception $e) {

            DB::rollBack();

            return $this->error(
                'Failed to update school'
            );
        }
    }

    /**
     * Upload Image for School
     */
    public function uploadImage(Request $request, School $school){
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
                    return $this->success('Successfully updated school image',[
                        'data' => new SchoolResource($school->refresh()->load([
                            'schoolType',
                            'schoolHead'
                        ]))
                    ]);
                }
            }else{
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