<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CouponController extends Controller
{
    public function index()
    {
        return Inertia::render('Coupons/Index', [
            'coupons' => Coupon::latest()->paginate(50),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'        => ['nullable', 'string', 'max:50', 'unique:coupons'],
            'type'        => ['required', 'in:percent,fixed'],
            'value'       => ['required', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'expires_at'  => ['nullable', 'date', 'after:today'],
        ], [
            'code.unique'       => 'هذا الكود مستخدم مسبقاً',
            'value.required'    => 'قيمة الخصم مطلوبة',
            'expires_at.after'  => 'تاريخ الانتهاء يجب أن يكون في المستقبل',
        ]);

        $data['code']      = $data['code'] ?? strtoupper(Str::random(8));
        $data['is_active'] = true;

        Coupon::create($data);
        return back()->with('success', 'تم إنشاء الكوبون');
    }

    public function update(Request $request, Coupon $coupon)
    {
        $data = $request->validate([
            'code'        => ['required', 'string', 'max:50', "unique:coupons,code,{$coupon->id}"],
            'type'        => ['required', 'in:percent,fixed'],
            'value'       => ['required', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'expires_at'  => ['nullable', 'date'],
        ]);

        $coupon->update($data);
        return back()->with('success', 'تم تعديل الكوبون');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return back()->with('success', 'تم حذف الكوبون');
    }

    public function toggleActive(Coupon $coupon)
    {
        $coupon->update(['is_active' => ! $coupon->is_active]);
        return back();
    }
}
