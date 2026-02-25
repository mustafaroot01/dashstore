<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sliders', function (Blueprint $table) {
            // 'category' links to a category, 'external' links to a URL, null = no link
            $table->enum('link_type', ['category', 'external'])->nullable()->after('is_active');
            $table->unsignedBigInteger('category_id')->nullable()->after('link_type');
            // 'link' column already exists for external URL
        });
    }

    public function down(): void
    {
        Schema::table('sliders', function (Blueprint $table) {
            $table->dropColumn(['link_type', 'category_id']);
        });
    }
};
