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
            $table->decimal('price_difference', 10, 2)->default(0.00); // Price adjustment from base product price
            $table->integer('stock')->default(0); // Quantity in stock
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