<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use App\Http\Requests\ActivityLogs\IndexActivityLogRequest;
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

        $query = Activity::query()
        ->;
    }

}
