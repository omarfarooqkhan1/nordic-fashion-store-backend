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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade'); // Link to the Order
            $table->foreignId('product_variant_id')->constrained()->onDelete('cascade'); // Link to the specific ProductVariant

            $table->unsignedInteger('quantity'); // How many of this variant were ordered
            $table->decimal('price', 8, 2); // Price of the item at the time of order (important for historical accuracy)

            $table->timestamps();

            // Optional: Add a unique constraint to prevent duplicate variant entries within an order,
            // if you intend to only have one entry per variant per order.
            // $table->unique(['order_id', 'product_variant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};