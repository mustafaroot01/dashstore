<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

use App\Traits\HandlesImageUploads;

class SliderController extends Controller
{
    use HandlesImageUploads;
    public function index()
    {
        return Inertia::render('Sliders/Index', [
            'sliders'    => Slider::with('category', 'product')->orderBy('order')->get(),
            'categories' => Category::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'products'   => Product::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'image'       => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:3072'],
            'title'       => ['nullable', 'string', 'max:200'],
            'link_type'   => ['nullable', 'in:category,product,external'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'product_id'  => ['nullable', 'exists:products,id'],
            'link'        => ['nullable', 'string', 'max:500'],
        ]);

        $data['image'] = $this->optimizeAndStore($request->file('image'), 'sliders');
        $data['order'] = Slider::max('order') + 1;

        // Clear irrelevant fields
        if ($data['link_type'] === 'category') { $data['link'] = null; $data['product_id'] = null; }
        if ($data['link_type'] === 'product')  { $data['link'] = null; $data['category_id'] = null; }
        if ($data['link_type'] === 'external') { $data['category_id'] = null; $data['product_id'] = null; }
        if (! $data['link_type'])              { $data['link'] = null; $data['category_id'] = null; $data['product_id'] = null; }

        Slider::create($data);
        return back()->with('success', 'تم إضافة السلايدة');
    }

    public function update(Request $request, Slider $slider)
    {
        $data = $request->validate([
            'image'       => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:3072'],
            'title'       => ['nullable', 'string', 'max:200'],
            'link_type'   => ['nullable', 'in:category,product,external'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'product_id'  => ['nullable', 'exists:products,id'],
            'link'        => ['nullable', 'string', 'max:500'],
        ]);

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($slider->image);
            $data['image'] = $this->optimizeAndStore($request->file('image'), 'sliders');
        } else {
            unset($data['image']);
        }

        if ($data['link_type'] === 'category') { $data['link'] = null; $data['product_id'] = null; }
        if ($data['link_type'] === 'product')  { $data['link'] = null; $data['category_id'] = null; }
        if ($data['link_type'] === 'external') { $data['category_id'] = null; $data['product_id'] = null; }
        if (! ($data['link_type'] ?? null))    { $data['link'] = null; $data['category_id'] = null; $data['product_id'] = null; }

        $slider->update($data);
        return back()->with('success', 'تم تعديل السلايدة');
    }

    public function destroy(Slider $slider)
    {
        Storage::disk('public')->delete($slider->image);
        $slider->delete();
        return back()->with('success', 'تم حذف السلايدة');
    }

    public function toggleActive(Slider $slider)
    {
        $slider->update(['is_active' => ! $slider->is_active]);
        return back();
    }

    public function reorder(Request $request)
    {
        $request->validate(['ids' => ['required', 'array']]);
        foreach ($request->ids as $index => $id) {
            Slider::where('id', $id)->update(['order' => $index]);
        }
        return back()->with('success', 'تم حفظ الترتيب');
    }
}
