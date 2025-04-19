<?php
namespace App\Events;

use App\Models\Rider;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RiderLocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $rider;

    public function __construct(Rider $rider)
    {
        $this->rider = $rider;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('riders.' . $this->rider->id),
            new Channel('admin.riders'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'rider_id' => $this->rider->id,
            'latitude' => $this->rider->current_location->latitude,
            'longitude' => $this->rider->current_location->longitude,
            'updated_at' => $this->rider->location_updated_at->toIso8601String(),
        ];
    }
}