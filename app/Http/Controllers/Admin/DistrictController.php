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
            'governorates' => \App\Models\Governorate::with(['districts' => function ($q) {
                $q->withCount('orders');
            }])->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:districts'],
            'governorate_id' => ['required', 'exists:governorates,id']
        ]);
        District::create([
            'name' => $request->name,
            'governorate_id' => $request->governorate_id
        ]);
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
