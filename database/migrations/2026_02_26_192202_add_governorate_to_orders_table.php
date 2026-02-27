<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('governorate_id')->nullable()->after('user_id')->constrained('governorates')->nullOnDelete();
            $table->foreignId('district_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['governorate_id']);
            $table->dropColumn('governorate_id');
            // Assuming we don't strict back district_id simply
        });
    }
};
