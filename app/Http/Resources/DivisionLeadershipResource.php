<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DivisionLeadershipResource extends JsonResource
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
            'name' => $this->name,
            'is_oic' => (bool) $this->is_oic,
            'search' => $this->search,
            'position' => $this->position,
            'term_start' => $this->term_start,
            'term_end' => $this->term_end,
            'term_end_display' => $this->term_end ?? 'Present',
            'is_current' => is_null($this->getRawOriginal('term_end')),
        ];
    }
}
