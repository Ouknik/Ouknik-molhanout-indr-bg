<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * List all active products with optional filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with('category')
            ->active()
            ->fromCatalog();

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $locale = app()->getLocale();
            $query->where(function ($q) use ($search, $locale) {
                $q->where("name_{$locale}", 'like', "%{$search}%")
                  ->orWhere('name_fr', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('sort_order')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data'    => $products->items(),
            'meta'    => [
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
                'per_page'     => $products->perPage(),
                'total'        => $products->total(),
            ],
        ]);
    }

    /**
     * Get product details.
     */
    public function show(int $id): JsonResponse
    {
        $product = Product::with('category')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $product,
        ]);
    }

    /**
     * List all categories.
     */
    public function categories(): JsonResponse
    {
        $categories = Category::where('is_active', true)
            ->withCount(['products' => fn($q) => $q->active()])
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $categories,
        ]);
    }

    /**
     * Get frequently ordered products for shop owner (AI suggestion).
     */
    public function frequentProducts(Request $request): JsonResponse
    {
        $shopId = $request->user()->shop?->id;

        if (!$shopId) {
            return response()->json(['success' => false, 'message' => 'No shop found'], 404);
        }

        $products = Product::select('products.*')
            ->selectRaw('COUNT(order_items.id) as order_count')
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.shop_id', $shopId)
            ->groupBy('products.id')
            ->orderByDesc('order_count')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $products,
        ]);
    }
}
