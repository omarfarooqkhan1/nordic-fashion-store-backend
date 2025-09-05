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
        Schema::create('blog_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('session_id')->nullable(); // For guest users
            $table->string('ip_address')->nullable(); // For additional tracking
            $table->timestamps();
            
            // Ensure a user can only like a blog once
            $table->unique(['blog_id', 'user_id'], 'unique_user_blog_like');
            // Ensure a session can only like a blog once
            $table->unique(['blog_id', 'session_id'], 'unique_session_blog_like');
            
            // Indexes for better performance
            $table->index(['blog_id', 'user_id']);
            $table->index(['blog_id', 'session_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_likes');
    }
};