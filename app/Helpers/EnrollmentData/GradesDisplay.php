<?php

namespace App\Helpers\EnrollmentData;
use App\Models\School;

trait GradesDisplay
{
    private function displayRelevant(School $school, $items){
        
        $type = $school->schoolType->name;
        $allowedGrades = match ($type) {  
                'Elementary' => [
                    'Kinder','Grade 1','Grade 2','Grade 3','Grade 4','Grade 5','Grade 6',
                ],

                'Junior High School' => [
                    'Grade 7','Grade 8','Grade 9','Grade 10',
                ],

                'Standalone SHS' => [
                    'Grade 11','Grade 12',
                ],

                'Integrated School',
                'Science High School',
                'ALS',
                'Junior High School with SHS' => [
                    'Kinder','Grade 1','Grade 2','Grade 3','Grade 4','Grade 5','Grade 6','Grade 7','Grade 8','Grade 9','Grade 10','Grade 11','Grade 12',
                ],

                default => [],
            };

        return $items->filter(function ($item) use ($allowedGrades) {

            return in_array(
                $item->grade_level,
                $allowedGrades
            );

        })->values();
    }
}
