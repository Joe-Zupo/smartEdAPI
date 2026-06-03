<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Announcement;

class AnnouncementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isPublic = $request->is('api/public/*');
        $isPublicShow = $request->routeIs('announcements.publicShow');
        return [
           'recent_posts' => $this->when(
                $isPublicShow,
                Announcement::where('type', 'public')
                    ->where('id', '!=', $this->id)
                    ->latest()
                    ->get(['id', 'title'])
                    ->values()
            ),
            'title'         => $this->title,
            'description'   => $this->description,
            'type'          => $this->type,
            'image_url' => $this->image
                ? asset('storage/' . $this->image)
                : null,
            'date' => $this->created_at->format('F d, Y'),
            'date_time' => $this->when(! $isPublic, $this->created_at->format('F d, Y h:i A')),
            
            'is_new' => $this->when(! $isPublic, $this->created_at->diffInDays(now()) <= 3),
        ]; 
    }
}
