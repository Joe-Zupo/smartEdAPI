<?php

namespace App\Http\Controllers;

use App\Models\Notifications;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    /**
     * Index Notifications
     * 
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Notifications::class);
        $user = $request->user();
        
        $request->validate([
            'action_required' => 'in:true,false',
            'read' => 'in:true,false',
            'page' => ['integer']
        ]);

        $read = $request->boolean('read', false);
        $perPage = $request->get('per_page', 10);

        $query = Notifications::with([
            'submission.school',
            'submission.academicYear',
            'submission.user',
            'readers' => function ($q) use ($user) {
                $q->where('user_id', $user->id);
            }
        ])->latest();

        if ($user->hasRole(['System Admin', 'Division Admin'])) {
            $query->where(function ($q) {
                $q->where('title', 'like', 'New Submission from%');
            });
        } elseif ($user->hasRole('School Account')) {
            if ($user->school_id) {
                $query->whereHas('submission', function ($q) use ($user) {
                    $q->where('school_id', $user->school_id);
                });
            }
            $query->whereIn('title', ['Submission Returned', 'Approved Submission', 'Pending Review']);
        }

        $baseQuery = $query->clone();

        if ($request->input('action_required') === 'true') {
            $query->where('title', 'Submission Returned')
                  ->whereHas('submission', function ($q) {
                      $q->where('status', 'returned');
                  });
        }
        if (isset($read)){
            $query->where('is_read', $read);
        }

        $unreadCount = $baseQuery->clone()->whereDoesntHave('readers', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->count();

        $readCount = $baseQuery->clone()->whereHas('readers', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->count();

        // Only compute action-required count for School Account users
        $actionRequiredCount = 0;
        if ($user->hasRole('School Account')) {
            $actionRequiredCount = $baseQuery->clone()
                ->where('title', 'Submission Returned')
                ->whereHas('submission', function ($q) {
                    $q->where('status', 'returned');
                })
                ->distinct('submission_id')
                ->count('submission_id');
        }

        $notifications = $query->paginate($perPage);

        if(!$read){
            $count = [
            'total' => $baseQuery->clone()->count(),
            'unread' => $unreadCount,
            ];
        }else{
            $count = [
                'total' => $baseQuery->clone()->count(),
                'read' => $readCount,
            ];
        }

        // Only show action_required count for School Account users
        if ($user->hasRole('School Account')) {
            $count['action_required'] = $actionRequiredCount;
        }

        if ($notifications->isEmpty()) {
            return $this->success('No notifications found', ['count' => $count]);
        }

        return $this->success('Notifications retrieved successfully', [
            'count' => $count,
            'data' => NotificationResource::collection($notifications),
            'pagination' => $this->paginateReturn($notifications)
            ]);        
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Notifications $notifications)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Notifications $notifications)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Notifications $notifications)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Notifications $notifications)
    {
        //
    }
    /**
     * Mark As Read
     * 
     * Mark notification as read.
     */
    public function markAsRead(Request $request, Notifications $notification)
    {
        $this->authorize('customFunc', Notifications::class);
        $user = $request->user();

        $alreadyRead = $notification->readers()
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyRead) {
            return $this->error('Notification already marked as read', 403);
        }

        $notification->readers()->attach($user->id);

        return $this->success('Notification marked as read');
    }
}
