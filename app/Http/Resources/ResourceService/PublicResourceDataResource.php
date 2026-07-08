<?php

namespace App\Http\Resources\ResourceService;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\DivisionLeadershipResource;

class PublicResourceDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [

            'academic_year' => $this['academic_year'],

            'totals_by_resource' => $this['totals_by_resource'],

            'office_of_the_superintendent' =>
                DivisionLeadershipResource::collection(
                    $this['office_of_the_superintendent']
                ),

        ];
    }
}
