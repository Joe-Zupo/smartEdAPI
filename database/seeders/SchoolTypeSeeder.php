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
        'Elementary',
        'Integrated School',
        'Junior High School',
        'Junior High School with SHS',
        'Standalone SHS',
        'Science High School',
        'ALS',
    ];

    foreach ($types as $type) {
        DB::table('school_types')->insert(['name' => $type]);
    }
    }
}
