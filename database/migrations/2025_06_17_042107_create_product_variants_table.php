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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('sku')->unique(); // Stock Keeping Unit
            $table->string('color'); // Color is required now (no size field)
            $table->decimal('price', 10, 2); // Price for this color variant
            $table->string('video_url')->nullable(); // Video URL for this color variant
            $table->string('video_path')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index(['product_id', 'color']);
            $table->index('sku');
            
            // Ensure unique color per product
            $table->unique(['product_id', 'color']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};