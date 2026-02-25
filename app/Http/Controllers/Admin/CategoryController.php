<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::withCount('products')
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->latest()
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
            'filters'    => $request->only('search'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:100'],
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ], [
            'name.required'  => 'اسم القسم مطلوب',
            'image.required' => 'صورة القسم مطلوبة',
        ]);

        $data['image'] = $request->file('image')->store('categories', 'public');

        Category::create($data);

        return back()->with('success', 'تم إضافة القسم بنجاح');
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:100'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($category->image);
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($data);

        return back()->with('success', 'تم تعديل القسم بنجاح');
    }

    public function destroy(Category $category)
    {
        try {
            $category->delete();
            Storage::disk('public')->delete($category->image);
            return back()->with('success', 'تم حذف القسم بنجاح');
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->with('error', 'لا يمكن حذف هذا القسم لأنه يحتوي على منتجات. يمكنك إخفاؤه بدلاً من ذلك.');
        }
    }

    public function toggleActive(Category $category)
    {
        $category->update(['is_active' => ! $category->is_active]);
        return back()->with('success', $category->is_active ? 'القسم نشط الآن' : 'القسم مخفي');
    }
}
