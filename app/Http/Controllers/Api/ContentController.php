<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\District;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Slider;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function sliders()
    {
        $sliders = Slider::active()->get()->map(fn ($s) => [
            'id'        => $s->id,
            'image_url' => $s->image_url,
            'title'     => $s->title,
            'link'      => $s->link,
        ]);

        return response()->json(['success' => true, 'data' => $sliders]);
    }

    public function categories()
    {
        $categories = Category::active()->withCount('products')
            ->get()
            ->map(fn ($c) => [
                'id'            => $c->id,
                'name'          => $c->name,
                'image_url'     => $c->image_url,
                'products_count'=> $c->products_count,
            ]);

        return response()->json(['success' => true, 'data' => $categories]);
    }

    public function products(Request $request)
    {
        $products = Product::active()->available()
            ->with(['firstImage', 'category'])
            ->when($request->category_id, fn ($q) => $q->where('category_id', $request->category_id))
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => $products->map($this->formatProduct(...)),
            'meta'    => [
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
                'total'        => $products->total(),
            ],
        ]);
    }

    public function product(int $id)
    {
        $product = Product::active()->available()
            ->with(['images', 'category'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => [
                ...$this->formatProduct($product),
                'description' => $product->description,
                'size'        => $product->size,
                'images'      => $product->images->map(fn ($img) => [
                    'id'  => $img->id,
                    'url' => $img->url,
                ]),
                'category' => [
                    'id'   => $product->category->id,
                    'name' => $product->category->name,
                ],
            ],
        ]);
    }

    public function districts()
    {
        $districts = District::active()->get(['id', 'name']);
        return response()->json(['success' => true, 'data' => $districts]);
    }

    public function privacyPolicy()
    {
        return response()->json([
            'success' => true,
            'data'    => Setting::get('privacy_policy', ''),
        ]);
    }

    // ── Helpers ─────────────────────────────────────────────────
    private function formatProduct(Product $p): array
    {
        return [
            'id'             => $p->id,
            'name'           => $p->name,
            'price'          => $p->price,
            'sale_price'     => $p->sale_price,
            'is_on_sale'     => $p->is_on_sale,
            'effective_price'=> $p->effective_price,
            'is_available'   => $p->is_available,
            'thumbnail'      => $p->firstImage?->url,
        ];
    }
}
