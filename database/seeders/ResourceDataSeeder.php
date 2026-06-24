<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ResourceData;
use App\Models\Submission;
use App\Models\AcademicYear;
use App\Models\School;

class ResourceDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $resources = [
            'Classrooms',
            'Teachers',
            'Seats',
            'Learning Materials'
        ];

        foreach (AcademicYear::all() as $academicYear) {

            if (in_array($academicYear->status, ['default', 'upcoming'])) {
                continue;
            }

            foreach (School::all() as $school) {

                foreach ($resources as $resourceName) {

                    ResourceData::query()
                        ->where('academic_year_id', $academicYear->id)
                        ->where('school_id', $school->id)
                        ->where('resource_name', $resourceName)
                        ->update([
                            'inventory' => rand(10, 140),
                            'requirement' => rand(20, 150),
                        ]);
                }
            }
        }
    }
}
