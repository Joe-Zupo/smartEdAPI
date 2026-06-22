<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AcademicYear;

class AcademicYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $academic_years = [
            [
                'start_date' => '2022-10-01',
                'end_date' => '2023-06-30',
                'status' => 'active',
            ],
            [
                'start_date' => '2023-10-01',
                'end_date' => '2024-06-30',
                'status' => 'active',
            ],
            [
                'start_date' => '2024-10-01',
                'end_date' => '2025-05-30',
                'status' => 'active',
            ],
            [
                'start_date' => '2025-06-01',
                'end_date' => '2026-05-30',
                'status' => 'active',
            ],
            [
                'start_date' => '2026-06-01',
                'end_date' => '2027-06-30',
                'status' => 'default',
            ],
            [
                'start_date' => '2027-10-01',
                'end_date' => '2028-06-30',
                'status' => 'upcoming',
            ],
        ];

        foreach($academic_years as $academic_year){
            AcademicYear::create($academic_year);
        }
    }
}
