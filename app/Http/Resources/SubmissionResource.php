<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\SchoolType;
use App\Models\School;

class SubmissionResource extends JsonResource
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
            'submission_number' => $this->submission_number,
            'type' => $this->type,
            'status' => $this->status,
            'date_submitted' => $this->updated_at->format('M d, Y h:i A'),
            'comments_count' => $this->comments()->count(),
            'submitted_by' => $this->user->name ?? 'Unknown User',

            'school' => [
                'name' => $this->school->school_name,
                'type' => $this->school->schoolType->name, // for testing
                'code' => $this->school->school_code,
            ],

            'details' => $this->when(
                $this->relationLoaded('enrollmentData') || $this->relationLoaded('resourceData') || $this->relationLoaded('schoolInformationDraft'),
                function (){

                    if ($this->type === 'enrollment') {
                        $items = $this->displayRelevant($this->school, $this->enrollmentData);
                        return [
                            'items' => EnrollmentDataResource::collection($items)
                            ];
                    }
                    if ($this->type === 'resource') {
                        return $this->resourceData->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'resource_name' => $item->resource_name,
                                'inventory' => $item->inventory,
                                'requirement' => $item->requirement,
                                'need' => $item->need,
                            ];
                        });
                    }
                    if ($this->type === 'information') {
                        $draft = $this->schoolInformationDraft()->latest()->first();

                        if (!$draft) {
                            return null;
                        }
                        $school_type_name = SchoolType::where('id', $draft->school_type_id)->value('name');
                        return [
                            'name' => $draft->name,
                            'code' => $draft->code,
                            'year_established' => $draft->year_established,
                            'school_type' => $school_type_name,
                            'address' => $draft->address,
                            'district' => $draft->district,
                            'latitude' => $draft->latitude,
                            'longitude' => $draft->longitude,
                            'image' => $draft->image ? asset('storage/' . $draft->image) : null,
                            'school_head' => $this->school->schoolHead->name,
                            'principal_level' => $this->school->schoolHead->principal_level,
                            'principal_contact' => $this->school->schoolHead->phone_number,
                            'principal_email' => $this->school->schoolHead->email,
                        ];    
                    }
                }
            )

        ];
    }

    private function displayRelevant(School $school, $items){
        
            $type = $school->schoolType->name;
            $allowedGrades = match ($type) {  
                    'Elementary' => [
                        'Kinder','Grade 1','Grade 2','Grade 3','Grade 4','Grade 5','Grade 6',
                    ],

                    'Junior High School' => [
                        'Grade 7','Grade 8','Grade 9','Grade 10',
                    ],

                    'Standalone SHS' => [
                        'Grade 11','Grade 12',
                    ],

                    'Integrated School',
                    'Science High School',
                    'ALS',
                    'Junior High School with SHS' => [
                        'Kinder','Grade 1','Grade 2','Grade 3','Grade 4','Grade 5','Grade 6','Grade 7','Grade 8','Grade 9','Grade 10','Grade 11','Grade 12',
                    ],

                    default => [],
                };

            return $items->filter(function ($item) use ($allowedGrades) {

                return in_array(
                    $item->grade_level,
                    $allowedGrades
                );

            })->values();
        }

}
