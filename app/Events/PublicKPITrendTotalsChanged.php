<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Services\KpiDataService;
use App\Http\Resources\KPIDataService\KPITrendResource;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class PublicKPITrendTotalsChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $totals;

    /**
     * Create a new event instance.
     * 
     * @param string $action (store, update, delete)
     * @param Collection|\Illuminate\Support\Collection $models Collection of KpiData models
     */
    public function __construct(array $totals)
    {

        $this->totals = $totals;
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
        return ['kpi_trends_total' => $this->totals];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'PublicKpiTrendTotalsChanged';
    }
}
