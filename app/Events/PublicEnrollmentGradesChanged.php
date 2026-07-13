<?php

namespace App\Events;

use App\Http\Resources\EnrollmentService\PublicEnrollmentResource;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\EnrollmentData;
use App\Services\EnrollmentDataService;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class PublicEnrollmentGradesChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $grades;

    public function __construct(array $grades)
    {
        $this->grades = $grades;
    }

    public function broadcastOn(): array
    {
        return [new Channel('public.enrollment')];
    }

    public function broadcastWith(): array
    {
        return ['enrollment_by_grade' => $this->grades];
    }

    public function broadcastAs(): string
    {
        return 'PublicEnrollmentGradesChanged';
    }
}
