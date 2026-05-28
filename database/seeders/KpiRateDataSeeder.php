<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\KpiRateData;

class KpiRateDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kpis = [
            'Gross Enrollment Rate',
            'Net Enrollment Rate',
            'Transition Rate',
            'Retention Rate',
            'Completion Rate',
            'Promotion Rate',
            'Repetition Rate',
            'School Leaver Rate',
        ];

        foreach ($kpis as $kpi) {
            KpiRateData::create([
                'name' => $kpi,
            ]);
        }
    }
}
