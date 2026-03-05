<?php

namespace App\Events;

use App\Models\Offer;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OfferPriceUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Offer $offer;
    public float $oldPrice;

    public function __construct(Offer $offer, float $oldPrice)
    {
        $this->offer = $offer->load(['items', 'distributor.distributor']);
        $this->oldPrice = $oldPrice;
    }

    /**
     * Broadcast to shop owner
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
        return 'offer.price_updated';
    }

    public function broadcastWith(): array
    {
        return [
            'offer' => [
                'id' => $this->offer->id,
                'order_id' => $this->offer->order_id,
                'distributor_id' => $this->offer->distributor_id,
                'old_price' => $this->oldPrice,
                'new_price' => (float) $this->offer->total_price,
                'price_change' => (float) $this->offer->total_price - $this->oldPrice,
                'estimated_delivery_time' => $this->offer->estimated_delivery_time,
                'distributor' => [
                    'name' => $this->offer->distributor?->name,
                    'company_name' => $this->offer->distributor?->distributor?->company_name,
                ],
                'items' => $this->offer->items->map(fn($item) => [
                    'product_id' => $item->product_id,
                    'unit_price' => (float) $item->unit_price,
                    'quantity' => $item->quantity,
                    'subtotal' => (float) $item->subtotal,
                ]),
            ],
            'updated_at' => now()->toIso8601String(),
        ];
    }
}
