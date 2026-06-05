<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeLevel extends Model
{
    protected $fillable = [
        'name',
    ];

    public function enrollmentData()
    {
        return $this->hasMany(EnrollmentData::class);
    }
}
