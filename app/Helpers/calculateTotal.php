<?php

namespace App\Helpers;
use App\Models\EnrollmentData;
trait calculateTotal
{
    private function calculateTotal(float $male, float $female, bool $accurateComp, $yearID): float 
    {

    // Future weighted support

    $totalsQuery = EnrollmentData::query()->where('academic_year_id', $yearID);

            $totals = $totalsQuery->selectRaw('
            SUM(male_count) as total_male,
            SUM(female_count) as total_female,
            SUM(total_count) as total_students
            ')->first();

    if ($accurateComp) {

        if ($totals->total_students == 0){
            return 0;
        }else{
                return round(
                (
                    ($male * $totals->total_male)
                    + ($female * $totals->total_female)
                ) / ($totals->total_male + $totals->total_female),
                1
            ); 
        }
    }

    // Current simple average
    return round(
        ($male + $female) / 2,1);
    }
}
