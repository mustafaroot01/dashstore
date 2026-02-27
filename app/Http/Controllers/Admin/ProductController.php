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

use App\Traits\HandlesImageUploads;

class ProductController extends Controller
{
    use HandlesImageUploads;
    public function index(Request $request): Response
    {
        $query = Product::with(['category', 'subcategory', 'firstImage'])
            ->when($request->search, function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('sku', 'like', "%{$request->search}%");
            })
            ->when($request->category_id, fn ($q) => $q->where('category_id', $request->category_id))
            ->when($request->subcategory_id, fn ($q) => $q->where('subcategory_id', $request->subcategory_id))
            ->when($request->availability === 'out', fn ($q) => $q->where('is_available', false))
            ->when($request->availability === 'in', fn ($q) => $q->where('is_available', true));

        $products   = $query->latest()->paginate(50)->withQueryString();
        $categories = Category::with('subcategories')->active()->get(['id', 'name']);

        // Count out of stock
        $outOfStockCount = Product::where('is_available', false)->where('is_active', true)->count();

        return Inertia::render('Products/Index', [
            'products'        => $products,
            'categories'      => $categories,
            'filters'         => $request->only(['search', 'category_id', 'subcategory_id', 'availability']),
            'outOfStockCount' => $outOfStockCount,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Products/Form', [
            'categories' => Category::with('subcategories')->active()->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id'  => ['required', 'exists:categories,id'],
            'subcategory_id' => ['nullable', 'exists:subcategories,id'],
            'name'         => ['required', 'string', 'max:150'],
            'sku'          => ['nullable', 'string', 'max:100', 'unique:products,sku'],
            'description'  => ['required', 'string'],
            'price'        => ['required', 'numeric', 'min:0'],
            'sale_price'   => ['nullable', 'numeric', 'min:0'],
            'is_on_sale'   => ['boolean'],
            'cost_price'   => ['required', 'numeric', 'min:0'],
            'is_active'    => ['boolean'],
            'is_available' => ['boolean'],
            'variants'     => ['required', 'array', 'min:1'],
            'variants.*.color' => ['nullable', 'string', 'max:50'],
            'variants.*.size'  => ['nullable', 'string', 'max:50'],
            'variants.*.stock' => ['required', 'integer', 'min:0'],
            'images'       => ['nullable', 'array', 'max:5'],
            'images.*'     => ['image', 'mimes:jpeg,png,jpg,webp', 'max:3072'],
        ]);

        $product = Product::create($data);

        foreach ($data['variants'] as $v) {
            $product->variants()->create([
                'color' => $v['color'] ?? null,
                'size'  => $v['size'] ?? null,
                'stock' => $v['stock'],
            ]);
        }

        $this->handleImages($product, $request);

        // --- Low Stock Notification Logic ---
        $lowStockWarnings = [];
        $lowStockThreshold = (int) \App\Models\Setting::get('low_stock_threshold', 3);
        
        foreach ($product->variants as $variant) {
            if ($variant->stock <= 0) {
                $colorSize = collect([$variant->color, $variant->size])->filter()->implode(' - ');
                $variantSuffix = $colorSize ? " ({$colorSize})" : "";
                $lowStockWarnings[] = "❌ {$product->name}{$variantSuffix} (تم الإضافة برصيد صفر!)";
            } elseif ($variant->stock <= $lowStockThreshold) {
                $colorSize = collect([$variant->color, $variant->size])->filter()->implode(' - ');
                $variantSuffix = $colorSize ? " ({$colorSize})" : "";
                $lowStockWarnings[] = "🔸 {$product->name}{$variantSuffix} (رصيد أولي قليل: {$variant->stock} قطع)";
            }
        }

        if (!empty($lowStockWarnings)) {
            $botToken = \App\Models\Setting::get('telegram_bot_token');
            $chatId   = \App\Models\Setting::get('telegram_chat_id');
            if ($botToken && $chatId) {
                $dashboardName = \App\Models\Setting::get('dashboard_name', 'أمواج ديالى');
                $message = "⚠️ *تنبيه من لوحة تحكم {$dashboardName}*\nتم إضافة منتج جديد برصيد منخفض:\n\n";
                $message .= implode("\n", $lowStockWarnings);
                
                try {
                    \Illuminate\Support\Facades\Http::timeout(5)->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                        'chat_id'    => $chatId,
                        'text'       => $message,
                        'parse_mode' => 'Markdown',
                    ]);
                } catch (\Exception $e) {
                    // Ignore telegram failures
                }
            }
        }
        // --- End Notification Logic ---

        return redirect()->route('admin.products.index')
            ->with('success', 'تم إضافة المنتج بنجاح');
    }

    public function edit(Product $product): Response
    {
        $product->load('images', 'category', 'variants', 'subcategory');

        return Inertia::render('Products/Form', [
            'product'    => $product,
            'categories' => Category::with('subcategories')->active()->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'category_id'  => ['required', 'exists:categories,id'],
            'subcategory_id' => ['nullable', 'exists:subcategories,id'],
            'name'         => ['required', 'string', 'max:150'],
            'sku'          => ['nullable', 'string', 'max:100', 'unique:products,sku,' . $product->id],
            'description'  => ['required', 'string'],
            'price'        => ['required', 'numeric', 'min:0'],
            'sale_price'   => ['nullable', 'numeric', 'min:0'],
            'is_on_sale'   => ['boolean'],
            'cost_price'   => ['required', 'numeric', 'min:0'],
            'is_active'    => ['boolean'],
            'is_available' => ['boolean'],
            'variants'     => ['required', 'array', 'min:1'],
            'variants.*.id'    => ['nullable', 'integer'],
            'variants.*.color' => ['nullable', 'string', 'max:50'],
            'variants.*.size'  => ['nullable', 'string', 'max:50'],
            'variants.*.stock' => ['required', 'integer', 'min:0'],
            'images'       => ['nullable', 'array'],
            'images.*'     => ['image', 'mimes:jpeg,png,jpg,webp', 'max:3072'],
            'deleted_images' => ['nullable', 'array'],
            'deleted_images.*' => ['integer'],
        ]);

        $product->update($data);

        // Sync Variants
        $incomingIds = [];
        foreach ($data['variants'] as $v) {
            if (isset($v['id'])) {
                $variant = $product->variants()->find($v['id']);
                if ($variant) {
                    $variant->update([
                        'color' => $v['color'] ?? null,
                        'size'  => $v['size'] ?? null,
                        'stock' => $v['stock'],
                    ]);
                    $incomingIds[] = $variant->id;
                    continue;
                }
            }
            // Create new if no ID or ID not found
            $newVariant = $product->variants()->create([
                'color' => $v['color'] ?? null,
                'size'  => $v['size'] ?? null,
                'stock' => $v['stock'],
            ]);
            $incomingIds[] = $newVariant->id;
        }

        // --- Low Stock Notification Logic ---
        $lowStockWarnings = [];
        $lowStockThreshold = (int) \App\Models\Setting::get('low_stock_threshold', 3);
        
        foreach ($product->variants as $variant) {
            if ($variant->stock <= 0) {
                $colorSize = collect([$variant->color, $variant->size])->filter()->implode(' - ');
                $variantSuffix = $colorSize ? " ({$colorSize})" : "";
                $lowStockWarnings[] = "❌ {$product->name}{$variantSuffix} (نفذت الكمية تماماً!)";
            } elseif ($variant->stock <= $lowStockThreshold) {
                $colorSize = collect([$variant->color, $variant->size])->filter()->implode(' - ');
                $variantSuffix = $colorSize ? " ({$colorSize})" : "";
                $lowStockWarnings[] = "🔸 {$product->name}{$variantSuffix} (متبقي {$variant->stock} قطع فقط)";
            }
        }

        if (!empty($lowStockWarnings)) {
            $botToken = \App\Models\Setting::get('telegram_bot_token');
            $chatId   = \App\Models\Setting::get('telegram_chat_id');
            if ($botToken && $chatId) {
                $dashboardName = \App\Models\Setting::get('dashboard_name', 'أمواج ديالى');
                $message = "⚠️ *تنبيه من لوحة تحكم {$dashboardName}*\nتم تعديل مخزون المنتج واكتشاف نقص:\n\n";
                $message .= implode("\n", $lowStockWarnings);
                
                try {
                    \Illuminate\Support\Facades\Http::timeout(5)->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                        'chat_id'    => $chatId,
                        'text'       => $message,
                        'parse_mode' => 'Markdown',
                    ]);
                } catch (\Exception $e) {
                    // Ignore telegram failures on product update
                }
            }
        }
        // --- End Notification Logic ---

        // Delete removed variants
        $product->variants()->whereNotIn('id', $incomingIds)->delete();

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
            $path = $this->optimizeAndStore($file, "products/{$product->id}");
            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => $path,
                'order'      => $order++,
            ]);
        }
    }
}
