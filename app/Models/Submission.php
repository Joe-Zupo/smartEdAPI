<?php

namespace App\Models;

use App\Observers\Observers\SubmissionObserver;
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
        'editable'
    ];

    protected $casts = [
        'academic_year_id' => 'integer',
        'school_id' => 'integer',
        'user_id' => 'integer',
        'editable' => 'boolean',
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
        return $this->hasMany(Notifications::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }


    //Details Relationships

        public function enrollmentDraft()
    {
        return $this->hasMany(EnrollmentDataDraft::class);
    }

    public function resourceDraft()
    {
        return $this->hasMany(ResourceDataDraft::class);
    }

    public function schoolInformationDraft()
    {
        return $this->hasOne(SchoolInformationDraft::class);
    }

    public static $creationCounter = 0;

    protected static function booted()
    {
        parent::booted();
        Submission::observe(SubmissionObserver::class);
        static::creating(function ($submission) {

        $year = AcademicYear::find($submission->academic_year_id);

            if (!$year) {
                $submission->submission_number = 'ERR-' . uniqid();
                return;
            }

            $yearStr = Carbon::parse($year->start_date)->format('Y');

            $counter = Submission::query()->where(
                'academic_year_id',
                $submission->academic_year_id
            )->count() + 1;

            $suffix = str_pad($counter, 3, '0', STR_PAD_LEFT);

            $submission->submission_number =
                "SUB-{$yearStr}-{$suffix}";
        });
    }
}
