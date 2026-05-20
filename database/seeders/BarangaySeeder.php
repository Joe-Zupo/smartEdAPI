<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BarangaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $barangays = [
            'Atlu-Bola',
            'Bical',
            'Bundagul',
            'Cacutud',
            'Calumpang',
            'Camachiles',
            'Dapdap',
            'Dau',
            'Dolores',
            'Duquit',
            'Lakandula',
            'Mabiga',
            'Macapagal Village',
            'Mamatitang',
            'Mangalit',
            'Marcos Village',
            'Mawaque',
            'Paralayunan',
            'Poblacion',
            'San Francisco',
            'San Joaquin',
            'Santa Ines',
            'Santa Maria',
            'Santo Rosario',
            'Sapang Balen',
            'Sapang Biabas',
            'Tabun',
        ];

        foreach($barangays as $barangay){
            DB::table('barangays')->insert(['name' => $barangay]);
        }
    }
}
