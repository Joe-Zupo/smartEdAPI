<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use App\Http\Resources\KPIDataService\KPIIndexResource;
use App\Services\KpiDataService;

class PublicKpiDataChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $academicYearId;

    /**
     * Create a new event instance.
     * 
     * @param string $action (store, update, delete)
     * @param Collection|\Illuminate\Support\Collection $models Collection of KpiData models
     */
    public function __construct(int $academicYearId)
    {
        $this->academicYearId = $academicYearId;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [new Channel('public.kpi-data')];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {

        $data = app(KpiDataService::class)->getIndex(null, true, $this->academicYearId);

        return 
            (new KpiIndexResource($data))->resolve();
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'PublicKpiDataChanged';
    }
}
