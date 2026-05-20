<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barangay extends Model
{
    public function schools()
    {
        return $this->hasMany(School::class);
    }
}
