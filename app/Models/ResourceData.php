<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResourceData extends Model
{
    protected $fillable = [
        'academic_year_id',
        'school_id',
        'submission_id',
        'resource_name',
        'inventory',
        'requirement',
        //'need',
    ];  

    protected $casts = [
        'submission_id' => 'integer',
        'school_id' => 'integer',
    ];

    protected static function booted(): void{
        static::created(function (ResourceData $resource) {
        $resource->need = max(0, $resource->requirement - $resource->inventory);
        });

        static::updated(function (ResourceData $resource) {
            $resource->need = max(0, $resource->requirement - $resource->inventory);
        });
    }
}
