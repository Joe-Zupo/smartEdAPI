<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\School;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $users = [
            [
                'school_id' => School::where('school_name', 'Atlu Bola Elementary School')->value('id'),
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'phone_number' => fake()->phoneNumber(),
                'username' => 'atlubolaES',
                'role' => 'School Account'
            ],
            [
                'school_id' => School::where('school_name', 'Calumpang Elementary School')->value('id'),
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'phone_number' => fake()->phoneNumber(),
                'username' => 'calumpangES',
                'role' => 'School Account'
            ],
            [
                'school_id' => School::where('school_name', 'Monicayo Integrated School')->value('id'),
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'phone_number' => fake()->phoneNumber(),
                'username' => 'monicayoIS',
                'role' => 'School Account'
            ],
            [
                'school_id' => 4,
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'phone_number' => fake()->phoneNumber(),
                'username' => 'inesIS',
                'role' => 'School Account'
            ],
            [
                'school_id' => 5,
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'phone_number' => fake()->phoneNumber(),
                'username' => 'camachilesHS',
                'role' => 'School Account'
            ],
            [
                'school_id' => 6,
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'phone_number' => fake()->phoneNumber(),
                'username' => 'mabalacatHS',
                'role' => 'School Account'
            ],
            [
                'school_id' => 7,
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'phone_number' => fake()->phoneNumber(),
                'username' => 'sapangSHS',
                'role' => 'School Account'
            ],
            [
                'school_id' => 8,
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'phone_number' => fake()->phoneNumber(),
                'username' => 'phisciHS',
                'role' => 'School Account'
            ],
            [
                'school_id' => null, // no school ID school Acc
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'phone_number' => fake()->phoneNumber(),
                'username' => 'usecaseOne',
                'role' => 'School Account'
            ],
            [
                'school_id' => 1, 
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'phone_number' => fake()->phoneNumber(),
                'username' => 'usecaseTwo',
                'role' => null // no role
            ],
            [
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'phone_number' => fake()->phoneNumber(),
                'username' => 'super_intendent',
                'role' => 'Division Admin'
            ],
            [
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'phone_number' => fake()->phoneNumber(),
                'username' => 'it_officer',
                'role' => 'System Admin'
            ],
            [
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'phone_number' => fake()->phoneNumber(),
                'username' => 'developers',
                'role' => 'System Admin'
            ],
            [
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'phone_number' => fake()->phoneNumber(),
                'username' => 'usecaseThree',
                'role' => 'System Admin',
                'is_active' => false //is not active
            ],
        ];
        //Usernames:
            //atlubolaES
            //calumpangES
            //monicayoIS
            //inesIS
            //camachilesHS
            //mabalacatHS
            //sapangSHS
            //phisciHS
            //usecaseOne, usecaseTwo, usecaseThree
            //super_intendent
            //it_officer
            //developers
        foreach ($users as $userData) {
            if (
                ($userData['role'] ?? null) === 'School Account'
                && !empty($userData['school_id'])
            ) {

                $schoolId = $userData['school_id'];

                $schoolPositionCount[$schoolId] =
                    ($schoolPositionCount[$schoolId] ?? 0) + 1;

                $userData['position'] =
                    $schoolPositionCount[$schoolId] === 1
                        ? 'Principal IV'
                        : 'Principal III';

                $userData['is_head'] = true;
            } else {

                $userData['position'] = null;
                $userData['is_head'] = false;
            }
            
            $userData['password'] = Hash::make($userData['username']);

            $roleName = $userData['role'];
            unset($userData['role']);

            $user = User::create($userData);

            $user->assignRole($roleName);
            $school = School::query()->where('id', $user->school_id)->first();
            
            if ($school) {
                $school->update([
                    'head_email'   => $user->email,
                    'phone_number' => $user->phone_number,
                ]);
            }
        }
    }
}
