<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $fillable = [
        'school_name',
        'school_code',
        'year_established',
        'school_type_id',
        'district',
        'latitude',
        'longitude',
        'address',
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

    public function schoolUsers()
    {
        return $this->hasMany(User::class, 'school_id', 'id');
            // ->whereHas('roles', fn($q) => $q->where('name', 'School Account'))
            // ->where('is_head', true);
    }
    public function schoolHead()
    {
        return $this->hasOne(User::class, 'school_id')
             ->whereHas('roles', fn($q) => $q->where('name', 'School Account'))
             ->where('is_head', true);
    }
}
