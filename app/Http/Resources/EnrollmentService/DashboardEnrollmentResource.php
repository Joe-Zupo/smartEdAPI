<?php

namespace App\Http\Resources\EnrollmentService;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardEnrollmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'dashboard_data' => collect($this->resource)->map(function ($row) {
                return $row;
            })->values()->all(),
        ];
    }
}
