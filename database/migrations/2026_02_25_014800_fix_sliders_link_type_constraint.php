<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // In SQLite, altering an enum column might fail or drop data if not handled carefully.
        // The safest way in Laravel 11/DBAL 4 is to change the column to a simple string.
        Schema::table('sliders', function (Blueprint $table) {
            $table->string('link_type')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('sliders', function (Blueprint $table) {
            $table->enum('link_type', ['category', 'external'])->nullable()->change();
        });
    }
};
