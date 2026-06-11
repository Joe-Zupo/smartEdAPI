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

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected static function booted(){
        parent::booted();
        static::creating(function ($academic_year){

        $start = $academic_year->start_date->format('Y');
        $end = $academic_year->end_date->format('Y');

        $academic_year->academic_year = "S.Y. {$start} - {$end}";
        
        });

        static::saving(function ($academic_year){
        
        $start = $academic_year->start_date->format('Y');
        $end = $academic_year->end_date->format('Y');

        $academic_year->academic_year = "S.Y. {$start} - {$end}";

        });
    }
}
