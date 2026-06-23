<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolInformationDraft extends Model
{
    protected $fillable = [
        'submission_id',
        'school_id',
        'school_name',
        'school_code',
        'year_established',
        'school_type_id',
        'address',
        'district',
        'latitude',
        'longitude',
        'image',
    ];

    protected $casts = [
        'submission_id' => 'integer',
        'school_id' => 'integer',
        'school_type_id' => 'integer',
    ];

    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

}
