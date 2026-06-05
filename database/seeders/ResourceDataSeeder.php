<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ResourceData;
use App\Models\Submission;

class ResourceDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $resources = ['Classrooms', 'Teachers', 'Seats', 'Learning Materials'];

        $submissions = Submission::where('type', 'resource')->get();

        foreach ($submissions as $submission) { //per resource submission we're linking a relationship wherein the resource submission has data
            foreach ($resources as $resourceName) {
                $req = rand(20, 150);
                $inv = rand(10, 140);

                ResourceData::create([
                    'submission_id' => $submission->id,
                    'resource_name' => $resourceName,
                    'inventory' => $inv,
                    'requirement' => $req,
                ]);
            }
        }
    }
}
