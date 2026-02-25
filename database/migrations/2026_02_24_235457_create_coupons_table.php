<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique(); // مثال: AMWAJ10
            $table->enum('type', ['percent', 'fixed']); // نسبة % أو مبلغ ثابت
            $table->decimal('value', 10, 2);
            $table->unsignedInteger('usage_limit')->nullable(); // NULL = غير محدود
            $table->unsignedInteger('used_count')->default(0);
            $table->dateTime('expires_at')->nullable();         // NULL = بدون انتهاء
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
