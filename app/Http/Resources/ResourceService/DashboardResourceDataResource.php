<?php

namespace App\Http\Resources\ResourceService;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResourceDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [

            'year' => $this['year'],

            'total_schools' => $this['total_schools'] ?? null,

            'total_students' => $this['total_students'],

            'classrooms' => $this['classrooms'] ?? 0,

            'teachers' => $this['teachers'] ?? 0,

        ];
    }
}
