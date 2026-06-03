<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use App\Http\Resources\AnnouncementResource;
use App\Http\Requests\Announcements\StoreAnnouncementRequest;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validated = $request->validated();

        $perPage = $validated['per_page'] ?? 5;
        $sortBy = $validated['sortBy'] ?? 'id';
        $sortOrder = $validated['sortOrder'] ?? 'asc';

        $query = Announcement::query();

        //Query Params

        $query->orderBy($sortBy, $sortOrder);
        $paginatedAnnouncements = $query
            ->paginate($perPage);

        if(!$paginatedAnnouncements->count()){
            return $this->success('No more users available');
        }

        $paginaton = $this->paginateReturn($paginatedAnnouncements);

        return $this->success('Users fetched successfully',[
           'announcements' => AnnouncementResource::collection($paginatedAnnouncements),
           'pagination' => $paginaton 
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
