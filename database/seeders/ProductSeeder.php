<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Create default category if none exists
        $category = Category::firstOrCreate(
            ['name' => 'مياه'],
            ['image' => 'categories/default.jpg', 'is_active' => true]
        );

        $products = [
            ['name' => 'قارورة ماء 19 لتر',  'price' => 3500, 'cost_price' => 2000, 'size' => '19 لتر'],
            ['name' => 'قارورة ماء 10 لتر',  'price' => 2000, 'cost_price' => 1200, 'size' => '10 لتر'],
            ['name' => 'قارورة ماء 1.5 لتر', 'price' => 500,  'cost_price' => 300,  'size' => '1.5 لتر'],
        ];

        foreach ($products as $p) {
            Product::firstOrCreate(
                ['name' => $p['name']],
                array_merge($p, [
                    'category_id'  => $category->id,
                    'description'  => 'مياه نقية عالية الجودة من أمواج ديالى',
                    'is_active'    => true,
                    'is_available' => true,
                    'is_on_sale'   => false,
                ])
            );
        }
    }
}
