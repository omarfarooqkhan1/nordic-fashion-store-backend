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
        Schema::create('custom_jacket_cart_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('item_id')->unique();
            $table->string('session_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name');
            $table->string('color');
            $table->string('size');
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            $table->text('front_image_url');
            $table->text('back_image_url');
            $table->json('logos')->nullable();
            $table->text('custom_description')->nullable();
            $table->timestamps();
            
            $table->index(['session_id']);
            $table->index(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_jacket_cart_items');
    }
};
