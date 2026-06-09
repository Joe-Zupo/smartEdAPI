<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\GradeLevel;
use App\Models\Submission;
use App\Models\EnrollmentData;

class EndrollmentDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $grades = GradeLevel::all();

        $submissions = Submission::where('type', 'enrollment')->get();

        foreach ($submissions as $submission) {
            foreach ($grades as $grade) {

                $male = rand(15, 60);
                $female = rand(15, 60);

                EnrollmentData::create([
                    'submission_id' => $submission->id,
                    'grade_level' => $grade->name,
                    'male_count' => $male,
                    'female_count' => $female,
                ]);
            }
        }
    }
}
