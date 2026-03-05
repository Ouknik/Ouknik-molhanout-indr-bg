<?php

namespace App\Events;

use App\Models\Delivery;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliveryStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Delivery $delivery;
    public string $oldStatus;

    public function __construct(Delivery $delivery, string $oldStatus)
    {
        $this->delivery = $delivery->load(['order.user', 'distributor']);
        $this->oldStatus = $oldStatus;
    }

    /**
     * Broadcast to shop owner tracking their delivery
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
        return 'delivery.status_updated';
    }

    public function broadcastWith(): array
    {
        return [
            'delivery' => [
                'id' => $this->delivery->id,
                'order_id' => $this->delivery->order_id,
                'old_status' => $this->oldStatus,
                'new_status' => $this->delivery->status,
                'current_latitude' => $this->delivery->current_latitude,
                'current_longitude' => $this->delivery->current_longitude,
                'delivered_at' => $this->delivery->delivered_at?->toIso8601String(),
            ],
            'order' => [
                'id' => $this->delivery->order->id,
                'order_number' => $this->delivery->order->order_number,
            ],
            'updated_at' => now()->toIso8601String(),
        ];
    }
}
