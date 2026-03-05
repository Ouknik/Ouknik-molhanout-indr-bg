<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Events\DeliveryStatusUpdated;
use App\Events\DeliveryLocationUpdated;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
    /**
     * List distributor's deliveries.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $distributor = $user->distributor;

        if (!$distributor) {
            return response()->json(['success' => false, 'message' => 'No distributor profile'], 404);
        }

        $query = Delivery::with(['order.items.product', 'order.shop'])
            ->where('distributor_id', $distributor->id);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $deliveries = $query->latest()->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data'    => $deliveries->items(),
            'meta'    => [
                'current_page' => $deliveries->currentPage(),
                'last_page'    => $deliveries->lastPage(),
                'per_page'     => $deliveries->perPage(),
                'total'        => $deliveries->total(),
            ],
        ]);
    }

    /**
     * Update delivery status (distributor action).
     */
    public function updateStatus(int $id, Request $request): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:preparing,on_the_way,delivered',
        ]);

        $user = $request->user();
        $distributor = $user->distributor;

        if (!$distributor) {
            return response()->json(['success' => false, 'message' => 'No distributor profile'], 404);
        }

        $delivery = Delivery::where('distributor_id', $distributor->id)
            ->findOrFail($id);

        // Validate state transitions — prevent invalid regressions
        $allowedTransitions = [
            'pending'    => ['preparing'],
            'preparing'  => ['on_the_way'],
            'on_the_way' => ['delivered'],
        ];

        $current = $delivery->status;
        if (!isset($allowedTransitions[$current]) || !in_array($request->status, $allowedTransitions[$current])) {
            return response()->json([
                'success' => false,
                'message' => __('delivery.invalid_transition'),
            ], 422);
        }

        // Update order status accordingly
        $statusMap = [
            'preparing'   => 'preparing',
            'on_the_way'  => 'on_delivery',
            'delivered'   => 'delivered',
        ];

        DB::transaction(function () use ($delivery, $request, $statusMap) {
            $delivery->update(['status' => $request->status]);
            $delivery->order->update(['status' => $statusMap[$request->status]]);

            if ($request->status === 'on_the_way') {
                $delivery->update(['picked_up_at' => now()]);
            }
        });

        // Broadcast real-time event to shop owner
        broadcast(new DeliveryStatusUpdated($delivery->fresh()->load(['order.shop', 'distributor'])))->toOthers();

        return response()->json([
            'success' => true,
            'message' => __('delivery.status_updated'),
            'data'    => $delivery->fresh()->load('order'),
        ]);
    }

    /**
     * Confirm delivery with PIN.
     */
    public function confirmWithPin(int $id, Request $request): JsonResponse
    {
        $request->validate(['pin' => 'required|string|size:6']);

        $user = $request->user();
        $distributor = $user->distributor;

        if (!$distributor) {
            return response()->json(['success' => false, 'message' => 'No distributor profile'], 404);
        }

        $delivery = Delivery::where('distributor_id', $distributor->id)
            ->findOrFail($id);

        if ($delivery->confirmDelivery($request->pin)) {
            $distributor->increment('total_orders');

            return response()->json([
                'success' => true,
                'message' => __('delivery.confirmed'),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('delivery.invalid_pin'),
        ], 400);
    }

    /**
     * Update live location during delivery.
     */
    public function updateLocation(int $id, Request $request): JsonResponse
    {
        $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $user = $request->user();
        $delivery = Delivery::where('distributor_id', $user->distributor->id)
            ->findOrFail($id);

        $delivery->updateLocation($request->latitude, $request->longitude);
// Broadcast real-time location to shop owner
        broadcast(new DeliveryLocationUpdated($delivery->fresh()))->toOthers();

        
        return response()->json(['success' => true]);
    }

    /**
     * Show delivery details.
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $user = $request->user();
        $distributor = $user->distributor;

        if (!$distributor) {
            return response()->json(['success' => false, 'message' => 'No distributor profile'], 404);
        }

        $delivery = Delivery::with([
            'order.items.product',
            'order.shop',
            'offer.items.product',
            'distributor',
        ])->where('distributor_id', $distributor->id)
          ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $delivery,
        ]);
    }
}
