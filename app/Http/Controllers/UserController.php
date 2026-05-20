<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Http\Requests\Users\IndexUserRequest;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexUserRequest $request)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('perPage', 10);
        $sortBy = $request->input('sortBy', 'created_at');
        $sortOrder = $request->input('sortOrder', 'desc');

        $searchRequest = $request->input('search');

        $query = User::query();

        $getActive = $request->boolean('is_active');

        //Query Parameters Checks
        if($request->has('role')){
            $query->hasRole(['School Account', 'Division Admin', 'System Admin']);
        }

        if ($request->has('is_active')){
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('has_school')){
            $query->whereNotNull('school_id')->get();
        }

        if ($searchRequest){
            //search for activity descriptions
            $userIDs =
            $query->where('name', 'like', '%' . $searchRequest .'%')
            ->orWhere('username', 'like', '%' . $searchRequest . '%')
            ->orWhereHas('school', function ($s) use ($searchRequest){
                $s->where('school.school_name','like','%' .$searchRequest. '%');
            });
        }

        $query->orderBy($sortBy, $sortOrder);
        $paginatedUsers = $query
            ->paginate($perPage);

        if(!$paginatedUsers->count()){
            return $this->success('No more users available');
        }

        $transformedUsers = $paginatedUsers->map(function($user){
            return new UserResource($user);
        });

        $paginaton = $this->paginateReturn($paginatedUsers);

        return $this->success('Users fetched successfully',[
           'users' => $transformedUsers,
           'pagination' => $paginaton 
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        
    }
}
