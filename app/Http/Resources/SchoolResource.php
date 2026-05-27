<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SchoolResource extends JsonResource
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
            'school_name' => $this->school_name,
            'school_code' => $this->school_code,
            'year_established' => $this->year_established,
            'school_type' => $this->whenLoaded('schoolType', function () {
                return [
                    'id' => $this->schoolType?->id,
                    'name' => $this->schoolType?->name,
                ];
            }),
            'street' => $this->street,
            'city' => $this->city,
            'barangay' => $this->whenLoaded('barangay', function () {
                return [
                    'id' => $this->barangay?->id,
                    'name' => $this->barangay?->name,
                ];
            }),
            'province' => $this->province,
            'district' => $this->district,
            'image' => $this->image
                ? asset('storage/' . $this->image)
                : null,
            'region' => $this->region,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
