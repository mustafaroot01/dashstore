<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 20)->unique(); // مثال: AW-00001
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('district_id')->constrained();
            $table->text('delivery_point');  // النقطة الدالة (نص حر)
            $table->string('phone', 20);     // هاتف التوصيل
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->enum('status', ['pending', 'received', 'preparing', 'delivering', 'delivered'])->default('pending');
            $table->decimal('total_price', 10, 2);
            $table->text('notes')->nullable();
            $table->timestamps(); // created_at = وقت الطلب الرسمي
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
