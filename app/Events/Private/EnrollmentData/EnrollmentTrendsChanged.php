<?php

namespace App\Events\Private\EnrollmentData;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class EnrollmentTrendsChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $fiveYearTrend;
    public int $schoolId;

    public function __construct(array $fiveYearTrend, int $schoolId)
    {
        $this->schoolId = $schoolId;
        $this->fiveYearTrend = $fiveYearTrend;
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('school.enrollment.'.$this->schoolId)];
    }

    public function broadcastWith(): array
    {
        return [
            'five_year_trend' => $this->fiveYearTrend,
        ];
    }

    public function broadcastAs(): string
    {
        return 'PrivateEnrollmentTrendsChanged';
    }
}
