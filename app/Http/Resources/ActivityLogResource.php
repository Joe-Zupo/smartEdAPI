<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityLogResource extends JsonResource
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
            'log_name' => $this->log_name,
            'user' => [
                'name' => $this->causer->name ?? 'Unknown',
                'school' => $this->causer->school->school_name,
            ],
            'description' => $this->description,
            'datetime' => $this->properties['datetime'] ?? null,
        ];
    }
}
