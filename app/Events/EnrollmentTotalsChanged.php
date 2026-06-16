<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\EnrollmentData;

class EnrollmentTotalsChanged implements ShouldBroadcast
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

    public function broadcastAs(): string
    {
        return 'EnrollmentTotalsChanged';
    }

    public function broadcastWith(): array
    {
        $totals = EnrollmentData::whereHas('submission', function ($q) {
            $q->where('status', 'approved')
              ->where('academic_year_id', $this->academicYearId);
        })
        ->selectRaw(
            'COALESCE(SUM(male_count),0) as total_male, '
            . 'COALESCE(SUM(female_count),0) as total_female, '
            . 'COALESCE(SUM(total_count),0) as total_students'
        )
        ->first();

        return [
            'academic_year_id' => $this->academicYearId,
            'totals' => [
                'total_male' => (int) ($totals->total_male ?? 0),
                'total_female' => (int) ($totals->total_female ?? 0),
                'total_students' => (int) ($totals->total_students ?? 0),
            ],
        ];
    }
}