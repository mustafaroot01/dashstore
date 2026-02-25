<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function validateCoupon(Request $request)
    {
        $request->validate([
            'code'     => ['required'],
            'subtotal' => ['required', 'numeric'],
        ]);

        $coupon = Coupon::where('code', $request->code)->first();

        if (! $coupon || ! $coupon->isValid()) {
            return response()->json(['success' => false, 'message' => 'الكوبون غير صالح أو منتهي الصلاحية'], 422);
        }

        $discount = $coupon->calculateDiscount($request->subtotal);

        return response()->json([
            'success'  => true,
            'coupon'   => [
                'id'       => $coupon->id,
                'code'     => $coupon->code,
                'type'     => $coupon->type,
                'value'    => $coupon->value,
                'discount' => $discount,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'district_id'    => ['required', 'exists:districts,id'],
            'delivery_point' => ['required', 'string'],
            'phone'          => ['required', 'string'],
            'coupon_id'      => ['nullable', 'exists:coupons,id'],
            'notes'          => ['nullable', 'string'],
            'items'          => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity'   => ['required', 'integer', 'min:1'],
        ]);

        return DB::transaction(function () use ($data, $request) {

            $subtotal       = 0;
            $discountAmount = 0;
            $orderItemsData = [];

            // Calculate subtotal and build items
            foreach ($data['items'] as $item) {
                $product  = Product::findOrFail($item['product_id']);
                $unitPrice = $product->effective_price;
                $subtotal += $unitPrice * $item['quantity'];
                $orderItemsData[] = [
                    'product_id' => $product->id,
                    'quantity'   => $item['quantity'],
                    'price'      => $unitPrice,
                    'cost_price' => $product->cost_price, // snapshot
                ];
            }

            // Apply coupon
            if (! empty($data['coupon_id'])) {
                $coupon = Coupon::find($data['coupon_id']);
                if ($coupon && $coupon->isValid()) {
                    $discountAmount = $coupon->calculateDiscount($subtotal);
                    $coupon->increment('used_count');
                }
            }

            $order = Order::create([
                'user_id'         => $request->user()->id,
                'district_id'     => $data['district_id'],
                'delivery_point'  => $data['delivery_point'],
                'phone'           => $data['phone'],
                'coupon_id'       => $data['coupon_id'] ?? null,
                'discount_amount' => $discountAmount,
                'total_price'     => max(0, $subtotal - $discountAmount),
                'notes'           => $data['notes'] ?? null,
            ]);

            foreach ($orderItemsData as $item) {
                OrderItem::create(['order_id' => $order->id, ...$item]);
            }

            $order->load(['items.product', 'district']);

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء الطلب بنجاح',
                'data'    => [
                    'id'             => $order->id,
                    'invoice_number' => $order->invoice_number,
                    'total_price'    => $order->total_price,
                    'status'         => $order->status,
                    'status_label'   => $order->status_label,
                ],
            ], 201);
        });
    }

    public function index(Request $request)
    {
        $orders = $request->user()->orders()
            ->with(['district', 'items'])
            ->latest()
            ->paginate(20);

        return response()->json(['success' => true, 'data' => $orders]);
    }

    public function show(Request $request, int $id)
    {
        $order = $request->user()->orders()
            ->with(['district', 'coupon', 'items.product.firstImage'])
            ->findOrFail($id);

        return response()->json(['success' => true, 'data' => $order->append('status_label')]);
    }
}
