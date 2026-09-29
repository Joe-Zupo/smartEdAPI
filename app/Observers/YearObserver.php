<?php

namespace App\Observers;

use App\Models\AcademicYear;
use App\Models\KpiData;
use App\Models\KpiRateData;
use App\Models\School;
use App\Support\SchoolYearRows;

class YearObserver
{
    public function created(AcademicYear $academicYear): void
    {
        $schoolTypes = ['Elementary','Integrated School','Junior High School','Junior High School with SHS','Standalone SHS','Science High School','ALS'];
        $kpiIds = KpiRateData::query()->pluck('id');

        foreach ($schoolTypes as $schoolType) {
            foreach ($kpiIds as $kpiId) {
                KpiData::firstOrCreate([
                    'kpi_id' => $kpiId,
                    'academic_year_id' => $academicYear->id,
                    'school_type' => $schoolType,
                ], [
                    'male' => 0,
                    'female' => 0,
                    'total' => 0,
                ]);
            }
        }

        foreach (School::with('schoolType')->get() as $school) {
            SchoolYearRows::ensure($school, $academicYear);
        }
    }
}
