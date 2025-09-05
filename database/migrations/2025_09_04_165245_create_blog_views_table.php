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
        Schema::create('blog_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('session_id')->nullable(); // For guest users
            $table->string('ip_address')->nullable(); // For additional tracking
            $table->string('user_agent')->nullable(); // For additional tracking
            $table->timestamp('viewed_at');
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['blog_id', 'user_id']);
            $table->index(['blog_id', 'session_id']);
            $table->index(['blog_id', 'ip_address']);
            $table->index('viewed_at');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_views');
    }
};