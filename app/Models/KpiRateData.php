<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiRateData extends Model
{
    public function kpiData()
    {
        return $this->hasMany(KpiData::class, 'kpi_id');
    }
}
