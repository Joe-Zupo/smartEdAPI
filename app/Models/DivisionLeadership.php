<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DivisionLeadership extends Model
{
    protected $fillable = [
        'name',
        'position',
        'is_oic',
        'term_start',
        'term_end',
    ];

    public static function booted(){
        parent::booted();
        static::creating(function ($dl){

            if ($dl->is_oic){
                $newName = "OIC, {$dl->position}";
                $dl->position = $newName;
            }
            
        });

        static::saving(function ($dl){
            if ($dl->is_oic){
                $newName = "OIC, {$dl->position}";
                $dl->position = $newName;
            }else{
                $newName = Str::remove('OIC, ', $dl->position);
                $dl->position = $newName;
            }
        });

    }
}
