<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cart_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_variant_id')->constrained()->onDelete('cascade');

            $table->unsignedInteger('quantity')->default(1);

            $table->timestamps();

            $table->unique(['cart_id', 'product_variant_id']); // prevents duplicate variants in cart
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
