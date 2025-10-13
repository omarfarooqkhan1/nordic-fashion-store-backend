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
        // Remove price column from products table (if it exists)
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'price')) {
                $table->dropColumn('price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add price column back to products table
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'price')) {
                $table->decimal('price', 10, 2)->default(0.00)->after('gender'); // Add price column after gender with default value
            }
        });
    }
};