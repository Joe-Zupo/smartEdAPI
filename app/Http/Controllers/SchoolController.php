<?php

namespace App\Http\Controllers;

use App\Http\Requests\Schools\IndexSchoolRequest;
use App\Http\Requests\Schools\StoreSchoolRequest;
use App\Http\Requests\Schools\UpdateSchoolRequest;
use App\Http\Resources\SchoolResource;
use Illuminate\Validation\Rule;
use App\Models\School;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\SchoolType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;

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
                'schoolHead'
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

        $user = User::query()->where('name', 'like', '%'. $request['school_head'] . '%');
        Arr::forget($validated, 'school_head');

        $validated['school_type_id'] = $typeID;

        $street = $validated['street'];
        $barangay = $validated['barangay'];
        $city = $validated['city'];
        $province = $validated['province'];
        $validated['address'] = "{$street}, {$barangay}, {$city}, {$province}";

        //image path appending and storage
            if ($request->hasFile('image')){
                // Optional old image deletion
                $validated['image'] = $request->file('image')->store('school_images', 'public');
            }
        
        $school = School::create($validated);
        $user->update(['school_id' => $school['id']]);
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

            //image path appending and storage
            if ($request->hasFile('image')){
                // Optional old image deletion
                 if ($school->image && Storage::disk('public')->exists($school->image)) {
                    Storage::disk('public')->delete($school->image);
                }

                $validated['image'] = $request->file('image')->store('school_images', 'public');
            }

            if($request->filled('school_head')){
                $oldHead = User::where('school_id', $school->id)->first();
                $user = User::query()->where('name', $request['school_head']);
                Arr::forget($validated, 'school_head');
                $oldHead->school_id = null; $oldHead->save();
                $user->update(['school_id' => $school->id]);
            }

            $school->update($validated);
            DB::commit();

            return $this->success(
                'School updated successfully',
                [
                    'school' => new SchoolResource(
                        $school->refresh()->load([
                            'schoolType',
                            'schoolHead'
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
                        'schoolHead'
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

    /**
     * Public Index Schools
     */
    public function publicIndex(Request $request)
    {
        $validated = $request->validate([
            'district' => ['nullable', Rule::in(['North', 'South', 'East', 'West'])],
            'search' => ['nullable', 'string'],
            'per_page' => ['nullable', 'integer', 'min:1'],
            'all' => ['nullable', 'boolean'],
        ]);

        $perPage = $validated['per_page'] ?? 5;
        $getAll = $validated['all'] ?? false;
        $searchRequest = $validated['search'] ?? null;

        $query = School::query()
            ->with('schoolType');

        if ($request->filled('district')) {
            $query->where('district', $validated['district']);
        }

        if ($searchRequest) {
            $query->where(function ($q) use ($searchRequest) {
                $q->where('school_name', 'like', "%{$searchRequest}%")
                ->orWhere('school_code', 'like', "%{$searchRequest}%");
            });
        }

        $schools = $getAll
            ? $query->get()
            : $query->paginate($perPage)->appends($request->query());

        if ($schools->isEmpty()) {
            return $this->success(
                'No schools found',
                [
                    'data' => [],
                    'pagination' => $getAll ? null : null,
                ]
            );
        }

        return $this->success(
            'Schools retrieved successfully',
            [
                'data' => SchoolResource::collection($schools),
                'pagination' => $getAll
                    ? null
                    : $this->paginateReturn($schools),
            ]
        );
    }

    /**
     * Show Public School
     * 
     * Display the specified resource for public.
     */
    public function publicShow(School $school)
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
}