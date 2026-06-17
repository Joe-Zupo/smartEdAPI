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
            // Loop through each school type

            //total males and females of current year
            $totalsQuery = EnrollmentData::whereHas('submission', function ($q) use ($year) {
            $q->where('status', 'approved')
                ->where('academic_year_id', $year->id);
            });

            $totals = $totalsQuery->selectRaw('
            SUM(male_count) as total_male,
            SUM(female_count) as total_female,
            SUM(total_count) as total_students
            ')->first();

            foreach ($schoolTypes as $schoolType) {
                // Loop all 8 KPI rates
                for ($i = 1; $i <= 8; $i++) {
                    $male = rand(90, 100);
                    $female = rand(90, 100);
                    $total = $this->calculateTotal($male, $female, $totals->total_male, $totals->total_female);

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
