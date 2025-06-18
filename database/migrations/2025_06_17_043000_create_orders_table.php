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
        Schema::create('orders', function (Blueprint $table) {
            $table->id(); // Primary key

            // Foreign key to the users table (customer who placed the order)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->decimal('total', 10, 2); // Total amount of the order (e.g., 999.99)

            // Status of the payment
            $table->string('payment_status')->default('pending'); // e.g., 'pending', 'paid', 'failed', 'refunded'

            // Status of the shipping
            $table->string('shipping_status')->default('pending'); // e.g., 'pending', 'processed', 'shipped', 'delivered', 'returned'

            // $table->text('shipping_address')->nullable();
            // $table->text('billing_address')->nullable();
            // $table->string('payment_method')->nullable(); // e.g., 'credit_card', 'paypal'
            // $table->string('transaction_id')->nullable()->unique(); // ID from payment gateway

            $table->timestamps(); // created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};