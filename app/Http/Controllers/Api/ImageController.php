<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageController extends Controller
{
    /**
     * Upload product image (Admin only)
     */
    public function uploadProductImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'product_id' => 'required|exists:products,id',
        ]);

        $product = Product::findOrFail($request->product_id);

        // Delete old image if exists
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        // Generate unique filename
        $filename = 'products/' . Str::uuid() . '.' . $request->image->getClientOriginalExtension();

        // Store image
        $path = $request->image->storeAs('', $filename, 'public');

        // Update product
        $product->update(['image' => $path]);

        return response()->json([
            'success' => true,
            'message' => __('Image uploaded successfully'),
            'data' => [
                'image_url' => Storage::disk('public')->url($path),
                'image_path' => $path,
            ],
        ]);
    }

    /**
     * Upload general image
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'folder' => 'nullable|string|max:50',
        ]);

        $folder = $request->folder ?? 'uploads';
        $filename = $folder . '/' . Str::uuid() . '.' . $request->image->getClientOriginalExtension();

        $path = $request->image->storeAs('', $filename, 'public');

        return response()->json([
            'success' => true,
            'message' => __('Image uploaded successfully'),
            'data' => [
                'image_url' => Storage::disk('public')->url($path),
                'image_path' => $path,
            ],
        ]);
    }

    /**
     * Delete image
     */
    public function delete(Request $request): JsonResponse
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        if (Storage::disk('public')->exists($request->path)) {
            Storage::disk('public')->delete($request->path);
            return response()->json([
                'success' => true,
                'message' => __('Image deleted successfully'),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('Image not found'),
        ], 404);
    }
}
