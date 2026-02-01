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
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id')->nullable(); // For guest checkout
            $table->string('order_number')->unique();
            $table->string('status')->default('pending'); // pending, processing, completed, cancelled, refunded
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax', 10, 2);
            $table->decimal('shipping', 10, 2);
            $table->decimal('total', 10, 2);
            $table->text('notes')->nullable();
            
            // Shipping information
            $table->string('shipping_name');
            $table->string('shipping_email');
            $table->string('shipping_phone')->nullable();
            $table->string('shipping_address');
            $table->string('shipping_city');
            $table->string('shipping_state')->nullable();
            $table->string('shipping_postal_code');
            $table->string('shipping_country');
            
            // Billing information (can be same as shipping)
            $table->boolean('billing_same_as_shipping')->default(true);
            $table->string('billing_name')->nullable();
            $table->string('billing_email')->nullable();
            $table->string('billing_phone')->nullable();
            $table->string('billing_address')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_state')->nullable();
            $table->string('billing_postal_code')->nullable();
            $table->string('billing_country')->nullable();
            
            // Payment information
            $table->string('payment_method')->nullable(); // credit_card, paypal, etc.
            $table->string('payment_status')->default('pending'); // pending, completed, failed
            $table->string('payment_transaction_id')->nullable();
            $table->string('currency', 3)->default('EUR'); // ISO 4217 currency code
            $table->string('tracking_number')->nullable();
            $table->string('shipping_service')->nullable(); // DHL, FedEx, UPS, etc.
            
            $table->timestamps();

            // Indexes for better performance
            $table->index(['user_id', 'status']);
            $table->index(['session_id', 'status']);
            $table->index('order_number');
            $table->index('tracking_number');
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
