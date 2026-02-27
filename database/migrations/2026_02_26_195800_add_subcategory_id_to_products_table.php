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
        Schema::table('products', function (Blueprint $table) {
            // Check if foreign key exists first to avoid duplicates (Laravel doesn't have a direct raw check, but we can try/catch or just add it if column was just dropped/created. A simpler way is to catch the exception if it already exists).
            // Actually, since this is specific to this deployment crash, we can safely just add the constraint now since we know it failed on the constraint step.
            try {
                $table->foreign('subcategory_id')->references('id')->on('subcategories')->nullOnDelete();
            } catch (\Exception $e) {
                // Constraint might already exist, ignore
            }
        });
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
