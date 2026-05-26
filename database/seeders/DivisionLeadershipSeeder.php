<?php

namespace Database\Seeders;

use App\Models\DivisionLeadership;
use Illuminate\Database\Seeder;

class DivisionLeadershipSeeder extends Seeder
{

    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $leaderships = [
            // Current Leadership
            [
                'name' => 'Maria Carmen P. Cuenco EdD, CESO V',
                'position' => 'Schools Division Superintendent',
                //'image_path' => $this->copySeederFile('division', 'sample_profile1.jpg', 'division-leadership'),
                'is_oic' => false,
                'term_start' => 2023,
                'term_end' => null,
            ],
            [
                'name' => 'Roberto S. Villamor EdD, CESE',
                'position' => 'Assistant Schools Division Superintendent',
                //'image_path' => $this->copySeederFile('division', 'sample_profile2.jpg', 'division-leadership'),
                'is_oic' => false,
                'term_start' => 2022,
                'term_end' => null,
            ],
            
            // Previous Superintendents
            [
                'name' => 'Gloria R. Mercado EdD, CESO V',
                'position' => 'Schools Division Superintendent',
                //'image_path' => $this->copySeederFile('division', 'sample_profile3.jpg', 'division-leadership'),
                'is_oic' => false,
                'term_start' => 2019,
                'term_end' => 2023,
            ],
            [
                'name' => 'Fernando L. Santos EdD, CESO V',
                'position' => 'Schools Division Superintendent',
                //'image_path' => $this->copySeederFile('division', 'sample_profile4.jpg', 'division-leadership'),
                'is_oic' => false,
                'term_start' => 2015,
                'term_end' => 2019,
            ],
            [
                'name' => 'Angelita B. Cruz EdD, CESO V',
                'position' => 'Schools Division Superintendent',
                //'image_path' => $this->copySeederFile('division', 'sample_profile5.jpg', 'division-leadership'),
                'is_oic' => true,
                'term_start' => 2014,
                'term_end' => 2015,
            ],
            [
                'name' => 'Ricardo M. Villanueva EdD, CESO IV',
                'position' => 'Schools Division Superintendent',
                //'image_path' => $this->copySeederFile('division', 'sample_profile6.jpg', 'division-leadership'),
                'is_oic' => false,
                'term_start' => 2010,
                'term_end' => 2014,
            ],
            [
                'name' => 'Teresita P. Reyes EdD, CESO V',
                'position' => 'Schools Division Superintendent',
                //'image_path' => $this->copySeederFile('division', 'sample_profile7.jpg', 'division-leadership'),
                'is_oic' => false,
                'term_start' => 2006,
                'term_end' => 2010,
            ],
            
            // Previous Assistant Superintendents
            [
                'name' => 'Jose Antonio M. Bautista EdD, CESE',
                'position' => 'Assistant Schools Division Superintendent',
                //'image_path' => $this->copySeederFile('division', 'sample_profile8.jpg', 'division-leadership'),
                'is_oic' => false,
                'term_start' => 2018,
                'term_end' => 2022,
            ],
            [
                'name' => 'Leonora S. Gonzales PhD, CESE',
               'position' => 'Assistant Schools Division Superintendent',
                //'image_path' => $this->copySeederFile('division', 'sample_profile9.jpg', 'division-leadership'),
                'is_oic' => false,
                'term_start' => 2014,
                'term_end' => 2018,
            ],
            [
                'name' => 'Patricia Anne L. Torres EdD',
                'position' => 'Assistant Schools Division Superintendent',
                //'image_path' => $this->copySeederFile('division', 'sample_profile10.jpg', 'division-leadership'),
                'is_oic' => true,
                'term_start' => 2013,
                'term_end' => 2014,
            ],
            [
                'name' => 'Benjamin R. Fernandez EdD, CESE',
                'position' => 'Assistant Schools Division Superintendent',
                //'image_path' => $this->copySeederFile('division', 'sample_profile11.jpg', 'division-leadership'),
                'is_oic' => false,
                'term_start' => 2009,
                'term_end' => 2013,
            ],
            [
                'name' => 'Rosalinda V. Aquino PhD',
                'position' => 'Assistant Schools Division Superintendent',
                //'image_path' => $this->copySeederFile('division', 'sample_profile12.jpg', 'division-leadership'),
                'is_oic' => false,
                'term_start' => 2005,
                'term_end' => 2009,
            ],
            [
                'name' => 'Emmanuel P. Cruz EdD, CESE',
                'position' => 'Assistant Schools Division Superintendent',
               // 'image_path' => $this->copySeederFile('division', 'sample_profile13.jpg', 'division-leadership'),
                'is_oic' => false,
                'term_start' => 2001,
                'term_end' => 2005,
            ],
            [
                'name' => 'Ana Marie D. Santiago PhD',
                'position' => 'Assistant Schools Division Superintendent',
                //'image_path' => $this->copySeederFile('division', 'sample_profile14.jpg', 'division-leadership'),
                'is_oic' => true,
                'term_start' => 2000,
                'term_end' => 2001,
            ],
            [
                'name' => 'Mariano G. Ramos EdD',
                'position' => 'Assistant Schools Division Superintendent',
                //'image_path' => $this->copySeederFile('division', 'sample_profile15.jpg', 'division-leadership'),
                'is_oic' => false,
                'term_start' => 1996,
                'term_end' => 2000,
            ],
        ];

        foreach ($leaderships as $leadership) {
            DivisionLeadership::create($leadership);
        }
    }
}
