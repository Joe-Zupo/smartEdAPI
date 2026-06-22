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
        $resources = ['Classrooms', 'Teachers', 'Seats', 'Learning Materials'];

        $academicYears = AcademicYear::all();
        $schools = School::all();

        foreach($academicYears as $academicYear){
            foreach($schools as $school){
                foreach ($resources as $resourceName) {
                    if($academicYear->status === 'default' || $academicYear->status === 'upcoming'){
                        $req = 0;
                        $inv = 0;
                    }else{
                        $req = rand(20, 150);
                        $inv = rand(10, 140);
                    }
                    ResourceData::create([
                        'academic_year_id' => $academicYear->id,
                        'school_id' => $school->id,
                        'resource_name' => $resourceName,
                        'inventory' => $inv,
                        'requirement' => $req,
                    ]);
                }
            }
        }
    }
}
