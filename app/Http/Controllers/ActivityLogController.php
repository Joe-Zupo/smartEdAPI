<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use App\Http\Requests\ActivityLogs\IndexActivityLogRequest;
use App\Http\Resources\ActivityLogResource;
use App\Models\User;
use App\Models\School;
use App\Policies\ActivityLogPolicy;

class ActivityLogController extends Controller
{
    /**
     * Activity Logs Index Endpoint
     *
     * Retrieves paginated activity logs with optional
     * filtering and searching capabilities.
     *
     */
    public function index(IndexActivityLogRequest $request)
    {
        $this->authorize('viewAny', User::class);
        $search = $request->input('search');
        $perPage = $request->get('per_page', 5);
        $sortBy = $request->input('sortBy', 'id');
        $sortOrder = $request->input('sortOrder', 'desc'); 

        $request->validated();

        $query = Activity::with(['causer.school'])
            ->select([
                'id',
                'log_name',
                'description',
                'properties',
                'causer_type',
                'causer_id',
                'created_at'
            ]);

        //search for where causer did a certain action in: 
        //Logged In,Logged Out,Submitted Data,Returned Data,Approved Data
        if ($request->has('action'))
            $query->where('log_name', 'like' , '%' . $request->input('action') . "%");

        //search for where causer has the same school
        if ($request->has('school')) {
            $query->whereHas('causer.school', function ($q) use ($request) {
                $q->where('school_name','like','%' . $request->query('school') . '%');
            });
        }

        //enable searching stored in results
        if ($search) {
            
            //search for activity descriptions
            $activityIds =
            $query->where('description', 'like', '%' . $search .'%') 
            
            //search for users
            ->orwhereHas('causer', function ($userQuery) use ($search){
                $userQuery->where('name','like','%' . $search . '%');
            });
        }

        $query->orderBy($sortBy, $sortOrder);
        $activityLogs = $query->paginate($perPage)->appends($request->query());

        if ($activityLogs->isEmpty()) {
            return response()->json(['message' => 'No activity logs found']);
        }

        return $this->success('Activity logs retrieved successfully', [
            'data' => ActivityLogResource::collection($activityLogs),
            'pagination' => $this->paginateReturn($activityLogs)
        ]);
    }

    // public function recent(IndexActivityLogRequest $request)
    // {
    //     $activityLogs = Activity::latest()->take(2)->get();

    //     if ($activityLogs->isEmpty()) {
    //         return response()->json(['message' => 'No activity logs found']);
    //     }

    //     return $this->success('Activity logs retrieved successfully', [
    //         'data' => ActivityLogResource::collection($activityLogs),
    //     ]);
    // }

}
