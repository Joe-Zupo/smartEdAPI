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

    public function users()
    {
        return $this->hasOne(User::class, 'school_id');
    }
}
