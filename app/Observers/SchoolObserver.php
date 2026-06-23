<?php

namespace App\Observers;

use App\Models\AcademicYear;
use App\Models\School;
use App\Models\SchoolType;
use App\Models\GradeLevel;
use App\Models\ResourceData;
use App\Models\EnrollmentData;


class SchoolObserver
{
    public function created(School $school): void
    {
        //INSTANTIATE GRADES
        $academicYear = AcademicYear::query()->where('status', 'default')->first();
        $type = $school->schoolType->name;
        $grades = GradeLevel::all();

            $allowedGrades = $this->getAllowedGrades($type);

            foreach ($grades as $grade) {

                $isAllowed = in_array(
                    $grade->name,
                    $allowedGrades
                );
                if (!$isAllowed){
                    continue;
                }

                if(!$isAllowed){
                    continue;
                }

                if($academicYear->status === 'upcoming' || $academicYear->status === 'default'){
                        EnrollmentData::create([
                        'academic_year_id' => $academicYear->id,
                        'school_id' => $school->id,
                        'grade_level' => $grade->name,
                        'male_count' => 0,
                        'female_count' => 0,
                    ]);
                }
            }
        
        //INSTANTIATE RESOURCES
        $resources = ['Classrooms', 'Teachers', 'Seats', 'Learning Materials'];

        foreach ($resources as $resourceName) {
                    $req = 0;
                    $inv = 0;
                    ResourceData::create([
                        'academic_year_id' => $academicYear->id,
                        'school_id' => $school->id,
                        'resource_name' => $resourceName,
                        'inventory' => $inv,
                        'requirement' => $req,
                    ]);
                }
    }

   public function updated(School $school): void
        {
            if (!$school->wasChanged('school_type_id')) {
                return;
            }

            $oldType = SchoolType::find($school->getOriginal('school_type_id'))?->name;

            $newType = $school->schoolType->name;

            $oldGrades = $this->getAllowedGrades($oldType);
            $newGrades = $this->getAllowedGrades($newType);

            $gradesToAdd = array_diff(
                $newGrades,
                $oldGrades
            );

            $gradesToRemove = array_diff($oldGrades,$newGrades);

            foreach (AcademicYear::query()->where('status', ['default', 'upcoming'])->get() as $year) {

                foreach ($gradesToAdd as $grade) {

                    EnrollmentData::firstOrCreate([
                        'academic_year_id' => $year->id,
                        'school_id' => $school->id,
                        'grade_level' => $grade,
                    ], [
                        'male_count' => 0,
                        'female_count' => 0,
                    ]);
                }
            }

            EnrollmentData::query()
                ->where('school_id', $school->id)
                ->whereIn('grade_level', $gradesToRemove)
                ->delete();
        }

    private function getAllowedGrades(string $type): array
    {
        return match ($type) {
            'Elementary' => [
                'Kinder','Grade 1','Grade 2','Grade 3',
                'Grade 4','Grade 5','Grade 6',
            ],

            'Junior High School' => [
                'Grade 7','Grade 8','Grade 9','Grade 10',
            ],

            'Standalone SHS' => [
                'Grade 11','Grade 12',
            ],

            'Integrated School',
            'Science High School',
            'ALS',
            'Junior High School with SHS' => [
                'Kinder','Grade 1','Grade 2','Grade 3',
                'Grade 4','Grade 5','Grade 6',
                'Grade 7','Grade 8','Grade 9','Grade 10',
                'Grade 11','Grade 12',
            ],

            default => [],
        };
    }
}
