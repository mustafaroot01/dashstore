<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Product::with(['category', 'firstImage'])
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->category_id, fn ($q) => $q->where('category_id', $request->category_id));

        $products   = $query->latest()->paginate(50)->withQueryString();
        $categories = Category::active()->get(['id', 'name']);

        return Inertia::render('Products/Index', [
            'products'   => $products,
            'categories' => $categories,
            'filters'    => $request->only(['search', 'category_id']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Products/Form', [
            'categories' => Category::active()->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id'  => ['required', 'exists:categories,id'],
            'name'         => ['required', 'string', 'max:150'],
            'description'  => ['required', 'string'],
            'size'         => ['nullable', 'string', 'max:50'],
            'price'        => ['required', 'numeric', 'min:0'],
            'sale_price'   => ['nullable', 'numeric', 'min:0'],
            'is_on_sale'   => ['boolean'],
            'cost_price'   => ['required', 'numeric', 'min:0'],
            'is_active'    => ['boolean'],
            'is_available' => ['boolean'],
            'images'       => ['nullable', 'array', 'max:5'],
            'images.*'     => ['image', 'mimes:jpeg,png,jpg,webp', 'max:3072'],
        ]);

        $product = Product::create($data);
        $this->handleImages($product, $request);

        return redirect()->route('admin.products.index')
            ->with('success', 'تم إضافة المنتج بنجاح');
    }

    public function edit(Product $product): Response
    {
        $product->load('images', 'category');

        return Inertia::render('Products/Form', [
            'product'    => $product,
            'categories' => Category::active()->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'category_id'  => ['required', 'exists:categories,id'],
            'name'         => ['required', 'string', 'max:150'],
            'description'  => ['required', 'string'],
            'size'         => ['nullable', 'string', 'max:50'],
            'price'        => ['required', 'numeric', 'min:0'],
            'sale_price'   => ['nullable', 'numeric', 'min:0'],
            'is_on_sale'   => ['boolean'],
            'cost_price'   => ['required', 'numeric', 'min:0'],
            'is_active'    => ['boolean'],
            'is_available' => ['boolean'],
            'images'       => ['nullable', 'array'],
            'images.*'     => ['image', 'mimes:jpeg,png,jpg,webp', 'max:3072'],
            'deleted_images' => ['nullable', 'array'],
            'deleted_images.*' => ['integer'],
        ]);

        $product->update($data);

        // Delete removed images
        if (! empty($data['deleted_images'])) {
            $toDelete = ProductImage::whereIn('id', $data['deleted_images'])
                ->where('product_id', $product->id)
                ->get();
            foreach ($toDelete as $img) {
                Storage::disk('public')->delete($img->image_path);
                $img->delete();
            }
        }

        // Add new images (respecting max 5 limit)
        $this->handleImages($product, $request);

        return redirect()->route('admin.products.index')
            ->with('success', 'تم تعديل المنتج بنجاح');
    }

    public function destroy(Product $product)
    {
        // Store paths before deletion
        $paths = $product->images->pluck('image_path')->toArray();
        
        try {
            $product->delete();
            // Delete all product images from storage only if DB delete succeeds
            foreach ($paths as $path) {
                Storage::disk('public')->delete($path);
            }
            return back()->with('success', 'تم حذف المنتج بنجاح');
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->with('error', 'لا يمكن حذف هذا المنتج لارتباطه بطلبات سابقة. يمكنك تغيير حالته إلى غير نشط بدلاً من مسحه.');
        }
    }

    public function toggleAvailability(Product $product)
    {
        $product->update(['is_available' => ! $product->is_available]);
        return back()->with('success', $product->is_available ? 'المنتج متوفر الآن' : 'المنتج غير متوفر');
    }

    public function toggleActive(Product $product)
    {
        $product->update(['is_active' => ! $product->is_active]);
        return back()->with('success', $product->is_active ? 'المنتج نشط الآن' : 'المنتج مخفي الآن');
    }

    // ── Helpers ─────────────────────────────────────────
    private function handleImages(Product $product, Request $request): void
    {
        if (! $request->hasFile('images')) return;

        $existing = $product->images()->count();
        $allowed  = 5 - $existing;

        if ($allowed <= 0) return;

        $order = $existing;
        foreach (array_slice($request->file('images'), 0, $allowed) as $file) {
            $path = $file->store("products/{$product->id}", 'public');
            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => $path,
                'order'      => $order++,
            ]);
        }
    }
}
