<?php

namespace App\Http\Resources\EnrollmentService;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicEnrollmentResource extends JsonResource
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

            'totals' => $this['totals']
        ];
    }
}
