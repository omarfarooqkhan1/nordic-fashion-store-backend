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
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('rating')->comment('1-5 star rating');
            $table->text('review_text')->nullable();
            $table->string('title')->nullable();
            $table->boolean('is_verified_purchase')->default(false);
            $table->json('media')->nullable(); // For images/videos
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->timestamps();
            
            // Ensure one review per user per product
            $table->unique(['user_id', 'product_id']);
            
            // Indexes for performance
            $table->index(['product_id', 'rating']);
            $table->index(['product_id', 'status']);
            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};
