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
            $table->foreignId('product_id')->constrained()->onDelete('cascade'); // <--- THIS IS THE CRUCIAL LINE
            $table->string('sku')->unique(); // Stock Keeping Unit
            $table->string('color')->nullable();
            $table->string('size')->nullable();
            $table->decimal('price', 10, 2); // Price for this variant
            $table->integer('stock')->default(0); // Quantity in stock
            $table->string('video_url')->nullable(); // Video URL for this variant
            $table->timestamps();
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