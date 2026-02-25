<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\District;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DistrictController extends Controller
{
    public function index()
    {
        return Inertia::render('Districts/Index', [
            'districts' => District::withCount('orders')->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => ['required', 'string', 'max:100', 'unique:districts']]);
        District::create(['name' => $request->name]);
        return back()->with('success', 'تم إضافة القضاء');
    }

    public function destroy(District $district)
    {
        try {
            $district->delete();
            return back()->with('success', 'تم حذف القضاء بنجاح');
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->with('error', 'لا يمكن حذف هذا القضاء لأنه مرتبط بطلبات سابقة. يمكنك إيقافه بدلاً من ذلك.');
        }
    }

    public function toggleActive(District $district)
    {
        $district->update(['is_active' => ! $district->is_active]);
        return back();
    }
}
