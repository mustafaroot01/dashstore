<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('admin_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->cascadeOnDelete();
            $table->text('action');      // وصف ما تم
            $table->string('ip_address', 45);
            $table->string('location', 100)->nullable();  // محافظة/دولة
            $table->string('device', 100)->nullable();    // Windows/Mac/Android
            $table->string('browser', 100)->nullable();   // Chrome/Firefox
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_logs');
    }
};
