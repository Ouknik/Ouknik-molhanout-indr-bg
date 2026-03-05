<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\OfferItem;
use App\Models\Order;
use App\Events\OfferSubmitted;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OfferController extends Controller
{
    /**
     * List nearby orders available for offers (Distributor view).
     */
    public function availableOrders(Request $request): JsonResponse
    {
        $user = $request->user();
        $distributor = $user->distributor;

        if (!$distributor) {
            return response()->json(['success' => false, 'message' => 'No distributor profile'], 404);
        }

        $orders = Order::with(['items.product', 'shop'])
            ->whereIn('status', ['published', 'receiving_offers'])
            ->nearby(
                $distributor->latitude,
                $distributor->longitude,
                $distributor->service_radius_km
            )
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data'    => $orders->items(),
            'meta'    => [
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
                'per_page'     => $orders->perPage(),
                'total'        => $orders->total(),
            ],
        ]);
    }

    /**
     * Submit an offer for an order.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id'                    => 'required|exists:orders,id',
            'delivery_cost'               => 'required|numeric|min:0',
            'estimated_delivery_time'     => 'nullable|string',
            'estimated_delivery_date'     => 'nullable|date',
            'notes'                       => 'nullable|string|max:1000',
            'items'                       => 'required|array|min:1',
            'items.*.order_item_id'       => 'required|exists:order_items,id',
            'items.*.product_id'          => 'required|exists:products,id',
            'items.*.unit_price'          => 'required|numeric|min:0',
            'items.*.quantity'            => 'required|numeric|min:0',
            'items.*.is_available'        => 'boolean',
            'items.*.notes'              => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $distributor = $user->distributor;

        if (!$distributor) {
            return response()->json(['success' => false, 'message' => 'No distributor profile'], 404);
        }

        // Check order is in valid state
        $order = Order::whereIn('status', ['published', 'receiving_offers'])
            ->findOrFail($request->order_id);

        // Check distributor hasn't already submitted
        $existing = Offer::where('order_id', $order->id)
            ->where('distributor_id', $distributor->id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => __('offers.already_submitted'),
            ], 409);
        }

        $offer = DB::transaction(function () use ($request, $user, $distributor, $order) {
            $offer = Offer::create([
                'order_id'                 => $order->id,
                'distributor_id'           => $distributor->id,
                'user_id'                  => $user->id,
                'delivery_cost'            => $request->delivery_cost,
                'estimated_delivery_time'  => $request->estimated_delivery_time,
                'estimated_delivery_date'  => $request->estimated_delivery_date,
                'notes'                    => $request->notes,
                'status'                   => 'submitted',
            ]);

            foreach ($request->items as $item) {
                OfferItem::create([
                    'offer_id'      => $offer->id,
                    'order_item_id' => $item['order_item_id'],
                    'product_id'    => $item['product_id'],
                    'unit_price'    => $item['unit_price'],
                    'quantity'      => $item['quantity'],
                    'is_available'  => $item['is_available'] ?? true,
                    'notes'         => $item['notes'] ?? null,
                ]);
            }

            // Recalculate totals
            $offer->calculateTotals();

            // Update order offer count and status
            $order->increment('offer_count');
            if ($order->status === 'published') {
                $order->update(['status' => 'receiving_offers']);
            }

            // Mark best offers
            Offer::markBestOffers($order->id);

            return $offer->load('items.product');
        });

        // Broadcast real-time event to shop owner
        broadcast(new OfferSubmitted($offer->fresh()->load(['order.shop', 'distributor', 'items.product'])))->toOthers();

        return response()->json([
            'success' => true,
            'message' => __('offers.submitted'),
            'data'    => $offer,
        ], 201);
    }

    /**
     * List distributor's submitted offers.
     */
    public function myOffers(Request $request): JsonResponse
    {
        $user = $request->user();
        $distributor = $user->distributor;

        if (!$distributor) {
            return response()->json(['success' => false, 'message' => 'No distributor profile'], 404);
        }

        $query = Offer::with(['order.items.product', 'order.shop', 'items.product'])
            ->where('distributor_id', $distributor->id);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $offers = $query->latest()->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data'    => $offers->items(),
            'meta'    => [
                'current_page' => $offers->currentPage(),
                'last_page'    => $offers->lastPage(),
                'per_page'     => $offers->perPage(),
                'total'        => $offers->total(),
            ],
        ]);
    }

    /**
     * Show offer details.
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $user = $request->user();
        $distributor = $user->distributor;

        if (!$distributor) {
            return response()->json(['success' => false, 'message' => 'No distributor profile'], 404);
        }

        $offer = Offer::with([
            'order.items.product',
            'order.shop',
            'distributor',
            'items.product',
            'items.orderItem',
        ])->where('distributor_id', $distributor->id)
          ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $offer,
        ]);
    }

    /**
     * Show order details for a distributor.
     */
    public function showOrder(int $id, Request $request): JsonResponse
    {
        $user = $request->user();
        $distributor = $user->distributor;

        if (!$distributor) {
            return response()->json(['success' => false, 'message' => 'No distributor profile'], 404);
        }

        $order = Order::with(['items.product', 'shop'])
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->findOrFail($id);

        // Check if distributor already submitted an offer
        $existingOffer = Offer::where('order_id', $order->id)
            ->where('distributor_id', $distributor->id)
            ->first();

        $data = $order->toArray();
        $data['has_submitted_offer'] = $existingOffer !== null;
        $data['my_offer_id'] = $existingOffer?->id;

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }
}
