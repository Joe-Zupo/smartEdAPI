<?php

namespace App\Http\Controllers;

use App\Http\Requests\Users\ChangeUserPasswordRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\School;
use App\Http\Requests\Users\IndexUserRequest;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserController extends Controller
{

    /**
     * Index Users
     */
    public function index(IndexUserRequest $request)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        $sortBy = $request->input('sortBy', 'id');
        $sortOrder = $request->input('sortOrder', 'desc');

        $searchRequest = $request->input('search');

        $query = User::query();

        $getActive = $request->boolean('is_active');

        //Query Parameters Checks

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('has_school')) {
            $query->whereNotNull('school_id');
        }

        if ($request->has('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', 'like', '%'. $request->input('role') . '%');
            });
        }

        if ($searchRequest) {
            //search for activity descriptions
            $query->where('name', 'like', '%' . $searchRequest . '%')
                ->orWhere('username', 'like', '%' . $searchRequest . '%')
                ->orWhereHas('school', function ($s) use ($searchRequest) {
                    $s->where('school_name', 'like', '%' . $searchRequest . '%');
                });
        }

        $query->orderBy($sortBy, $sortOrder);
        $paginatedUsers = $query
            ->paginate($perPage);

        if (!$paginatedUsers->count()) {
            return $this->success('No more users available');
        }

        $transformedUsers = $paginatedUsers->map(function ($user) {
            return new UserResource($user);
        });

        $paginaton = $this->paginateReturn($paginatedUsers);

        return $this->success('Users fetched successfully', [
            'users' => UserResource::collection($transformedUsers),
            'pagination' => $paginaton
        ]);
    }

    /**
     * Create a new User
     */
    public function store(StoreUserRequest $request)
    {
        $validatedRequest = $request->validated();
        //dd($validatedRequest);
        DB::beginTransaction();

        $user = User::create($validatedRequest);
        $user->assignRole($validatedRequest['role']);
        if ($user->hasRole('School Account')) {
            if (isset($validatedRequest['school'])) {
                $user->school_id = $validatedRequest['school'] ?
                School::where('school_name', $validatedRequest['school'])->value('id') : null;
                $message = 'School Account fully initialized, account is set as active';
                $user->save();
            }else{
                $message = 'School Account partially initialized, account is set as inactive';
                $user['is_active'] = false;
            }
        } else {
            $message = "Account set to active";
        }

        DB::commit();
        return $this->success('User: ' . $user->name . ' Created Successfully', [
            'notice' => $message,
            'user' => new UserResource($user)
        ]);
    }

    /**
     * Fetch a User
     */
    public function show(User $user)
    {
        try {
            return $this->success(
                'User fetched successfully',
                ['user' => new UserResource($user)]
            );
        } catch (\Exception $e) {
            return $this->error('User not Found');
        }
    }

    /**
     * Update a User's Data
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $validatedRequest = $request->validated();
        DB::beginTransaction();

        $user->update($validatedRequest);

        DB::commit();
        return $this->success('User Updated successfully', [
                new UserResource($user)
        ]);
    }

    public function destroy(User $user)
    {

    }


    //Custom Functions


    /**
     * Change a user's password
     */
    public function changePassword(User $user, ChangeUserPasswordRequest $request)
    {
        $validatedRequest = $request->validated();

        DB::transaction(function () use ($user, $validatedRequest) {
            $user['password'] = Hash::make($validatedRequest['password']);
            $user->save();
            //DB::commit (if needed use try catch instead)
        });

        return $this->success('User password changed successfully', [
            'user' => new UserResource($user)
        ]);
    }

    /**
     * Toggle a user's status
     */
    public function changeStatus(User $user)
    {

        DB::transaction(function () use ($user) {
            $user->is_active = !$user->is_active;
            $user->save();
        });

        return $this->success('User Status Changed Successfully', [
            'user' => new UserResource($user)
        ]);
    }
}
