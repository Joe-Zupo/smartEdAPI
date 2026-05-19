<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\School;

class SchoolType extends Model
{
    protected $fillable = [
        'name',
    ];

    public function schools(){
        return $this->hasMany(School::class, 'school_type_id');
    }
}
