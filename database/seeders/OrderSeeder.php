<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\District;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $users     = User::all();
        $districts = District::all();
        $products  = Product::all();

        if ($users->isEmpty() || $districts->isEmpty() || $products->isEmpty()) {
            $this->command->warn('يجب وجود مستخدمين، أقضية، ومنتجات قبل تشغيل OrderSeeder');
            return;
        }

        $statuses = ['pending', 'received', 'preparing', 'delivering', 'delivered'];

        for ($i = 1; $i <= 5; $i++) {
            $user     = $users->random();
            $district = $districts->random();
            $status   = $statuses[array_rand($statuses)];

            $order = Order::create([
                'user_id'         => $user->id,
                'district_id'     => $district->id,
                'delivery_point'  => 'أمام المدرسة الفلانية',
                'phone'           => $user->phone,
                'coupon_id'       => null,
                'discount_amount' => 0,
                'notes'           => $i % 2 === 0 ? 'الرجاء التوصيل قبل الظهر' : null,
                'status'          => $status,
                'total_price'     => 0, // سيتم حسابه بعد الأصناف
            ]);

            $total = 0;
            $itemsCount = rand(1, 3);

            for ($j = 0; $j < $itemsCount; $j++) {
                $product = $products->random();
                $qty     = rand(1, 4);
                $price   = (float) $product->price;
                $cost    = (float) $product->cost_price;

                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $product->id,
                    'quantity'   => $qty,
                    'price'      => $price,
                    'cost_price' => $cost,
                ]);

                $total += $price * $qty;
            }

            $order->update(['total_price' => $total]);
        }
    }
}
