<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->text('description');
            $table->string('size', 50)->nullable();
            $table->decimal('price', 10, 2);          // سعر البيع الأصلي (يظهر مشطوباً عند الخصم)
            $table->decimal('sale_price', 10, 2)->nullable();  // سعر الخصم
            $table->boolean('is_on_sale')->default(false);     // تفعيل سعر الخصم
            $table->decimal('cost_price', 10, 2);     // سعر التكلفة (سري)
            $table->boolean('is_active')->default(true);       // ظاهر/مخفي
            $table->boolean('is_available')->default(true);    // متوفر/غير متوفر
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
