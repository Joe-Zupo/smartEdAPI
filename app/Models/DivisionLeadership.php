<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DivisionLeadership extends Model
{
    protected $fillable = [
        'name',
        'position',
        'is_oic',
        'term_start',
        'term_end',
    ];
}
