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
     * Display a listing of the resource.
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
            'created_at']);
        
        //search for where causer did a certain action in: Logged In,Logged Out,Submitted Data,Returned Data,Approved Data
        if($request->has('action'))
            $query->where('action', '%' . $request->query('log_name') . "%");

        //search for where causer has the same school
        if($request->has('school')){
            $query->where('name', 'like', '%' . $request->query('school') . '%');
        }

        //enable searching stored in results
        if($search){
            //search for activity descriptions
            $results =
            $query->where('description', 'like', '%' . $search .'%') 
            
            //search for users
            ->orwhereHas('name', 'like', '%'. $search . '%');

            $activityIds = collect(); //Empty basket or collection

            foreach ($results as $result) { //sifts through results

                $model = $result->searchable; //gets the model of the result

                if ($model instanceof Activity) {
                    $activityIds->push($model->id); //appends ids to the collection
                }

                if ($model instanceof User) {
                    $activityIds = $activityIds->merge( //collected all IDs from search and gets the activity model
                        Activity::where('causer_id', $model->id)->pluck('id')
                    );
                }
            }

            $query->whereIn('id', $activityIds->unique()); 
            //sorts the query to match the collection of IDs we gathered from result

        }

        $activityLogs = $query->paginate($perPage)->appends($request->query());

        if ($activityLogs->isEmpty()) {
            return response()->json(['message' => 'No activity logs found']);
        }

        return $this->success('Activity logs retrieved successfully',[
            'data' => ActivityLogResource::collection($activityLogs),
            'pagination' => $this->paginateReturn($activityLogs)
        ]);
    }

}
