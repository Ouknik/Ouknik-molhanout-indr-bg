<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order->load(['items.product', 'user.shop']);
    }

    /**
     * Get the channels the event should broadcast on.
     * Broadcast to all distributors in the same city
     */
    public function broadcastOn(): array
    {
        $channels = [
            new Channel('orders'), // Public channel for all distributors
        ];

        // Also broadcast to city-specific channel
        if ($this->order->user?->shop?->city) {
            $channels[] = new Channel('orders.city.' . $this->order->user->shop->city);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'order.created';
    }

    public function broadcastWith(): array
    {
        return [
            'order' => [
                'id' => $this->order->id,
                'order_number' => $this->order->order_number,
                'status' => $this->order->status,
                'items_count' => $this->order->items->count(),
                'shop_name' => $this->order->user?->shop?->shop_name,
                'city' => $this->order->user?->shop?->city,
                'created_at' => $this->order->created_at->toIso8601String(),
                'items' => $this->order->items->map(fn($item) => [
                    'product_name' => $item->product?->name ?? $item->custom_product_name,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit,
                ]),
            ],
        ];
    }
}
