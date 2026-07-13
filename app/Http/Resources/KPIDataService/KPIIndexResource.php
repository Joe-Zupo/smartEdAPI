<?php

namespace App\Http\Resources\KPIDataService;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\KpiDataResource;
use App\Http\Resources\DivisionLeadershipResource;

class KPIIndexResource extends JsonResource
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

                'items' => KpiDataResource::collection(
                    $this['data']['items']
                ),

                'office_of_the_superintendent' =>
                    isset($this['data']['office_of_the_superintendent'])
                        ? DivisionLeadershipResource::collection(
                            $this['data']['office_of_the_superintendent']
                        )
                        : null,

            ],
            'pagination' => $this['pagination'],
        ];
    }
}
