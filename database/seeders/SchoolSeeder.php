<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use App\Helpers\SeederFileTrait;

class SchoolSeeder extends Seeder
{
    use SeederFileTrait, WithoutModelEvents;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //$this->cleanupSeederFiles('school_images');
        $schools = [

            // =======================
            // 1. ELEMENTARY SCHOOLS
            // =======================
            [
                'school_name'       => 'Atlu Bola Elementary School',
                'school_code'       => '03120101',                  // sampledata
                'year_established'  => 1980,                        // sampledata
                'school_type_id'    => 1,                           // elementary
                'district'          => 'East',                      // sampledata
                'latitude'          => 15.2088,                     // sampledata
                'longitude'         => 120.5912,                    // sampledata
                'address'           => 'Purok 2, Atlu Bola, Mabalacat, Pampanga, Central Luzon',
                'image'       => $this->copySeederFile('schools', 'atlu_bola.png', 'school_images'),
            ],
            [
                'school_name'       => 'Calumpang Elementary School',
                'school_code'       => '03120102',                  // sampledata
                'year_established'  => 1985,                        // sampledata
                'school_type_id'    => 1,
                'district'          => 'East',                // sampledata
                'latitude'          => 15.2145,                     // sampledata
                'longitude'         => 120.5865,                    // sampledata
                'address'           => 'Sitio Riverside, Calumpang, Mabalacat, Pampanga, Central Luzon',
                'image'       => $this->copySeederFile('schools', 'calumpang.png', 'school_images'),

            ],

            // =======================
            // 2. INTEGRATED SCHOOLS
            // =======================
            [
                'school_name'       => 'Monicayo Integrated School',
                'school_code'       => '502212',                  // sampledata
                'year_established'  => 2006,                        // sampledata
                'school_type_id'    => 2,  // integrated
                'district'          => 'West',                     // sampledata
                'latitude'          => 15.2312,                     // sampledata
                'longitude'         => 120.6145,                    // sampledata
                'address'           => 'Barangay Calumpang, Sitio Monicayo, Mabalacat, 2010 Pampanga',
                'image'       => $this->copySeederFile('schools', 'monicayo.png', 'school_images'),
            ],
            [
                'school_name'       => 'Sta. Ines Integrated School',
                'school_code'       => '501981',
                'year_established'  => 1965,
                'school_type_id'    => 2,
                'district'          => 'West',
                'latitude'          => 15.2607,
                'longitude'         => 120.6105,
                'address'           => 'Purok 3, Barangay Sta. Ines, Mabalacat City, Pampanga, Central Luzon',
                'image'       => $this->copySeederFile('schools', 'sta_ines.png', 'school_images'),
            ],

            // =======================
            // 3. JUNIOR HIGH SCHOOLS
            // =======================
            [
                'school_name'       => 'Camachiles National High School',
                'school_code'       => '03120301',                  // sampledata
                'year_established'  => 1998,                        // sampledata
                'school_type_id'    => 3,         // junior high
                'district'          => 'South',                     // sampledata
                'latitude'          => 15.2267,                     // sampledata
                'longitude'         => 120.6012,                    // sampledata
                'address'           => 'Camachiles St., Camachiles, Mabalacat, Pampanga, Central Luzon',
                'image'       => $this->copySeederFile('schools', 'camachiles.png', 'school_images'),
            ],
            [
                'school_name'       => 'Mabalacat Community High School',
                'school_code'       => '03120302',                  // sampledata
                'year_established'  => 2003,                        // sampledata
                'school_type_id'    => 3,
                'district'          => 'South',                      // sampledata
                'latitude'          => 15.2089,                     // sampledata
                'longitude'         => 120.6234,                    // sampledata
                'address'           => 'Community Drive, Santo Nino, Mabalacat, Pampanga, Central Luzon',
                'image'       => $this->copySeederFile('schools', 'mabalacat_community.png', 'school_images'),
            ],

            // =======================
            // 4. SENIOR HIGH SCHOOLS
            // =======================
            [
                'school_name'       => 'Sapang Biabas Resettlement SHS',
                'school_code'       => '03120501',                  // sampledata
                'year_established'  => 2016,                        // sampledata
                'school_type_id'    => 5,   // standalone senior high
                'district'          => 'North',                     // sampledata
                'latitude'          => 15.2401,                     // sampledata
                'longitude'         => 120.6089,                    // sampledata
                'address'           => 'Resettlement Area, Sapang Biabas, Mabalacat, Pampanga, Central Luzon',
            ],

            // =======================
            // 5. SCIENCE HIGH SCHOOL
            // =======================
            [
                'school_name'       => 'Philippine Science High School Central Luzon Campus',
                'school_code'       => '03120601',                  // sampledata
                'year_established'  => 2010,                        // sampledata
                'school_type_id'    => 6,
                'district'          => 'West',                      // sampledata
                'latitude'          => 15.2156,                     // sampledata
                'longitude'         => 120.6345,                    // sampledata
                'address'           => 'Lahar Zone, San Fernando, Mabalacat, Pampanga, Central Luzon',
            ],
        ];

        $principalLevels = ["Principal I","Principal II","Principal III","Principal IV"];

        foreach ($schools as $school) {
            $school['position'] = Arr::random($principalLevels);
            $school['region'] = "Region III";
            School::create($school);
        }
    }
}
