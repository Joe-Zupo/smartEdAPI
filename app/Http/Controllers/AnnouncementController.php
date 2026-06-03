<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AcademicYear;
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
     * Create Announcement
     * Fix file Upload Otherwise functional
     */
    public function store(StoreAnnouncementRequest $request)
    {
        $validated = $request->validated();

        $year = AcademicYear::where('status', 'default')->first();

        if ($year) {
            $cleanName = str_replace(['S.Y. ', 'S.Y.', ' '], '', $year->name);
            $folderName = "announcements/{$cleanName}";
        } else {
            $folderName = "announcements/general";
        }

        $validated['image'] = $request->file('image')->store($folderName, 'public');



        $announcement = Announcement::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'image_url' => $validated['image'],
        ]);

        return $this->success("Announcement created successfully", ["data" => $announcement]);
    }
    
    /**
    * Public Index Announcements
    * 
    * Display a listing of the resource for public.
    */
    public function publicIndex()
    {
        $announcements = Announcement::where('type', 'public')
            ->latest()
            ->get();
        if ($announcements->isEmpty()) {
            return $this->success('No Announcements fetched');
        }

        return $this->success('Announcements retrieved successfully', ['data' => AnnouncementResource::collection($announcements)]);
    }

    /**
     * Public Show Announcement
     *  
     */
    public function publicShow(Announcement $announcement)
    {
        if ($announcement['type'] !== ['public']){
            return $this->error("Announcement not found", 404);
        }

        return $this->success("Announcement retrieved Successfully", ['data' => new AnnouncementResource($announcement)]);
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
