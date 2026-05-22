<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    protected $fillable = [
        'start_date',
        'end_date',
        'academic_year',
        'status',
    ];
}
