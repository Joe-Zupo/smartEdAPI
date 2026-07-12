<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SchoolEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $school;
    public $action;
    public function __construct($school, $action)
    {
        // ensure we load relevant relationships before broadcasting
        $this->school = $school->loadMissing(['schoolType', 'schoolHead']);
        $this->action = $action;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new channel('schools'),
        ];
    }

    public function broadcastAs()
    {
        return 'school.' . $this->action;
    }

    public function broadcastWith()
    {
        // always send full school data using resource
        return [
            'action' => $this->action,
            'school' => (new \App\Http\Resources\SchoolResource($this->school))->resolve(),
        ];
    }
   
    }

                

