<?php

namespace App\Events;

use App\Models\Offer;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OfferSubmitted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Offer $offer;

    public function __construct(Offer $offer)
    {
        $this->offer = $offer->load(['items', 'distributor.distributor', 'order']);
    }

    /**
     * Broadcast to the shop owner who created the order
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('shop.' . $this->offer->order->user_id),
            new PrivateChannel('order.' . $this->offer->order_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'offer.submitted';
    }

    public function broadcastWith(): array
    {
        return [
            'offer' => [
                'id' => $this->offer->id,
                'order_id' => $this->offer->order_id,
                'distributor_id' => $this->offer->distributor_id,
                'total_price' => (float) $this->offer->total_price,
                'estimated_delivery_time' => $this->offer->estimated_delivery_time,
                'status' => $this->offer->status,
                'notes' => $this->offer->notes,
                'created_at' => $this->offer->created_at->toIso8601String(),
                'distributor' => [
                    'name' => $this->offer->distributor?->name,
                    'company_name' => $this->offer->distributor?->distributor?->company_name,
                    'rating' => $this->offer->distributor?->distributor?->rating,
                ],
                'items' => $this->offer->items->map(fn($item) => [
                    'product_id' => $item->product_id,
                    'unit_price' => (float) $item->unit_price,
                    'quantity' => $item->quantity,
                    'subtotal' => (float) $item->subtotal,
                ]),
            ],
        ];
    }
}
