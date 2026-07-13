<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use App\Services\ResourceDataService;
use App\Http\Resources\ResourceService\PublicResourceDataResource;
use Illuminate\Queue\SerializesModels;
use App\Models\ResourceData;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class PublicResourceTotalsChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $academicYearId;

    public function __construct(int $academicYearId)
    {
        $this->academicYearId = $academicYearId;
    }

    public function broadcastOn(): array
    {
        return [new Channel('public.resource-data')];
    }

    public function broadcastAs(): string
    {
        return 'PublicResourceTotalsChanged';
    }

    public function broadcastWith(): array
    {
        $data = app(ResourceDataService::class)
            ->getPublicResource(
                null,
                $this->academicYearId
            );

        return (new PublicResourceDataResource($data))
            ->resolve();
    }
}
