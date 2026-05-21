<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    protected $fillable = [
        'starting_date',
        'ending_date',
        'academic_year',
        'status',
    ];
}
