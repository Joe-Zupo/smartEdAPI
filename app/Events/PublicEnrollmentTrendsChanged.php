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

class PublicEnrollmentTrendsChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $fiveYearTrend;

    public function __construct(array $fiveYearTrend)
    {
        $this->fiveYearTrend = $fiveYearTrend;
    }

    public function broadcastOn(): array
    {
        return [new Channel('public.enrollment')];
    }

    public function broadcastWith(): array
    {
        return [
            'five_year_trend' => $this->fiveYearTrend,
        ];
    }

    public function broadcastAs(): string
    {
        return 'PublicEnrollmentTrendsChanged';
    }
}
