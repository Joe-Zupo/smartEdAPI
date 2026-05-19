<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'school' => [
                'id' => $this->school_id,
                'school_name' => $this->school->school_name ?? 'No school assigned',
                'school_code' => $this->school->school_code ?? 'No code assigned'
            ],
            'username' => $this->username,
            'email' => $this->email,
            'password' => $this->password,
            'phone_number' => $this->phone_number,
            'is_active' => boolval($this->is_active),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
