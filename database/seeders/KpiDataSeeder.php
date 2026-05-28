<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\KpiData;

class KpiDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $academicYearId = 1;
        $schoolType = 'elementary';  // enum
        // Loop all 8 KPI rates
        for ($i = 1; $i <= 8; $i++) {
            $male = rand(1, 100);
            $female = rand(1, 100);
            $total = $male + $female;
            $kpiData = [
                'kpi_id' => $i,  // all 8 KPI rates
                'academic_year_id' => $academicYearId,
                'male' => $male,
                'female' => $female,
                'total' => $total,
                'school_type' => $schoolType];
                
            KpiData::create($kpiData);
        }
    }
}
