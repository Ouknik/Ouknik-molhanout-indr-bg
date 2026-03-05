<?php

namespace App\Events;

use App\Models\Delivery;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliveryLocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Delivery $delivery;

    public function __construct(Delivery $delivery)
    {
        $this->delivery = $delivery;
    }

    /**
     * Broadcast to shop owner tracking the delivery
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('shop.' . $this->delivery->order->user_id),
            new PrivateChannel('delivery.' . $this->delivery->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'delivery.location_updated';
    }

    public function broadcastWith(): array
    {
        return [
            'delivery_id' => $this->delivery->id,
            'latitude' => (float) $this->delivery->current_latitude,
            'longitude' => (float) $this->delivery->current_longitude,
            'updated_at' => now()->toIso8601String(),
        ];
    }
}
