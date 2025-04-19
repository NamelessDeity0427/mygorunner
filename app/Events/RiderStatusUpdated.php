<?php
namespace App\Events;

use App\Models\Rider;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RiderStatusUpdated implements ShouldBroadcast
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
            'status' => $this->rider->status,
            'updated_at' => now()->toIso8601String(),
        ];
    }
}