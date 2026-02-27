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
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'subcategory_id')) {
                $table->bigInteger('subcategory_id')->unsigned()->nullable()->after('category_id');
            }
        });

        // Add foreign key in a separate statement to ensure column exists
        try {
            Schema::table('products', function (Blueprint $table) {
                $table->foreign('subcategory_id')->references('id')->on('subcategories')->nullOnDelete();
            });
        } catch (\Exception $e) {
            // Constraint might already exist, ignore
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['subcategory_id']);
            $table->dropColumn('subcategory_id');
        });
    }
};
