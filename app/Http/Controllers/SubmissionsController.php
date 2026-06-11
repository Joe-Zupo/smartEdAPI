<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use Illuminate\Http\Request;
use App\Http\Resources\SubmissionResource;
use App\Models\AcademicYear;

class SubmissionsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexSubmissionsRequest $request)
    {


        // $submissions = Submission::all();

        // return $this->success('Submissions retrieved successfully', 
        // ['submissions' => SubmissionResource::collection($submissions->load([
        //     'enrollmentData',
        //     'resourceData',
        //     'schoolInformationDraft'
        // ]))]);
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
    public function show(Submissions $submissions)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Submissions $submissions)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Submissions $submissions)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Submissions $submissions)
    {
        //
    }
}
