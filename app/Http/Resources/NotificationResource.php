<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Notifications;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $actionRequired = false;

        // Only compute action_required for School Account users
        $user = $request->user();
        if ($user && $user->hasRole('School Account')) {
            if ($this->title === 'Submission Returned' && $this->submission) {
                // Check if this is the latest "Submission Returned" notification for this submission
                $newerReturnedNotification = Notifications::query()->where('submission_id', $this->submission->id)
                    ->where('title', 'Submission Returned')
                    ->where('created_at', '>', $this->created_at)
                    ->exists();

                // Only set action_required to true if this is the latest returned notification
                // and the submission status is still returned
                $actionRequired = !$newerReturnedNotification && $this->submission->status === 'returned';
            }
        }

        $data = [
            'id' => $this->id,
            'submission_id' => $this->submission->id,
            'submission_number' => $this->submission->submission_number,
            'title' => $this->title,
            'message' => $this->message,
            'is_read' => $this->is_read,
            'created_at' => $this->created_at,
        ];

        // Only include the action_required field for School Account users
        if ($user && $user->hasRole('School Account')) {
            $data['action_required'] = $actionRequired;
        }

        return $data;
    }
}
