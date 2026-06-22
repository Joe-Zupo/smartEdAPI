<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\School;
use App\Models\AcademicYear;

class EnrollmentDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $school = School::query()->where('id', $this->school_id)->first('school_name');
        $academicYear = AcademicYear::query()->where('id', $this->academic_year_id)->first('academic_year');
        return [
            'id' => $this->id,
            'academic_year' => $academicYear,
            'school' => $school,
            'grade_level' => $this->grade_level,
            'male_count' => $this->male_count,
            'female_count' => $this->female_count,
            'total_count' => $this->total_count,
        ];
    }
}
