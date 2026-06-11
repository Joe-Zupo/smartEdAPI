<?php

namespace App\Helpers;

trait calculateTotal
{
    private function calculateTotal(float $male, float $female, ?int $malePopulation = null, ?int $femalePopulation = null): float 
    {

    // Future weighted support
    if ($malePopulation && $femalePopulation) {

        return round(
            (
                ($male * $malePopulation)
                + ($female * $femalePopulation)
            ) / ($malePopulation + $femalePopulation),
            1
        );
    }

    // Current simple average
    return round(
        ($male + $female) / 2,1);
    }
}
