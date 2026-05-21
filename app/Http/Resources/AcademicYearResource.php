<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AcademicYearResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'year_id' => $this->id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'academic_year' => $this->academic_year,
            'status' => $this->status,
            'date_added' => $this->updated_at->format('Y-m-d')
        ];
    }
}
