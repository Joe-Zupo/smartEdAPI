<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\School;
use App\Models\Submission;
use App\Models\AcademicYear;
use App\Models\SchoolInformationDraft;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;


class SubmissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schools = School::all();

        // Get all relevant years
        $years = AcademicYear::whereIn('status', ['default', 'active', 'archived'])->get();

        // Define types and statuses
        $types = ['enrollment', 'resource'];
        $statuses = ['approved'];

        foreach ($schools as $school) {
            // Get school user
            $schoolUser = User::where('school_id', $school->id)
                ->role('School Account')
                ->first();
            $userId = $schoolUser ? $schoolUser->id : User::first()->id;

            foreach ($years as $year) {
                foreach ($types as $type) {
                    foreach ($statuses as $status) {
                        Submission::create([
                            'academic_year_id' => $year->id,
                            'school_id'        => $school->id,
                            'user_id'          => $userId,
                            'type'             => $type,
                            'status'           => $status,
                        ]);
                    }
                }
            }
        }
        $image = UploadedFile::fake()->image('school.jpg');
        $path = $image->store('school_images', 'public');

        $submission = Submission::create([
            'academic_year_id' => AcademicYear::where('status', 'default')->first()->id,
            'school_id'        => School::first()->id,
            'user_id'          => User::first()->id,
            'type'             => 'information',
            'status'           => 'approved',
        ]);

        SchoolInformationDraft::create([
            'submission_id' => $submission->id,
            'school_id'     => $submission->school_id,
            'name'   => 'Draft School Name',
            'code'   => '00000000',
            'address'       => 'Draft Address',
            'year_established' => 2000,
            'school_type_id'   => 1,
            'district'        => 'District 1',
            'latitude'       => 10.0000,
            'longitude'      => 120.0000,
            'image' => $path,
        ]);

        $submission2 = Submission::create([
            'academic_year_id' => AcademicYear::where('status', 'default')->first()->id,
            'school_id'        => School::skip(3)->first()->id,
            'user_id'          => 2,
            'type'             => 'information',
            'status'           => 'returned',
        ]);

        SchoolInformationDraft::create([
            'submission_id' => $submission2->id,
            'school_id'     => $submission2->school_id,
            'name'   => '2nd Draft School Name',
            'code'   => '00000001',
            'address'       => 'Draft Address',
            'year_established' => 2001,
            'school_type_id'   => 2,
            'district'        => 'West',
            'latitude'       => 20.0000,
            'longitude'      => 130.0000,
            'image' => $path,
        ]);
    }
}
