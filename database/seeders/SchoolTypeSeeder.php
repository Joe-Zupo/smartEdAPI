<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SchoolTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
        'elementary',
        'integrated_school',
        'junior_high_school',
        'junior_high_school_with_shs',
        'standalone_shs',
        'science_high_school',
        'als',
    ];

    foreach ($types as $type) {
        DB::table('school_types')->insert(['name' => $type]);
    }
    }
}
