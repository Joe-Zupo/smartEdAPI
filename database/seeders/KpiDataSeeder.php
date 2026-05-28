<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\KpiData;
use App\Models\AcademicYear;

class KpiDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void{
        // Get all academic years
        $academicYears = AcademicYear::all();

        // School types enum
        $schoolTypes = ['elementary', 'secondary'];

        // Loop through each academic year
        foreach ($academicYears as $year) {
            // Loop through each school type
            foreach ($schoolTypes as $schoolType) {
                // Loop all 8 KPI rates
                for ($i = 1; $i <= 8; $i++) {
                    $male = rand(1, 100);
                    $female = rand(1, 100);
                    $total = ($male + $female) / 2;

                    KpiData::create([
                        'kpi_id' => $i,
                        'academic_year_id' => $year->id,
                        'male' => $male,
                        'female' => $female,
                        'total' => $total,
                        'school_type' => $schoolType,
                    ]);
                }
            }
        }
    }
}
