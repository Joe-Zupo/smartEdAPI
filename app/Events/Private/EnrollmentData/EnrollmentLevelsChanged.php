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

class EnrollmentLevelsChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $levels;

    public function __construct(array $levels)
    {
        $this->levels = $levels;
    }

    public function broadcastOn(): array
    {
        return [new Channel('public.enrollment')];
    }

    public function broadcastWith(): array
    {
        return ['enrollment_by_level' => $this->levels];
    }

    public function broadcastAs(): string
    {
        return 'PrivateEnrollmentLevelsChanged';
    }
}
