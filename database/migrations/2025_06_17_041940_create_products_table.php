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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->enum('gender', ['male', 'female', 'unisex'])->default('unisex');
            $table->decimal('price', 10, 2)->default(0.00); // Base price for display
            $table->decimal('discount', 5, 2)->default(0); // Discount percentage (0-100.00)
            $table->boolean('is_active')->default(false); // Product status
            $table->json('available_sizes')->nullable(); // Available sizes: ["XS", "S", "M", "L", "XL", "One Size"]
            $table->string('size_guide_image')->nullable(); // Size guide image path
            $table->timestamps();

            // Indexes for better performance
            $table->index(['category_id', 'gender']);
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};