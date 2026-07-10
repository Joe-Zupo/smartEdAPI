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

class PublicEnrollmentTotalsChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $academicYearId;

    public function __construct(int $academicYearId)
    {
        $this->academicYearId = $academicYearId;
    }

    public function broadcastOn(): array
    {
        return [new Channel('public.enrollment')];
    }

    public function broadcastWith(): array
    {
        $data = app(EnrollmentDataService::class)
            ->getPublicEnrollment(
                null,
                $this->academicYearId
            );

        return (new PublicEnrollmentResource($data))->resolve();
    }

    public function broadcastAs(): string
    {
        return 'PublicEnrollmentTotalsChanged';
    }
}
