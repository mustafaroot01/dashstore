<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with(['user', 'governorate', 'district.governorate'])
            ->when($request->status,  fn ($q) => $q->where('status', $request->status))
            ->when($request->district_id, fn ($q) => $q->where('district_id', $request->district_id))
            ->when($request->date,    fn ($q) => $q->whereDate('created_at', $request->date))
            ->when($request->search,  fn ($q) => $q->where(function ($q2) use ($request) {
                $q2->where('invoice_number', 'like', "%{$request->search}%")
                   ->orWhere('phone', 'like', "%{$request->search}%")
                   ->orWhereHas('user', fn ($u) =>
                        $u->where('first_name', 'like', "%{$request->search}%")
                          ->orWhere('last_name', 'like', "%{$request->search}%")
                          ->orWhere('phone', 'like', "%{$request->search}%")
                    );
            }))
            ->latest()
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('Orders/Index', [
            'orders'   => $orders,
            'statuses' => Order::$statuses,
            'filters'  => $request->only(['status', 'district_id', 'date', 'search']),
        ]);
    }

    public function show(Order $order)
    {
        $order->load(['user', 'governorate', 'district.governorate', 'coupon', 'items.product.firstImage']);
        $settings = Setting::getAllKeyed();
        $governorates = \App\Models\Governorate::with('districts')->active()->get();

        return Inertia::render('Orders/Show', [
            'order'        => $order->append('status_label'),
            'settings'     => $settings,
            'statuses'     => Order::$statuses,
            'governorates' => $governorates,
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => ['required', 'in:' . implode(',', array_keys(Order::$statuses))],
        ]);

        $order->update(['status' => $request->status]);

        return back()->with('success', 'تم تغيير حالة الطلب');
    }

    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'governorate_id' => ['required', 'exists:governorates,id'],
            'district_id'    => ['nullable', 'exists:districts,id'],
            'delivery_point' => ['required', 'string', 'max:255'],
            'phone'          => ['required', 'string', 'max:20'],
            'notes'          => ['nullable', 'string', 'max:1000'],
        ]);

        $order->update($data);

        return back()->with('success', 'تم تحديث بيانات الطلب بنجاح');
    }
}
