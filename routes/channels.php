<?php

use App\Models\Order;
use App\Models\Delivery;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Only register channels when Pusher credentials are configured
if (!env('PUSHER_APP_KEY')) {
    return;
}

// Shop owner's private channel - receives offers and delivery updates
Broadcast::channel('shop.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId && $user->role === 'shop_owner';
});

// Distributor's private channel - receives order acceptances
Broadcast::channel('distributor.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId && $user->role === 'distributor';
});

// Order specific channel - for all parties involved in an order
Broadcast::channel('order.{orderId}', function ($user, $orderId) {
    $order = Order::find($orderId);
    if (!$order) return false;
    
    // Shop owner who created the order
    if ($user->id === $order->user_id) return true;
    
    // Distributors who have made offers on this order
    if ($user->role === 'distributor') {
        return $order->offers()->where('distributor_id', $user->id)->exists();
    }
    
    return false;
});

// Delivery tracking channel
Broadcast::channel('delivery.{deliveryId}', function ($user, $deliveryId) {
    $delivery = Delivery::find($deliveryId);
    if (!$delivery) return false;
    
    // Shop owner tracking their delivery
    if ($delivery->order->user_id === $user->id) return true;
    
    // Distributor doing the delivery
    if ($delivery->distributor_id === $user->id) return true;
    
    return false;
});

// Public orders channel - all distributors can listen
// No authorization needed for public channels
