<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('static_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->longText('content');
            $table->json('images')->nullable(); // Store images with metadata (position, caption, alt_text, width, height)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('static_pages');
    }
};
