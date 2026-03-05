<?php

namespace App\Services;

use App\Models\Distributor;
use App\Models\NotificationLog;
use App\Models\Order;
use App\Models\Offer;
use App\Models\User;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class NotificationService
{
    /**
     * Send push notification and log it.
     */
    public static function send(User $user, string $title, string $body, string $type = 'system', array $data = []): void
    {
        // Log to database
        NotificationLog::create([
            'user_id' => $user->id,
            'title'   => $title,
            'body'    => $body,
            'type'    => $type,
            'data'    => $data,
        ]);

        // Send Firebase push if token exists
        if ($user->fcm_token) {
            try {
                $messaging = app('firebase.messaging');

                $message = CloudMessage::withTarget('token', $user->fcm_token)
                    ->withNotification(Notification::create($title, $body))
                    ->withData(array_merge($data, ['type' => $type]));

                $messaging->send($message);
            } catch (\Exception $e) {
                \Log::error('FCM notification failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Notify nearby distributors about a new order.
     */
    public static function notifyNearbyDistributors(Order $order): void
    {
        $distributors = Distributor::where('is_active', true)
            ->where('is_verified', true)
            ->get()
            ->filter(function ($distributor) use ($order) {
                $distance = self::haversineDistance(
                    $distributor->latitude,
                    $distributor->longitude,
                    $order->delivery_latitude,
                    $order->delivery_longitude
                );
                return $distance <= $distributor->service_radius_km;
            });

        foreach ($distributors as $distributor) {
            self::send(
                $distributor->user,
                __('notifications.new_order_title'),
                __('notifications.new_order_body', ['number' => $order->order_number]),
                'order',
                ['order_id' => $order->id]
            );
        }
    }

    /**
     * Notify shop owner about a new offer.
     */
    public static function notifyNewOffer(Order $order, Offer $offer): void
    {
        self::send(
            $order->user,
            __('notifications.new_offer_title'),
            __('notifications.new_offer_body', [
                'distributor' => $offer->distributor->company_name,
                'amount'      => $offer->total_amount,
            ]),
            'offer',
            ['order_id' => $order->id, 'offer_id' => $offer->id]
        );
    }

    /**
     * Notify distributor that their offer was accepted.
     */
    public static function notifyOfferAccepted(Offer $offer): void
    {
        self::send(
            $offer->user,
            __('notifications.offer_accepted_title'),
            __('notifications.offer_accepted_body', [
                'number' => $offer->order->order_number,
            ]),
            'offer',
            ['order_id' => $offer->order_id, 'offer_id' => $offer->id]
        );
    }

    /**
     * Calculate distance between two GPS points (Haversine).
     */
    private static function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2 +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
