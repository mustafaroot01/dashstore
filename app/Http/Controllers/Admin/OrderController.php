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
        $orders = Order::with(['user', 'district'])
            ->when($request->status,  fn ($q) => $q->where('status', $request->status))
            ->when($request->district_id, fn ($q) => $q->where('district_id', $request->district_id))
            ->when($request->date,    fn ($q) => $q->whereDate('created_at', $request->date))
            ->when($request->search,  fn ($q) => $q->where(function ($q2) use ($request) {
                $q2->where('invoice_number', 'like', "%{$request->search}%")
                   ->orWhereHas('user', fn ($u) =>
                        $u->where('first_name', 'like', "%{$request->search}%")
                          ->orWhere('last_name', 'like', "%{$request->search}%")
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
        $order->load(['user', 'district', 'coupon', 'items.product.firstImage']);
        $settings = Setting::getAllKeyed();

        return Inertia::render('Orders/Show', [
            'order'    => $order->append('status_label'),
            'settings' => $settings,
            'statuses' => Order::$statuses,
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
}
