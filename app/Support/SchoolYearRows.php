<?php

namespace App\Support;

use App\Models\AcademicYear;
use App\Models\EnrollmentData;
use App\Models\ResourceData;
use App\Models\School;
use App\Models\SchoolType;
use Illuminate\Database\Eloquent\Collection;

final class SchoolYearRows
{
    private const RESOURCES = ['Classrooms', 'Teachers', 'Seats', 'Learning Materials'];

    /** Academic Years that receive rows: Current Year and Upcoming Years. */
    public static function openYears(): Collection
    {
        return AcademicYear::query()->whereIn('status', ['default', 'upcoming'])->get();
    }

    /** Idempotent. Creates missing Enrollment Data and Resource Data rows for one School and one Academic Year. */
    public static function ensure(School $school, AcademicYear $year): void
    {
        $type = self::typeName($school);

        foreach (GradeOfferings::forType($type) as $grade) {
            EnrollmentData::firstOrCreate([
                'academic_year_id' => $year->id,
                'school_id' => $school->id,
                'grade_level' => $grade,
            ], [
                'male_count' => 0,
                'female_count' => 0,
            ]);
        }

        foreach (self::RESOURCES as $resource) {
            ResourceData::firstOrCreate([
                'academic_year_id' => $year->id,
                'school_id' => $school->id,
                'resource_name' => $resource,
            ], [
                'inventory' => 0,
                'requirement' => 0,
            ]);
        }
    }

    /**
     * Uses the loaded schoolType relation only when it still matches school_type_id.
     * After a type change the loaded relation holds the old type, so fall back to a query.
     */
    private static function typeName(School $school): ?string
    {
        if ($school->relationLoaded('schoolType') && $school->schoolType?->id === $school->school_type_id) {
            return $school->schoolType->name;
        }

        return SchoolType::find($school->school_type_id)?->name;
    }
}