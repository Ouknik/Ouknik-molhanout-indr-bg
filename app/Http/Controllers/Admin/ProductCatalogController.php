<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductCatalogController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')->fromCatalog();

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name_fr', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%");
            });
        }

        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->orderBy('sort_order')->paginate(20);
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_ar'         => 'required|string|max:255',
            'name_fr'         => 'required|string|max:255',
            'name_en'         => 'required|string|max:255',
            'category_id'     => 'required|exists:categories,id',
            'description_ar'  => 'nullable|string',
            'description_fr'  => 'nullable|string',
            'description_en'  => 'nullable|string',
            'unit'            => 'required|in:box,kg,pack,bottle,piece,bag,liter',
            'reference_price' => 'nullable|numeric|min:0',
            'barcode'         => 'nullable|string',
            'image'           => 'nullable|image|max:2048',
            'is_active'       => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name_en']) . '-' . Str::random(4);
        $validated['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        Product::create($validated);

        return redirect()->route('admin.products.index')
            ->with('success', __('Product created successfully.'));
    }

    public function edit(int $id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, int $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name_ar'         => 'required|string|max:255',
            'name_fr'         => 'required|string|max:255',
            'name_en'         => 'required|string|max:255',
            'category_id'     => 'required|exists:categories,id',
            'description_ar'  => 'nullable|string',
            'description_fr'  => 'nullable|string',
            'description_en'  => 'nullable|string',
            'unit'            => 'required|in:box,kg,pack,bottle,piece,bag,liter',
            'reference_price' => 'nullable|numeric|min:0',
            'barcode'         => 'nullable|string',
            'image'           => 'nullable|image|max:2048',
            'is_active'       => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validated);

        return redirect()->route('admin.products.index')
            ->with('success', __('Product updated successfully.'));
    }

    public function toggleStatus(int $id)
    {
        $product = Product::findOrFail($id);
        $product->update(['is_active' => !$product->is_active]);

        return back()->with('success', __('Product status updated.'));
    }

    // ─── CATEGORIES ───

    public function categories()
    {
        $categories = Category::withCount('products')->orderBy('sort_order')->paginate(20);
        return view('admin.categories.index', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_fr' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'image'   => 'nullable|image|max:2048',
        ]);

        $validated['slug'] = Str::slug($validated['name_en']);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('categories', 'public');
        }

        Category::create($validated);

        return back()->with('success', __('Category created.'));
    }

    public function updateCategory(Request $request, int $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_fr' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'image'   => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($validated);

        return back()->with('success', __('Category updated.'));
    }

    public function destroyCategory(int $id)
    {
        $category = Category::findOrFail($id);

        if ($category->products()->exists()) {
            return back()->with('error', __('Cannot delete category with products.'));
        }

        $category->delete();

        return back()->with('success', __('Category deleted.'));
    }
}
