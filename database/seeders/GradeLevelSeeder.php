<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\GradeLevel;

class GradeLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $levels = ['Kinder'];

        for ($i = 1; $i <= 12; $i++) {
            $levels[] = "Grade $i";
        }

        foreach ($levels as $level) {
            GradeLevel::firstOrCreate(['name' => $level]);
        }
    }
}
