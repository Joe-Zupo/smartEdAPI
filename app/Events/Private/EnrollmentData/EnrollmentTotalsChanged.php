<?php

namespace App\Events\Private\EnrollmentData;

use App\Helpers\responseAPI;
use App\Http\Resources\EnrollmentService\IndexEnrollmentResource;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\School;
use App\Services\EnrollmentDataService;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class EnrollmentTotalsChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels, responseAPI;

    /**
     * Create a new event instance.
     */
    public int $schoolId;
    public int $academicYearId;
    public string $mode;

    public function __construct(int $schoolId, int $academicYearId, string $mode = 'Admin')
    {
        $this->schoolId = $schoolId;
        $this->academicYearId = $academicYearId;
        $this->mode = $mode;
    }

    public function broadcastOn(): array
    {
        if($this->mode === 'Admin'){
            return [new PrivateChannel('admin.enrollment')];
        }else if($this->mode === 'School'){
            return [new PrivateChannel('admin.enrollment.'.$this->schoolId)];
        }else{
            return [$this->error('Broadcast could not be completed', 409)];
        }
    }
    public function broadcastWith(): array
    {
        if($this->mode === 'Admin'){
            $data = app(EnrollmentDataService::class)
            ->getIndex(null,null,$this->academicYearId,null);

            return (new IndexEnrollmentResource($data))->resolve();
        }else if($this->mode === 'School'){
            $school = School::query()->where('id', $this->schoolId)->first();
            $data = app(EnrollmentDataService::class)
            ->getIndex(null,null,$this->academicYearId,$school);

            return (new IndexEnrollmentResource($data))->resolve();
        }else{
            return [$this->error('Broadcast could not be completed', 409)];
        }
    }

    public function broadcastAs(): string
    {
        if($this->mode === 'Admin'){
            return 'Admin.PrivateEnrollmentTotalsChanged';
        }else if($this->mode === 'School'){
            return 'School.PrivateEnrollmentTotalsChanged';
        }else{
            return $this->error('Broadcast could not be completed', 409);
        }
    }
}
