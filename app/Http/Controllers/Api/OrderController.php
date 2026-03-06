<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Offer;
use App\Events\OrderCreated;
use App\Events\OfferAccepted;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * List shop owner's orders.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $shop = $user->shop;

        if (!$shop) {
            return response()->json(['success' => false, 'message' => 'No shop found'], 404);
        }

        $query = Order::with(['items.product', 'offers'])
            ->where('shop_id', $shop->id);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate($request->per_page ?? 15);

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
     * Create a new order (draft).
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'delivery_address'      => 'required|string',
            'delivery_latitude'     => 'required|numeric',
            'delivery_longitude'    => 'required|numeric',
            'preferred_delivery_time' => 'nullable|date',
            'notes'                 => 'nullable|string|max:1000',
            'items'                 => 'required|array|min:1',
            'items.*.product_id'    => 'required|exists:products,id',
            'items.*.quantity'      => 'required|numeric|min:0.1',
            'items.*.unit'          => 'required|string',
            'items.*.notes'         => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $shop = $user->shop;

        if (!$shop) {
            return response()->json(['success' => false, 'message' => 'No shop found'], 404);
        }

        $order = DB::transaction(function () use ($request, $user, $shop) {
            $order = Order::create([
                'shop_id'               => $shop->id,
                'user_id'               => $user->id,
                'delivery_address'      => $request->delivery_address,
                'delivery_latitude'     => $request->delivery_latitude,
                'delivery_longitude'    => $request->delivery_longitude,
                'preferred_delivery_time' => $request->preferred_delivery_time,
                'notes'                 => $request->notes,
                'status'                => 'draft',
            ]);

            foreach ($request->items as $item) {
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'unit'       => $item['unit'],
                    'notes'      => $item['notes'] ?? null,
                ]);
            }

            return $order->load('items.product');
        });

        return response()->json([
            'success' => true,
            'message' => __('orders.created'),
            'data'    => $order,
        ], 201);
    }

    /**
     * Show order details with items and offers.
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $user = $request->user();
        $shop = $user->shop;

        if (!$shop) {
            return response()->json(['success' => false, 'message' => 'No shop found'], 404);
        }

        $order = Order::with([
            'items.product',
            'offers.distributor',
            'offers.items.product',
            'shop',
            'delivery',
        ])->where('shop_id', $shop->id)->find($id);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $order,
        ]);
    }

    /**
     * Publish an order (make it visible to distributors).
     */
    public function publish(int $id, Request $request): JsonResponse
    {
        $user = $request->user();
        $order = Order::where('shop_id', $user->shop?->id)
            ->where('status', 'draft')
            ->findOrFail($id);

        $order->publish();

        // Broadcast real-time event to distributors
        broadcast(new OrderCreated($order->fresh()->load(['items.product', 'shop'])))->toOthers();

        return response()->json([
            'success' => true,
            'message' => __('orders.published'),
            'data'    => $order->fresh(),
        ]);
    }

    /**
     * Accept an offer on an order.
     */
    public function acceptOffer(int $orderId, int $offerId, Request $request): JsonResponse
    {
        $user = $request->user();
        $order = Order::where('shop_id', $user->shop?->id)
            ->whereIn('status', ['published', 'receiving_offers'])
            ->findOrFail($orderId);

        $offer = Offer::where('order_id', $orderId)
            ->where('status', 'submitted')
            ->findOrFail($offerId);

        $order->acceptOffer($offer);

        // Broadcast real-time event to distributor and shop
        broadcast(new OfferAccepted($offer->fresh()->load(['order.shop', 'distributor'])))->toOthers();

        return response()->json([
            'success' => true,
            'message' => __('orders.offer_accepted'),
            'data'    => $order->fresh()->load(['acceptedOffer.distributor', 'delivery']),
        ]);
    }

    /**
     * Get offers for a specific order (shop owner view).
     */
    public function offers(int $orderId, Request $request): JsonResponse
    {
        $user = $request->user();
        $order = Order::where('shop_id', $user->shop?->id)->findOrFail($orderId);

        $offers = Offer::with(['distributor', 'items.product'])
            ->where('order_id', $orderId)
            ->where('status', 'submitted')
            ->orderBy('total_amount')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $offers,
        ]);
    }

    /**
     * Update order (only if draft).
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $user = $request->user();
        $order = Order::where('shop_id', $user->shop?->id)
            ->where('status', 'draft')
            ->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'delivery_address'        => 'sometimes|string',
            'delivery_latitude'       => 'sometimes|numeric',
            'delivery_longitude'      => 'sometimes|numeric',
            'preferred_delivery_time' => 'nullable|date',
            'notes'                   => 'nullable|string|max:1000',
            'items'                   => 'sometimes|array|min:1',
            'items.*.product_id'      => 'required_with:items|exists:products,id',
            'items.*.quantity'        => 'required_with:items|numeric|min:0.1',
            'items.*.unit'            => 'required_with:items|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        DB::transaction(function () use ($order, $request) {
            $order->update($request->only([
                'delivery_address', 'delivery_latitude',
                'delivery_longitude', 'preferred_delivery_time', 'notes',
            ]));

            if ($request->has('items')) {
                $order->items()->delete();

                foreach ($request->items as $item) {
                    OrderItem::create([
                        'order_id'   => $order->id,
                        'product_id' => $item['product_id'],
                        'quantity'   => $item['quantity'],
                        'unit'       => $item['unit'],
                        'notes'      => $item['notes'] ?? null,
                    ]);
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => __('orders.updated'),
            'data'    => $order->fresh()->load('items.product'),
        ]);
    }

    /**
     * Cancel order.
     */
    public function cancel(int $id, Request $request): JsonResponse
    {
        $user = $request->user();
        $order = Order::where('shop_id', $user->shop?->id)
            ->whereIn('status', ['draft', 'published', 'receiving_offers'])
            ->findOrFail($id);

        DB::transaction(function () use ($order) {
            $order->update(['status' => 'cancelled']);

            // Reject all pending offers
            $order->offers()->where('status', 'submitted')->update(['status' => 'rejected']);
        });

        return response()->json([
            'success' => true,
            'message' => __('orders.cancelled'),
        ]);
    }
}
