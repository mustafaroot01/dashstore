<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Governorate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GovernorateController extends Controller
{
    public function index()
    {
        return Inertia::render('Governorates/Index', [
            'governorates' => Governorate::withCount('districts')->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => ['required', 'string', 'max:100', 'unique:governorates']]);
        Governorate::create(['name' => $request->name]);
        return back()->with('success', 'تم إضافة المحافظة');
    }

    public function destroy(Governorate $governorate)
    {
        try {
            $governorate->delete();
            return back()->with('success', 'تم حذف المحافظة بنجاح');
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->with('error', 'لا يمكن حذف هذه المحافظة لأنها مرتبطة بأقضية. يمكنك إيقافها بدلاً من ذلك.');
        }
    }

    public function toggleActive(Governorate $governorate)
    {
        $governorate->update(['is_active' => ! $governorate->is_active]);
        return back();
    }
}
