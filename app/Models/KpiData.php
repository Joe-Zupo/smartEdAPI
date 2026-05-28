<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiData extends Model
{
    protected $fillable = [
        'kpi_id',
        'academic_year_id',
        'male',
        'female',
        'total',
        'school_type',
    ];

    // Relationship to user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship to KPI rate
    public function kpiRate()
    {
        return $this->belongsTo(KpiRateData::class, 'kpi_id');
    }

    // Relationship to academic year
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
