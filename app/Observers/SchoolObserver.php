<?php

namespace App\Observers;

use App\Models\School;
use App\Models\SchoolType;
use App\Models\EnrollmentData;
use App\Support\GradeOfferings;
use App\Support\SchoolYearRows;

class SchoolObserver
{
    public function created(School $school): void
    {
        foreach (SchoolYearRows::openYears() as $year) {
            SchoolYearRows::ensure($school, $year);
        }
    }

   public function updated(School $school): void
    {
        if (!$school->wasChanged('school_type_id')) {
            return;
        }

        $oldGrades = GradeOfferings::forType(SchoolType::find($school->getOriginal('school_type_id'))?->name);
        $newGrades = GradeOfferings::forType(SchoolType::find($school->school_type_id)?->name);
        $gradesToRemove = array_diff($oldGrades, $newGrades);

        $openYears = SchoolYearRows::openYears();

        foreach ($openYears as $year) {
            SchoolYearRows::ensure($school, $year); // adds new grades, skips existing
        }

        if ($gradesToRemove) {
            EnrollmentData::query()
                ->where('school_id', $school->id)
                ->whereIn('academic_year_id', $openYears->pluck('id'))
                ->whereIn('grade_level', $gradesToRemove)
                ->delete();
        }
    }
}
