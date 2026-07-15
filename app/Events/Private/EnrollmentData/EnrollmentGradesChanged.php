<?php

namespace App\Events\Private\EnrollmentData;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EnrollmentGradesChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $grades;
    public int $schoolId;

    public function __construct(array $grades, int $schoolId)
    {
        $this->grades = $grades;
        $this->schoolId = $schoolId;
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('school.enrollment.'.$this->schoolId)];
    }

    public function broadcastWith(): array
    {
        return ['enrollment_by_grade' => $this->grades];
    }

    public function broadcastAs(): string
    {
        return 'PrivateEnrollmentGradesChanged';
    }
}
