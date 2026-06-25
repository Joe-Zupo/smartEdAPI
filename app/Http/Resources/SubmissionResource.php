<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\SchoolType;
use App\Models\Comment;
use App\Models\School;
use App\Http\Resources\EnrollmentDraftResource;
use App\Http\Resources\CommentResource;
use App\Helpers\EnrollmentData\GradesDisplay;

class SubmissionResource extends JsonResource
{
    use GradesDisplay;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $comments = Comment::query()->where('submission_id', $this->id)->get();

        if($this->editable === false && $this->status === 'pending'){
            $typeMessage = 'Edit Request - ' . $this->type;
        }
        else{
            $typeMessage = $this->type;
        }
        return [
            'id' => $this->id,
            'submission_number' => $this->submission_number,
            'type' => $typeMessage,
            'status' => $this->status,
            'date_submitted' => $this->updated_at->format('M d, Y h:i A'),
            'comments_count' => $this->comments()->count(),
            'submitted_by' => $this->user->name ?? 'Unknown User',

            'school' => [
                'school_name' => $this->school->school_name,
                'type' => $this->school->schoolType->name, // for testing
                'school_code' => $this->school->school_code,
            ],

            'details' => $this->when(
                $this->relationLoaded('enrollmentDraft') || $this->relationLoaded('resourceDraft') || $this->relationLoaded('schoolInformationDraft'),
                function (){

                    if ($this->type === 'enrollment') {
                        $items = $this->displayRelevant($this->school, $this->enrollmentDraft);
                        return [
                            'items' => EnrollmentDraftResource::collection($items)
                            ];
                    }
                    if ($this->type === 'resource') {
                        return $this->resourceDraft->map(function ($item) {
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
                            'school_name' => $draft->school_name,
                            'school_code' => $draft->school_code,
                            'year_established' => $draft->year_established,
                            'school_type' => $school_type_name,
                            'address' => $draft->address,
                            'district' => $draft->district,
                            'latitude' => $draft->latitude,
                            'longitude' => $draft->longitude,
                            'image' => $draft->image ? asset('storage/' . $draft->image) : null,
                            'school_head' => $this->school->schoolHead->name,
                            'principal_level' => $this->school->position,
                            'principal_contact' => $this->school->schoolHead->phone_number,
                            'principal_email' => $this->school->schoolHead->email,
                        ];    
                    }
                }
            ),

            
            'comments' => CommentResource::collection($comments),

        ];
    }

}
