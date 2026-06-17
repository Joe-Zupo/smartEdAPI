<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResourceData extends Model
{
    protected $fillable = [
        'submission_id',
        'resource_name',
        'inventory',
        'requirement',
        //'need',
    ];  

    protected $casts = [
        'submission_id' => 'integer'
    ];

    public function submission(){
        return $this->belongsTo(Submission::class, 'submission_id');
    }

    protected static function booted(): void{
        static::created(function (ResourceData $resource) {
        $resource->need = max(0, $resource->requirement - $resource->inventory);
        });

        static::updated(function (ResourceData $resource) {
            $resource->need = max(0, $resource->requirement - $resource->inventory);
        });
    }
}
