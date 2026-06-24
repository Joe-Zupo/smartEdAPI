<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\Submission;
use App\Models\School;
use App\Models\EnrollmentData;

class EndrollmentDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $academicYears = AcademicYear::all();
        $schools = School::with('schoolType')->get();

        foreach ($academicYears as $academicYear) {

            // Skip current and future years
            if (in_array($academicYear->status, ['default', 'upcoming'])) {
                continue;
            }

            foreach ($schools as $school) {

                $allowedGrades = match ($school->schoolType->name) {

                    'Elementary' => [
                        'Kinder',
                        'Grade 1',
                        'Grade 2',
                        'Grade 3',
                        'Grade 4',
                        'Grade 5',
                        'Grade 6',
                    ],

                    'Junior High School' => [
                        'Grade 7',
                        'Grade 8',
                        'Grade 9',
                        'Grade 10',
                    ],

                    'Standalone SHS' => [
                        'Grade 11',
                        'Grade 12',
                    ],

                    'Integrated School',
                    'Science High School',
                    'ALS',
                    'Junior High School with SHS' => [
                        'Kinder',
                        'Grade 1',
                        'Grade 2',
                        'Grade 3',
                        'Grade 4',
                        'Grade 5',
                        'Grade 6',
                        'Grade 7',
                        'Grade 8',
                        'Grade 9',
                        'Grade 10',
                        'Grade 11',
                        'Grade 12',
                    ],

                    default => [],
                };

                foreach ($allowedGrades as $grade) {

                    EnrollmentData::query()
                        ->where('academic_year_id', $academicYear->id)
                        ->where('school_id', $school->id)
                        ->where('grade_level', $grade)
                        ->update([
                            'male_count' => rand(15, 60),
                            'female_count' => rand(15, 60),
                        ]);
                }
            }
        }
    } 
}
