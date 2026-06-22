<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResourceDraftResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'submission' => $this->whenLoaded('submission', function(){
                return[
                    'id'        =>  $this->submission?->id,
                    'status'    =>  $this->submission?->status,
                    'type'      =>  $this->submission?->type,
                ];
            }),
            
            'id'                => $this->id,
            'resource_name'     => $this->resource_name,
            'inventory'         => $this->inventory,
            'requirement'       => $this->requirement,
            'need'              => $this->need,

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
