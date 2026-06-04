<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\Permission\Traits\HasRoles;
use App\Models\User;

class UserResource extends JsonResource
{
    use HasRoles;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        
        return [
            'name' => $this->name,
            'user_id' => $this->id,
            'role' => $this->getRoleNames()->first(),
            'assignment' => [
                'type' => $this->hasRole('School Account') ? 'School' : 'Division',
                'school_id' => $this->school_id,
                'school_name' => $this->school?->school_name,
                'school_code' => $this->school?->school_code,
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
