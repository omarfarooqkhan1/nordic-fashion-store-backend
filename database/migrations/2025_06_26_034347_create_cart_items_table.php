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
            $table->string('size')->nullable(); // Size is stored at cart item level

            $table->unsignedInteger('quantity')->default(1);

            $table->timestamps();

            $table->unique(['cart_id', 'product_variant_id', 'size']); // prevents duplicate variant+size combinations in cart
        });
    }

public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
