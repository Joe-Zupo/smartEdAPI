<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\KpiData;
use App\Models\AcademicYear;
use App\Models\EnrollmentData;
use App\Helpers\calculateTotal;
use Illuminate\Support\Facades\DB;

class KpiDataSeeder extends Seeder
{
    use calculateTotal;
    /**
     * Run the database seeds.
     */
    public function run(): void{
        // Get all academic years
        $academicYears = AcademicYear::query()->whereNotIn('status', ['upcoming'])->get();

        // School types enum
        $schoolTypes = ['Elementary','Integrated School','Junior High School','Junior High School with SHS','Standalone SHS','Science High School','ALS'];

        // Loop through each academic year
        foreach ($academicYears as $year) {

            foreach ($schoolTypes as $schoolType) {
                // Loop all 8 KPI rates
                for ($i = 1; $i <= 8; $i++) {
                    $male = rand(90, 100);
                    $female = rand(90, 100);
                    $total = $this->calculateTotal($male, $female, true);

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
