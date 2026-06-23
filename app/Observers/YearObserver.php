<?php

namespace App\Observers;

use App\Models\AcademicYear;
use App\Models\EnrollmentData;
use App\Models\School;
use App\Models\ResourceData;

class YearObserver
{
    public function created(AcademicYear $academicYear): void
    {
        $schools = School::with('schoolType')->get();

        foreach ($schools as $school) {

            $allowedGrades = $this->getAllowedGrades($school->schoolType?->name ?? '');

            // Enrollment Data
            foreach ($allowedGrades as $grade) {

                EnrollmentData::firstOrCreate([
                    'academic_year_id' => $academicYear->id,
                    'school_id' => $school->id,
                    'grade_level' => $grade,
                ], [
                    'male_count' => 0,
                    'female_count' => 0,
                ]);
            }

            // Resource Data
            foreach (
                [
                    'Classrooms',
                    'Teachers',
                    'Seats',
                    'Learning Materials'
                ] as $resource
            ) {

                ResourceData::firstOrCreate([
                    'academic_year_id' => $academicYear->id,
                    'school_id' => $school->id,
                    'resource_name' => $resource,
                ], [
                    'inventory' => 0,
                    'requirement' => 0,
                ]);
            }
        }
    }

    private function getAllowedGrades(string $type): array
    {
        return match ($type) {

            'Elementary' => [
                'Kinder','Grade 1','Grade 2',
                'Grade 3','Grade 4',
                'Grade 5','Grade 6',
            ],

            'Junior High School' => [
                'Grade 7','Grade 8',
                'Grade 9','Grade 10',
            ],

            'Standalone SHS' => [
                'Grade 11','Grade 12',
            ],

            'Integrated School',
            'Science High School',
            'ALS',
            'Junior High School with SHS' => [
                'Kinder','Grade 1','Grade 2',
                'Grade 3','Grade 4',
                'Grade 5','Grade 6',
                'Grade 7','Grade 8',
                'Grade 9','Grade 10',
                'Grade 11','Grade 12',
            ],

            default => [],
        };
    }
}
