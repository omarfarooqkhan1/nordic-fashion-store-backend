<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Status Update - {{ $orderNumber }}</title>
    <style>
      body {
        font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        background: #fff;
        color: #111;
        margin: 0;
        padding: 0;
        line-height: 1.6;
      }
      .container {
        max-width: 600px;
        margin: 0 auto;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        overflow: hidden;
      }
      .header {
        background: #111;
        color: #fff;
        padding: 32px 24px 20px 24px;
        text-align: center;
      }
      .header h1 {
        margin: 0;
        font-size: 2rem;
        font-weight: 700;
        letter-spacing: 1px;
      }
      .brand {
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 2px;
        color: #fff;
      }
      .content {
        padding: 32px 24px;
      }
      .greeting {
        font-size: 1.1rem;
        margin-bottom: 18px;
        color: #111;
        font-weight: 500;
      }
      .status-update {
        background: #f8f9fa;
        border: 2px solid #111;
        border-radius: 12px;
        padding: 24px;
        margin: 24px 0;
        text-align: center;
      }
      .status-badge {
        background: #111;
        color: #fff;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 1.1rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        display: inline-block;
        margin-bottom: 12px;
      }
      .status-badge.processing {
        background: #f59e0b;
      }
      .status-badge.shipped {
        background: #10b981;
      }
      .status-badge.delivered {
        background: #059669;
      }
      .status-badge.cancelled {
        background: #ef4444;
      }
      .order-number {
        font-size: 1.3rem;
        font-weight: 700;
        color: #111;
        margin-bottom: 10px;
      }
      .order-items {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 20px;
        margin: 20px 0;
      }
      .order-items h3 {
        margin: 0 0 16px 0;
        color: #111;
        font-size: 1.1rem;
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 8px;
      }
      .item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #f3f4f6;
      }
      .item:last-child {
        border-bottom: none;
      }
      .item-details {
        flex: 1;
      }
      .item-name {
        font-weight: 600;
        color: #111;
        margin-bottom: 4px;
      }
      .item-variant {
        color: #666;
        font-size: 0.9rem;
      }
      .item-price {
        font-weight: 600;
        color: #111;
        text-align: right;
        min-width: 80px;
      }
      .order-summary {
        background: #f8f9fa;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 20px;
        margin: 20px 0;
      }
      .order-summary h3 {
        margin: 0 0 16px 0;
        color: #111;
        font-size: 1.1rem;
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 8px;
      }
      .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 6px 0;
        color: #666;
      }
      .summary-row.total {
        border-top: 1px solid #e5e7eb;
        margin-top: 8px;
        padding-top: 12px;
        font-weight: 700;
        color: #111;
        font-size: 1.1rem;
      }
      .address-info {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 20px;
        margin: 20px 0;
      }
      .address-info h3 {
        margin: 0 0 12px 0;
        color: #111;
        font-size: 1.1rem;
      }
      .address-details {
        color: #666;
        line-height: 1.5;
      }
      .tracking-info {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 20px;
        margin: 20px 0;
      }
      .tracking-info h3 {
        margin: 0 0 12px 0;
        color: #111;
        font-size: 1.1rem;
      }
      .tracking-number {
        font-family: 'Courier New', monospace;
        background: #f3f4f6;
        padding: 8px 12px;
        border-radius: 6px;
        font-weight: 600;
        color: #111;
        display: inline-block;
        margin: 8px 0;
      }
      .divider {
        height: 1.5px;
        background: #111;
        margin: 24px 0;
        border: none;
      }
      .footer {
        background: #111;
        color: #fff;
        padding: 24px;
        text-align: center;
        font-size: 0.95rem;
      }
      .footer a {
        color: #fff;
        text-decoration: underline;
      }
      .footer p {
        margin: 5px 0;
      }
      @media only screen and (max-width: 600px) {
        .container {
          margin: 0;
          border-radius: 0;
          box-shadow: none;
        }
        .header, .content, .footer {
          padding: 14px !important;
        }
        .status-update, .tracking-info, .order-items, .order-summary, .address-info {
          padding: 16px !important;
        }
        .item {
          flex-direction: column;
          align-items: flex-start;
          gap: 8px;
        }
        .item-price {
          text-align: left;
          min-width: auto;
        }
        .summary-row {
          font-size: 0.9rem;
        }
        .address-details {
          font-size: 0.9rem;
        }
      }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="brand">Nord Flex</div>
            <h1>Order Status Update</h1>
        </div>
        
        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Hello {{ $customerName }},
            </div>
            
            <p>We have an update on your order. Here's the latest information:</p>
            
            <!-- Status Update -->
            <div class="status-update">
                <div class="order-number">Order #{{ $orderNumber }}</div>
                <div class="status-badge {{ $currentStatus }}">
                    @switch($currentStatus)
                        @case('pending')
                            Order Received
                            @break
                        @case('processing')
                            Processing
                            @break
                        @case('shipped')
                            Shipped
                            @break
                        @case('delivered')
                            Delivered
                            @break
                        @case('cancelled')
                            Cancelled
                            @break
                        @default
                            {{ ucfirst($currentStatus) }}
                    @endswitch
                </div>
                <p style="margin: 12px 0 0 0; color: #666; font-size: 0.95rem;">
                    Status updated on {{ $order->updated_at->format('F j, Y \a\t g:i A') }}
                </p>
            </div>

            @if($currentStatus === 'processing')
                <p>Great news! Your order is now being processed. Our team is carefully preparing your items for shipment.</p>
                <p><strong>What's next:</strong> You'll receive another email with tracking information once your order ships.</p>
            @elseif($currentStatus === 'shipped')
                <p>Excellent! Your order has been shipped and is on its way to you.</p>
                
                @if($trackingNumber)
                <div class="tracking-info">
                    <h3>📦 Tracking Information</h3>
                    <p><strong>Tracking Number:</strong></p>
                    <div class="tracking-number">{{ $trackingNumber }}</div>
                    @if($shippingService)
                    <p><strong>Shipping Service:</strong> {{ $shippingService }}</p>
                    @endif
                    <p style="color: #666; font-size: 0.9rem; margin-top: 12px;">
                        You can track your package using the tracking number above on the {{ $shippingService ?? 'carrier' }}'s website.
                    </p>
                </div>
                @endif
                
                <p><strong>Estimated delivery:</strong> 3-5 business days within Nordic countries</p>
            @elseif($currentStatus === 'delivered')
                <p>🎉 Wonderful! Your order has been delivered successfully.</p>
                <p>We hope you love your new items from Nord Flex. If you have any questions or concerns about your order, please don't hesitate to contact us.</p>
                <p><strong>Thank you for choosing Nord Flex!</strong></p>
            @elseif($currentStatus === 'cancelled')
                <p>We're sorry to inform you that your order has been cancelled.</p>
                <p>If you have any questions about this cancellation or need assistance with a new order, please contact our customer service team.</p>
                <p>Any charges made to your payment method will be refunded within 3-5 business days.</p>
            @else
                <p>Your order status has been updated. If you have any questions, please contact our customer service team.</p>
            @endif

            <!-- Order Items -->
            <div class="order-items">
                <h3>📦 Order Items</h3>
                @foreach($orderItems as $item)
                <div class="item">
                    <div style="display: flex; align-items: center; gap: 12px; flex: 1;">
                        @if($item->product_image)
                        <img src="{{ $item->product_image }}" alt="{{ $item->product_name }}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 6px; border: 1px solid #eee;">
                        @endif
                        <div class="item-details">
                            <div class="item-name">{{ $item->product_name }}</div>
                            <div class="item-variant">{{ $item->variant_name }} × {{ $item->quantity }}</div>
                        </div>
                    </div>
                    <div class="item-price">
                        {{ $order->getFormattedPrice($item->subtotal) }}
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Order Summary -->
            <div class="order-summary">
                <h3>💰 Order Summary</h3>
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span><span class="currency-symbol">{{ $currencySymbol }}</span>{{ number_format($order->subtotal, 2) }}</span>
                </div>
                <div class="summary-row">
                    <span>Shipping:</span>
                    <span>
                        @if($order->shipping == 0)
                            Free
                        @else
                            <span class="currency-symbol">{{ $currencySymbol }}</span>{{ number_format($order->shipping, 2) }}
                        @endif
                    </span>
                </div>
                <div class="summary-row">
                    <span>Tax (VAT):</span>
                    <span><span class="currency-symbol">{{ $currencySymbol }}</span>{{ number_format($order->tax, 2) }}</span>
                </div>
                <div class="summary-row total">
                    <span>Total:</span>
                    <span><span class="currency-symbol">{{ $currencySymbol }}</span>{{ number_format($order->total, 2) }}</span>
                </div>
            </div>

            <!-- Shipping Address -->
            <div class="address-info">
                <h3>🏠 Shipping Address</h3>
                <div class="address-details">
                    <strong>{{ $order->shipping_name }}</strong><br>
                    {{ $order->shipping_address }}<br>
                    {{ $order->shipping_city }}@if($order->shipping_state), {{ $order->shipping_state }}@endif {{ $order->shipping_postal_code }}<br>
                    {{ $order->shipping_country }}
                    @if($order->shipping_phone)
                    <br><strong>Phone:</strong> {{ $order->shipping_phone }}
                    @endif
                </div>
            </div>

            @if(!$order->billing_same_as_shipping)
            <!-- Billing Address -->
            <div class="address-info">
                <h3>💳 Billing Address</h3>
                <div class="address-details">
                    <strong>{{ $order->billing_name }}</strong><br>
                    {{ $order->billing_address }}<br>
                    {{ $order->billing_city }}@if($order->billing_state), {{ $order->billing_state }}@endif {{ $order->billing_postal_code }}<br>
                    {{ $order->billing_country }}
                    @if($order->billing_phone)
                    <br><strong>Phone:</strong> {{ $order->billing_phone }}
                    @endif
                </div>
            </div>
            @endif

            <hr class="divider">
            
            <p style="color: #444; font-size: 0.98rem; margin-top: 16px;">
                <strong>Need help?</strong> Contact our customer service team at 
                <a href="mailto:support@nordflex.store">support@nordflex.store</a> 
                or call us at +358 44 9782549.
            </p>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p><strong>Nord Flex</strong></p>
            <p>Bringing you the finest Nordic-inspired fashion</p>
            <p>
                <a href="mailto:support@nordflex.store">support@nordflex.store</a> | 
                <a href="tel:+358449782549">+358 44 9782549</a>
            </p>
            <p style="margin-top: 15px; font-size: 12px; color: #ccc;">
                © {{ date('Y') }} Nord Flex. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>