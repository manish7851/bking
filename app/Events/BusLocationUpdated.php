<?php

namespace App\Events;

use App\Models\Bus;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BusLocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The bus instance.
     *
     * @var \App\Models\Bus
     */
    public $bus;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\Bus  $bus
     * @return void
     */
    public function __construct(Bus $bus)
    {
        $this->bus = $bus;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('bus-tracking');
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'bus.location.updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'id' => $this->bus->id,
            'bus_name' => $this->bus->bus_name,
            'bus_number' => $this->bus->bus_number,
            'latitude' => $this->bus->latitude,
            'longitude' => $this->bus->longitude,
            'speed' => $this->bus->speed,
            'heading' => $this->bus->heading,
            'status' => $this->bus->status,
            'last_tracked_at' => $this->bus->last_tracked_at,
            'updated_at' => $this->bus->updated_at
        ];
    }
}
