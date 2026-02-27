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
            'governorate_id' => ['required', 'exists:governorates,id'],
            'district_id'    => ['nullable', 'exists:districts,id'],
            'delivery_point' => ['required', 'string'],
            'phone'          => ['required', 'string'],
            'coupon_id'      => ['nullable', 'exists:coupons,id'],
            'notes'          => ['nullable', 'string'],
            'items'          => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.product_variant_id' => ['required', 'exists:product_variants,id'],
            'items.*.quantity'   => ['required', 'integer', 'min:1'],
        ]);

        return DB::transaction(function () use ($data, $request) {

            $subtotal       = 0;
            $discountAmount = 0;
            $orderItemsData = [];
            $variantsToDeduct = [];

            // Calculate subtotal, build items, check stock
            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $variant = \App\Models\ProductVariant::where('id', $item['product_variant_id'])
                                                     ->where('product_id', $product->id)
                                                     ->first();

                if (!$variant) {
                    return response()->json([
                        'success' => false,
                        'message' => "النسخة المحددة غير تابعة للمنتج ({$product->name})",
                    ], 422);
                }

                if ($variant->stock < $item['quantity']) {
                    $variantName = trim("{$variant->color} {$variant->size}");
                    $msg = "الكمية المطلوبة من \"{$product->name}\"" .
                           ($variantName ? " ($variantName)" : '') .
                           " غير متوفرة. المتوفر: {$variant->stock} قطعة فقط.";
                    return response()->json([
                        'success' => false,
                        'message' => $msg,
                    ], 422);
                }

                $unitPrice = $product->effective_price;
                $subtotal += $unitPrice * $item['quantity'];
                
                $orderItemsData[] = [
                    'product_id'         => $product->id,
                    'product_variant_id' => $variant->id,
                    'quantity'           => $item['quantity'],
                    'price'              => $unitPrice,
                    'cost_price'         => $product->cost_price, // snapshot
                ];

                $variantsToDeduct[] = [
                    'variant'  => $variant,
                    'quantity' => $item['quantity']
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
                'governorate_id'  => $data['governorate_id'],
                'district_id'     => $data['district_id'] ?? null,
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

            // Deduct stock after successful creation
            foreach ($variantsToDeduct as $v) {
                $v['variant']->decrement('stock', $v['quantity']);
            }

            $order->load(['items.product', 'items.variant', 'district.governorate']);

            // Send Telegram Notification
            try {
                $botToken = \App\Models\Setting::get('telegram_bot_token');
                $chatId   = \App\Models\Setting::get('telegram_chat_id');

                if ($botToken && $chatId) {
                    $message = "🚨 *طلب جديد وصلك الآن!* 🚨\n\n";
                    $message .= "📦 *رقم الفاتورة:* `{$order->invoice_number}`\n";
                    $message .= "👤 *الزبون:* {$request->user()->first_name} {$request->user()->last_name}\n";
                    
                    $districtName = $order->district ? $order->district->name : 'غير محدد';
                    $govName = $order->district && $order->district->governorate ? $order->district->governorate->name : 'غير محدد';
                    
                    $message .= "📍 *المحافظة:* {$govName} - {$districtName}\n";
                    $message .= "💰 *قيمة الطلب:* " . number_format($order->total_price) . " د.ع\n";
                    
                    if ($order->notes) {
                        $message .= "📝 *ملاحظة الزبون:* {$order->notes}\n";
                    }

                    $message .= "\n[🔗 عرض الطلب في لوحة التحكم](" . route('admin.orders.show', $order->id) . ")";

                    \Illuminate\Support\Facades\Http::timeout(5)->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                        'chat_id'    => $chatId,
                        'text'       => $message,
                        'parse_mode' => 'Markdown',
                        'disable_web_page_preview' => true,
                    ]);
                }
            } catch (\Exception $e) {
                // Silently ignore to not interrupt checkout process
                \Illuminate\Support\Facades\Log::error('Telegram Notification Failed: ' . $e->getMessage());
            }

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
            ->with(['district.governorate', 'items.variant', 'items.product.firstImage'])
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $orders->map(fn ($o) => [
                'id'             => $o->id,
                'invoice_number' => $o->invoice_number,
                'total_price'    => $o->total_price,
                'discount_amount'=> $o->discount_amount,
                'status'         => $o->status,
                'status_label'   => $o->status_label,
                'delivery_point' => $o->delivery_point,
                'phone'          => $o->phone,
                'governorate'    => $o->district?->governorate?->name,
                'district'       => $o->district?->name,
                'created_at'     => $o->created_at->toISOString(),
                'items_count'    => $o->items->count(),
                'items'          => $o->items->map(fn ($item) => [
                    'id'        => $item->id,
                    'quantity'  => $item->quantity,
                    'price'     => $item->price,
                    'name'      => $item->product?->name,
                    'thumbnail' => $item->product?->firstImage?->url,
                    'variant'   => $item->variant ? trim("{$item->variant->color} {$item->variant->size}") : null,
                ]),
            ]),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
                'total'        => $orders->total(),
            ],
        ]);
    }

    public function show(Request $request, int $id)
    {
        $order = $request->user()->orders()
            ->with(['district.governorate', 'coupon', 'items.variant', 'items.product.firstImage'])
            ->findOrFail($id);

        return response()->json(['success' => true, 'data' => $order->append('status_label')]);
    }
}
