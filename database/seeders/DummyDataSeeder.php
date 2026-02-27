<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\District;
use App\Models\Governorate;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        
        // Truncate Tables
        User::truncate();
        Governorate::truncate();
        District::truncate();
        Category::truncate();
        Subcategory::truncate();
        Product::truncate();
        ProductVariant::truncate();
        Order::truncate();
        OrderItem::truncate();
        
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        // 1. Governorates & Districts
        $govNames = ['بغداد', 'أربيل', 'البصرة', 'ديالى', 'نينوى'];
        $distNames = [
            'بغداد' => ['الكرادة', 'المنصور', 'الأعظمية'],
            'أربيل' => ['عنكاوة', 'سوران', 'شقلاوة'],
            'البصرة' => ['الزبير', 'أبو الخصيب', 'شط العرب'],
            'ديالى' => ['بعقوبة', 'الخالص', 'مقدادية'],
            'نينوى' => ['الموصل', 'تلعفر', 'الحمدانية'],
        ];

        foreach ($govNames as $gName) {
            $gov = Governorate::create(['name' => $gName, 'is_active' => true]);
            foreach ($distNames[$gName] as $dName) {
                District::create(['governorate_id' => $gov->id, 'name' => $dName, 'is_active' => true]);
            }
        }

        // 2. Categories & Subcategories
        $catData = [
            'أجهزة كهربائية' => ['ثلاجات', 'غسالات', 'مكيفات'],
            'موبايلات' => ['آيفون', 'سامسونج', 'شاومي'],
            'لابتوبات' => ['أبل', 'ديل', 'اتش بي'],
            'عطور' => ['رجالي', 'نسائي', 'أطفال'],
            'ملابس' => ['رجالي', 'نسائي', 'رياضي'],
        ];

        foreach ($catData as $cName => $subNames) {
            $cat = Category::create(['name' => $cName, 'image' => 'placeholder.png', 'is_active' => true]);
            foreach ($subNames as $sName) {
                Subcategory::create(['category_id' => $cat->id, 'name' => $sName, 'image' => 'placeholder.png', 'is_active' => true]);
            }
        }

        // 3. Customers (Users)
        $districts = District::all();
        $users = [];
        for ($i = 1; $i <= 10; $i++) {
            $dist = $districts->random();
            $users[] = User::create([
                'first_name' => 'زبون ' . $i,
                'last_name' => 'وهمي',
                'phone' => '07' . rand(500000000, 999999999),
                'password' => Hash::make('password'),
                'is_active' => true,
                'governorate_id' => $dist->governorate_id,
                'district_id' => $dist->id,
                'address' => 'عنوان تجريبي ' . $i,
                'gender' => 'male',
            ]);
        }

        // 4. Products & Variants (Create ~120 products to test large orders)
        $subcategories = Subcategory::all();
        $products = [];
        for ($i = 1; $i <= 120; $i++) {
            $sub = $subcategories->random();
            $price = rand(10, 500) * 1000;
            
            $product = Product::create([
                'category_id' => $sub->category_id,
                'subcategory_id' => $sub->id,
                'name' => 'منتج تجريبي رقم ' . $i,
                'sku' => 'SKU-' . str_pad($i, 5, '0', STR_PAD_LEFT) . strtoupper(Str::random(3)),
                'description' => 'وصف تجريبي للمنتج رقم ' . $i,
                'price' => $price,
                'cost_price' => $price * 0.7,
                'is_active' => true,
                'is_available' => true,
            ]);

            // Add 1 or 2 variants
            $variantsCount = rand(1, 2);
            for ($v = 1; $v <= $variantsCount; $v++) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'color' => $v == 1 ? 'أسود' : 'أبيض',
                    'size' => rand(30, 45),
                    'stock' => rand(10, 100),
                ]);
            }
            $products[] = $product;
        }

        // 5. Create a Massive Order (105 items)
        $user = $users[0];
        $order = Order::create([
            'user_id' => $user->id,
            'invoice_number' => 'INV-TEST-001',
            'governorate_id' => $user->governorate_id,
            'district_id' => $user->district_id,
            'delivery_point' => 'نقطة دالة تجريبية لطلب ضخم',
            'phone' => $user->phone,
            'status' => 'pending',
            'total_price' => 0,
        ]);

        $totalPrice = 0;
        $variants = ProductVariant::with('product')->inRandomOrder()->take(105)->get();
        
        foreach ($variants as $variant) {
            $qty = rand(1, 3);
            $price = $variant->product->price;
            
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $variant->product_id,
                'product_variant_id' => $variant->id,
                'quantity' => $qty,
                'price' => $price,
                'cost_price' => $variant->product->cost_price ?? ($price * 0.7),
            ]);
            
            $totalPrice += ($price * $qty);
        }
        
        $order->update(['total_price' => $totalPrice]);
    }
}
