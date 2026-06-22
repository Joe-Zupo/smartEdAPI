<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnrollmentData extends Model
{
    protected $fillable = [
        'academic_year_id',
        'school_id',

        'grade_level',
        'male_count',
        'female_count',
        'total_count',
    ];

    protected $casts = [
        'school_id' => 'integer',
        'total_count' => 'integer',
    ];

    public function gradeLevel(){
        return $this->belongsTo(GradeLevel::class);
    }

    protected static function booted(): void{
        static::updated(function (EnrollmentData $enrollment) {
            $enrollment->total_count = $enrollment->male_count + $enrollment->female_count;
        });
    }
}
