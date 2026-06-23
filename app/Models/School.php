<?php

namespace App\Models;

use App\Observers\SchoolObserver;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $fillable = [
        'school_name',
        'school_code',
        'year_established',
        'school_type_id',
        'user_id',
        'district',
        'latitude',
        'longitude',
        'address',
        'school_head',
        'position',
        'region',
        'image',
    ];

    protected $casts = [
        'school_type_id' => 'integer',
    ];

    public function schoolType()
    {
        return $this->belongsTo(SchoolType::class, 'school_type_id');
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    public function schoolHead()
     {
         return $this->hasOne(User::class, 'school_id')
              ->whereHas('roles', fn($q) => $q->where('name', 'School Account'));
     }
    protected static function booted()
    {
        parent::booted();
        School::observe(SchoolObserver::class); // Observes School Type Changes and Creates default values for Enrollment and Resource Data
    }
}
