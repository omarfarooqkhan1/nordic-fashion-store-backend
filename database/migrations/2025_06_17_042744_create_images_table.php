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
        Schema::create('images', function (Blueprint $table) {
            $table->id(); // Primary key (auto-incrementing ID)
            $table->string('url'); // Stores the URL to the image file (e.g., from a CDN)
            $table->string('alt_text')->nullable(); // Optional: For accessibility (alt attribute)
            $table->unsignedInteger('sort_order')->default(0); // For defining display order of images

            // Polymorphic columns:
            // This creates two columns: 'imageable_id' (integer) and 'imageable_type' (string).
            // 'imageable_id' will store the ID of the related product or product variant.
            // 'imageable_type' will store the class name of the related model (e.g., 'App\Models\Product', 'App\Models\ProductVariant').
            $table->morphs('imageable');

            $table->timestamps(); // Created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};