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

            'totals' => $this['totals'],

            'five_year_trend' => $this['five_year_trend'],

            'enrollment_by_level' => $this['enrollment_by_level'],

            'enrollment_by_grade' => $this['enrollment_by_grade'],

            'office_of_the_superintendent' => $this['office_of_the_superintendent'],
        ];
    }
}
