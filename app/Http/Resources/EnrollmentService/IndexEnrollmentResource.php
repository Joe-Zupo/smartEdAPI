<?php

namespace App\Http\Resources\EnrollmentService;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IndexEnrollmentResource extends JsonResource
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

                'school' => $this['data']['school'] ?? null,

                'items' => $this['data']['items'] ?? null,

                'enrollments_totals' => $this['data']['enrollments_totals'],

                'five_year_trend' => $this['data']['five_year_trend'] ?? null,

                'enrollment_by_level' => $this['data']['enrollment_by_level'] ?? null,

                'enrollment_by_grade' => $this['data']['enrollment_by_grade'] ?? null,
            ],

            'pagination' => $this['pagination'],
        ];
    }
}
