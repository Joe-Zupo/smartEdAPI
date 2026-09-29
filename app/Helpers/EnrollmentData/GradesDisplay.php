<?php

namespace App\Helpers\EnrollmentData;
use App\Models\School;
use App\Support\GradeOfferings;

trait GradesDisplay
{
    private function displayRelevant(School $school, $items){

        $type = $school->schoolType->name;
        $allowedGrades = GradeOfferings::forType($type);

        return $items->filter(function ($item) use ($allowedGrades) {

            return in_array(
                $item->grade_level,
                $allowedGrades
            );

        })->values();
    }
}
