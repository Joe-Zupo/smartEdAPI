<?php

namespace App\Http\Resources\Kpi;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KpiDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
			'id' => $this->id,
			'kpi_id' => $this->kpi_id,
			'kpi_rate' => $this->whenLoaded('kpiRate', function () {
				return [
					'id' => $this->kpiRate?->id,
					'name' => $this->kpiRate?->name,
				];
			}),
			'academic_year_id' => $this->academic_year_id,
			'academic_year' => $this->whenLoaded('academicYear', function () {
				return [
					'id' => $this->academicYear?->id,
					'name' => $this->academicYear?->name ?? null,
				];
			}),
			'male' => $this->male !== null ? (float) $this->male : null,
			'female' => $this->female !== null ? (float) $this->female : null,
			'total' => $this->total !== null ? (float) $this->total : null,
			'school_type' => $this->school_type,
			'created_at' => $this->created_at?->toDateTimeString(),
			'updated_at' => $this->updated_at?->toDateTimeString(),
		];
    }
}
