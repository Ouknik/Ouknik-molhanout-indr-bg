<?php

namespace App\Events;

use App\Models\Offer;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OfferAccepted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Offer $offer;

    public function __construct(Offer $offer)
    {
        $this->offer = $offer->load(['order', 'distributor']);
    }

    /**
     * Broadcast to:
     * - The winning distributor
     * - All other distributors who made offers (to notify rejection)
     * - Public orders channel
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('orders'), // All distributors
            new PrivateChannel('distributor.' . $this->offer->distributor_id), // Winning distributor
            new PrivateChannel('order.' . $this->offer->order_id), // Order specific channel
        ];
    }

    public function broadcastAs(): string
    {
        return 'offer.accepted';
    }

    public function broadcastWith(): array
    {
        return [
            'offer' => [
                'id' => $this->offer->id,
                'order_id' => $this->offer->order_id,
                'distributor_id' => $this->offer->distributor_id,
                'total_price' => (float) $this->offer->total_price,
                'status' => $this->offer->status,
            ],
            'order' => [
                'id' => $this->offer->order->id,
                'order_number' => $this->offer->order->order_number,
                'status' => $this->offer->order->status,
            ],
            'message' => 'Offer has been accepted',
        ];
    }
}
