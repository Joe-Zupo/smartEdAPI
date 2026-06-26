<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Announcement;
use Illuminate\Http\Request;
use App\Http\Resources\AnnouncementResource;

class HomeController extends Controller
{
    /**
     * Home Index
     * 
     * Display a listing of the resource.
     */
    public function index()
    {
        $announcements = Announcement::query()->where('type', 'public')
            ->latest()
            ->take(8)
            ->get();

        $schools = School::query()
            ->orderBy('school_name')
            ->take(8)
            ->get(['id', 'school_name', 'image'])
            ->map(function ($school) {
                return [
                    'id' => $school->id,
                    'name' => $school->school_name,
                    'image' => $school->image
                        ? asset('storage/' . $school->image)
                        : null,
                ];
            })
            ->values();

        if ($announcements->isEmpty() && $schools->isEmpty()) {
            return response()->json([
                'message' => 'No records found',
            ]);
        }

        return response()->json([
            'message' => 'Home data retrieved successfully',
            'data' => [
                'announcements' => AnnouncementResource::collection($announcements),
                'schools' => $schools,
            ],
        ]);
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
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
