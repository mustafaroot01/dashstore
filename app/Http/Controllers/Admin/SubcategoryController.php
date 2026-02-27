<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

use App\Traits\HandlesImageUploads;

class SubcategoryController extends Controller
{
    use HandlesImageUploads;
    public function index(Request $request, Category $category): Response
    {
        $subcategories = $category->subcategories()
            ->withCount('products')
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->latest()
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('Subcategories/Index', [
            'category'      => $category,
            'subcategories' => $subcategories,
            'filters'       => $request->only('search'),
        ]);
    }

    public function store(Request $request, Category $category)
    {
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:100'],
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ], [
            'name.required'  => 'اسم الصنف الفرعي مطلوب',
            'image.required' => 'صورة الصنف مطلوبة',
        ]);

        $data['image'] = $this->optimizeAndStore($request->file('image'), "categories/{$category->id}/subcategories");

        $category->subcategories()->create($data);

        return back()->with('success', 'تم إضافة الصنف الفرعي بنجاح');
    }

    public function update(Request $request, Category $category, Subcategory $subcategory)
    {
        // Ensure subcategory belongs to this category
        if ($subcategory->category_id !== $category->id) {
            abort(404);
        }

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:100'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            if ($subcategory->image) {
                Storage::disk('public')->delete($subcategory->image);
            }
            $data['image'] = $this->optimizeAndStore($request->file('image'), "categories/{$category->id}/subcategories");
        }

        $subcategory->update($data);

        return back()->with('success', 'تم تعديل الصنف الفرعي بنجاح');
    }

    public function destroy(Category $category, Subcategory $subcategory)
    {
        if ($subcategory->category_id !== $category->id) abort(404);

        try {
            $subcategory->delete();
            if ($subcategory->image) {
                Storage::disk('public')->delete($subcategory->image);
            }
            return back()->with('success', 'تم حذف الصنف الفرعي بنجاح');
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->with('error', 'لا يمكن حذف هذا الصنف لأنه يحتوي على منتجات. يمكنك إخفاؤه بدلاً من ذلك.');
        }
    }

    public function toggleActive(Category $category, Subcategory $subcategory)
    {
        if ($subcategory->category_id !== $category->id) abort(404);

        $subcategory->update(['is_active' => ! $subcategory->is_active]);
        return back()->with('success', $subcategory->is_active ? 'الصنف الفرعي نشط الآن' : 'الصنف الفرعي مخفي');
    }
}
