<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use App\Http\Resources\AnnouncementResource;
use App\Http\Requests\Announcements\StoreAnnouncementRequest;

class AnnouncementController extends Controller
{
    /**
     * Index Announcements
     * Fetches the 10 latest announcements
     */
    public function index(Request $request)
    {
        $user = $request->user();
        // $public = collect();
        // $dashboard = collect();

        //Query Param
        if($user->hasRole('School Account')){
            $dashboard = Announcement::where('type', 'dashboard')
                ->latest()
                ->take(10)
                ->get();
        } else {
            $public = Announcement::where('type', 'public')
                ->latest()
                ->take(10)
                ->get();

            $dashboard = Announcement::where('type', 'dashboard')
                ->latest()
                ->take(10)
                ->get();
        }

        if($public->isEmpty() && $dashboard->isEmpty()){
            return $this->success("No Announcements Retrieved");
        }

        return $this->success('Users fetched successfully',[
           'public' => AnnouncementResource::collection($public),
           'dashboard' => AnnouncementResource::collection($dashboard)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
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
    public function show(Announcement $announcement)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Announcement $announcement)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Announcement $announcement)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Announcement $announcement)
    {
        //
    }
}
