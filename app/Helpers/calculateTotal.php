<?php

namespace App\Helpers;
use App\Models\EnrollmentData;
use App\Models\AcademicYear;

trait calculateTotal
{
    private function calculateTotal(float $male, float $female, $yearID, bool $accurateComp = false): float 
    {

    // Future weighted support
    $academicYear = AcademicYear::query()->where('id', $yearID)->first();

    $totalsQuery = EnrollmentData::whereHas('submission', function ($q) use ($academicYear) {
            $q->where('status', 'approved')
                ->where('academic_year_id', $academicYear->id);
            });

            $totals = $totalsQuery->selectRaw('
            SUM(male_count) as total_male,
            SUM(female_count) as total_female,
            SUM(total_count) as total_students
            ')->first();

    if ($accurateComp) {

        return round(
            (
                ($male * $totals->total_male)
                + ($female * $totals->total_female)
            ) / ($totals->total_male + $totals->total_female),
            1
        );
    }

    // Current simple average
    return round(
        ($male + $female) / 2,1);
    }
}
