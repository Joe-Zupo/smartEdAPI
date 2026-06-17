<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnrollmentData extends Model
{
    protected $fillable = [
        'grade_level',
        'male_count',
        'female_count',
        'total_count',
    ];

    public function submission(){
        return $this->belongsTo(Submission::class, 'submission_id');
    }
    public function gradeLevel(){
        return $this->belongsTo(GradeLevel::class);
    }

    protected static function booted(): void{
        static::updated(function (EnrollmentData $enrollment) {
            $enrollment->total_count = $enrollment->male_count + $enrollment->female_count;
        });
    }
}
