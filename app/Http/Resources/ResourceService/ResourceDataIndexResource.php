<?php

namespace App\Http\Resources\ResourceService;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ResourceDataResource;

class ResourceDataIndexResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [

            'data' => [

                'academic_year' => $this['data']['academic_year'],

                'items' => ResourceDataResource::collection(
                    $this['data']['items']
                ),

                'totals_by_resource' => $this['data']['totals_by_resource'],

            ],

            'pagination' => $this['pagination'],

        ];
    }
}
