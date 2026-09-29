<?php

namespace App\Observers;

use App\Models\AcademicYear;
use App\Models\School;
use App\Models\SchoolType;
use App\Models\ResourceData;
use App\Models\EnrollmentData;
use App\Support\GradeOfferings;

class SchoolObserver
{
    public function created(School $school): void
    {
        //INSTANTIATE GRADES
        $academicYear = AcademicYear::query()->where('status', 'default')->first();
        $type = $school->schoolType->name;

        foreach (GradeOfferings::forType($type) as $grade) {

            if ($academicYear->status === 'upcoming' || $academicYear->status === 'default') {
                EnrollmentData::create([
                    'academic_year_id' => $academicYear->id,
                    'school_id' => $school->id,
                    'grade_level' => $grade,
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

            $oldGrades = GradeOfferings::forType($oldType);
            $newGrades = GradeOfferings::forType($newType);

            $gradesToAdd = array_diff(
                $newGrades,
                $oldGrades
            );

            $gradesToRemove = array_diff($oldGrades,$newGrades);

            foreach (AcademicYear::query()->whereIn('status', ['default', 'upcoming'])->get() as $year) {

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
}
