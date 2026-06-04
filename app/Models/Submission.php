<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Submission extends Model
{
    public $fillable = [
        'academic_year_id',
        'school_id',
        'user_id',
        'submission_number',
        'type',
        'status',
    ];

    protected $casts = [
        'academic_year_id' => 'integer',
        'school_id' => 'integer',
        'user_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function enrollmentData()
    {
        return $this->hasMany(EnrollmentData::class);
    }

    public function resourceData()
    {
        return $this->hasMany(ResourceData::class);
    }

    public static $creationCounter = 0;

    protected static function booted()
    {
        parent::booted();
        static::creating(function ($submission) {

            self::$creationCounter++;
            $year = AcademicYear::find($submission->academic_year_id);
            $school = School::find($submission->school_id);

            if (!$year || !$school) {
                $submission->submission_number = 'ERR-' . uniqid();
                return;
            }

            // $cleanYears = str_replace(['S.Y.', ' '], '', $year->academic_year);

            // $parts = explode('-', $cleanYears);

            // $yearName = substr($parts[0], -2) . substr($parts[1], -2);

            // $suffix = match ($submission->type) {
            //     'enrollment' => 'ED', // Enrollment Data
            //     'resource' => 'RD', // Resource Data
            //     'information' => 'ID', // School Information Draft
            //     default => 'XX',
            // };
            //$submission->submission_number = "SUB-{$yearName}-{$school->code}-{$suffix}";

            $yearStr = Carbon::parse($year->start_date)->format('Y');

            $counter = self::$creationCounter;
            $digits = strlen($counter);
                switch($digits) {
                    case(1):
                        $suffix = "00{$counter}";
                        break;
                    case(2):
                        $suffix = "0{$counter}";
                        break;
                    case(3):
                    default:
                        $suffix = "{$counter}";
                        break;
                } 
            $submission->submission_number = "SUB-{$yearStr}-{$suffix}";
        });
    }
}
