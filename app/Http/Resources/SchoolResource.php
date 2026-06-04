<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class SchoolResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $addressArray = Str::of($this->address)->explode(', ');

        $street = $addressArray[0];
        $barangay = $addressArray[1];
        $city = $addressArray[2];
        $province = $addressArray[3];
        return [
            'id' => $this->id,
            'school_name' => $this->school_name,
            // 'school_head' => $this->whenLoaded('schoolHead', function (){
            //     return [
            //         'id' => $this->schoolHead?->id,
            //         'is_head' => boolval($this->schoolHead?->is_head),
            //         'name' => $this->schoolHead?->name,
            //         'position' => $this->schoolHead?->position
            //     ];
            // }),
            'school_head' => $this->school_head,
            'position' => $this->position,
            'phone_number' => $this->phone_number,
            'head_email' => $this->head_email,
            'school_code' => $this->school_code,
            'year_established' => $this->year_established,
            'school_type' => $this->whenLoaded('schoolType', function () {
                return [
                    'id' => $this->schoolType?->id,
                    'name' => $this->schoolType?->name,
                ];
            }),
            'address' => 
                [
                    'street' => $street,
                    'city'  => $city,
                    'barangay' => $barangay,
                    'province' => $province,
                ]
            ,
            'district' => $this->district,
            'latitude' => $this->latitude !== null
                ? ($this->latitude >= 0 ? 'N ' : 'S ') . number_format(abs($this->latitude), 6)
                : null,

            'longitude' => $this->longitude !== null
                ? ($this->longitude >= 0 ? 'E ' : 'W ') . number_format(abs($this->longitude), 6)
                : null,

            'image' => $this->image
                ? asset('storage/' . $this->image)
                : null,
                
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
