<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use App\Http\Requests\ActivityLogs\IndexActivityLogRequest;
use App\Http\Resources\ActivityLogResource;
use App\Models\User;
use App\Models\School;

class ActivityLogController extends Controller
{
    /**
     * Activity Logs Index Endpoint
     *
     * Retrieves paginated activity logs with optional
     * filtering and searching capabilities.
     *
     * ---------------------------------------------------
     * Query Parameters
     * ---------------------------------------------------
     *
     * action : string (optional)
     * ---------------------------------------------------
     * Filters activity logs by action/log name.
     *
     * Example:
     * ?action=Logged In
     *
     * Performs a exact match
     *
     * Supported values include:
     * - Logged In
     * - Logged Out
     * - Submitted Data
     * - Returned Data
     * - Approved Data
     *
     *
     * school : string (optional)
     * ---------------------------------------------------
     * Filters activity logs where the causer's
     * related school name matches the provided value.
     *
     * Example:
     * ?school=CCIS
     *
     * Searches through:
     * Activity -> Causer -> School
     *
     * Performs a partial match using LIKE.
     *
     *
     * search : string (optional)
     * ---------------------------------------------------
     * Global search query.
     *
     * Searches:
     * - activity descriptions
     * - causer/user names
     *
     * Example:
     * ?search=joseph
     *
     *
     * per_page : integer (optional)
     * ---------------------------------------------------
     * Determines the number of records returned
     * per paginated response.
     *
     * Default:
     * 5
     *
     * Example:
     * ?per_page=10
     *
     *
     * page : integer (optional)
     * ---------------------------------------------------
     * Specifies the pagination page number.
     *
     * Example:
     * ?page=2
     *
     *
     * ---------------------------------------------------
     * Example Request
     * ---------------------------------------------------
     *
     * GET /api/activity-logs?
     *     action=Logged In&
     *     school=CCIS&
     *     search=joseph&
     *     per_page=10&
     *     page=1
     *
     *
     * ---------------------------------------------------
     * Response
     * ---------------------------------------------------
     *
     * Returns a paginated JSON response containing:
     * - activity logs
     * - causer information
     * - school information
     * - pagination metadata
     *
     */
    public function index(IndexActivityLogRequest $request)
    {
        $search = $request->input('search');
        $perPage = $request->get('per_page', 5);

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

        //search for where causer did a certain action in: Logged In,Logged Out,Submitted Data,Returned Data,Approved Data
        if ($request->has('action'))
            $query->where('log_name', '%' . $request->query('action') . "%");

        //search for where causer has the same school
        if ($request->has('school')) {
            $query->whereHas('causer.school', function ($q) use ($request) {
                $q->where(
                    'school_name',
                    'like',
                    '%' . $request->query('school') . '%'
                );
            });
        }

        //enable searching stored in results
        if ($search) {
            //search for activity descriptions
            $activityIds =
<<<<<<< Updated upstream
                $query->where('description', 'like', '%' . $search . '%')

                    //search for users
                    ->orwhereHas('causer', function ($userQuery) use ($search) {
                        $userQuery->where(
                            'name',
                            'like',
                            '%' . $search . '%'
                        );
                    });
=======
            $query->where('description', 'like', '%' . $search .'%') 
            
            //search for users
            ->orwhereHas('causer', function ($userQuery) use ($search){
                $userQuery->where('name','like','%' . $search . '%'
                );
            });
>>>>>>> Stashed changes

        }

        $activityLogs = $query->paginate($perPage)->appends($request->query());

        if ($activityLogs->isEmpty()) {
            return response()->json(['message' => 'No activity logs found']);
        }

        return $this->success('Activity logs retrieved successfully', [
            'data' => ActivityLogResource::collection($activityLogs),
            'pagination' => $this->paginateReturn($activityLogs)
        ]);
    }

    public function recent(IndexActivityLogRequest $request)
    {
        $activityLogs = Activity::latest()->take(2)->get();

        if ($activityLogs->isEmpty()) {
            return response()->json(['message' => 'No activity logs found']);
        }

        return $this->success('Activity logs retrieved successfully', [
            'data' => ActivityLogResource::collection($activityLogs),
        ]);
    }

}
