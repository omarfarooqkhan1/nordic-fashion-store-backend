<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - {{ $orderNumber }}</title>
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
      .order-summary {
        background: #fafafa;
        border: 1px solid #eee;
        border-radius: 8px;
        padding: 18px;
        margin: 24px 0 18px 0;
      }
      .order-number {
        font-size: 1.3rem;
        font-weight: 700;
        color: #111;
        margin-bottom: 10px;
      }
      .status-badge {
        background: #111;
        color: #fff;
        padding: 6px 14px;
        border-radius: 16px;
        font-size: 0.9rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        display: inline-block;
      }
      .order-details {
        margin: 16px 0 0 0;
      }
      .order-details h3 {
        color: #111;
        border-bottom: 1px solid #eee;
        padding-bottom: 8px;
        margin-bottom: 12px;
        font-size: 1.1rem;
        font-weight: 600;
      }
      .item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #eee;
      }
      .item:last-child {
        border-bottom: none;
      }
      .item-details {
        flex: 1;
      }
      .item-name {
        font-weight: 600;
        margin-bottom: 2px;
        color: #111;
      }
      .item-variant, .item-quantity {
        font-size: 0.95rem;
        color: #444;
        margin-bottom: 2px;
      }
      .item-price {
        font-weight: 600;
        color: #111;
        font-size: 1rem;
        min-width: 60px;
        text-align: right;
      }
      .totals {
        background: #fff;
        border: 1.5px solid #111;
        border-radius: 8px;
        padding: 16px;
        margin: 18px 0;
      }
      .total-row {
        display: flex;
        justify-content: space-between;
        padding: 6px 0;
        border-bottom: 1px solid #eee;
        font-size: 1rem;
      }
      .total-row:last-child {
        border-bottom: none;
        border-top: 2px solid #111;
        padding-top: 12px;
        margin-top: 8px;
        font-weight: 700;
        font-size: 1.1rem;
        color: #111;
      }
      .shipping-info {
        background: #fafafa;
        border-radius: 8px;
        padding: 14px;
        margin: 18px 0;
      }
      .shipping-info h3 {
        color: #111;
        margin-bottom: 10px;
        font-size: 1rem;
        font-weight: 600;
      }
      .address {
        line-height: 1.5;
        color: #222;
      }
      .shipping-time {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 16px;
        margin: 20px 0;
      }
      .shipping-time h3 {
        margin: 0 0 10px 0;
        color: #111;
        font-size: 1.1rem;
      }
      .shipping-time p {
        margin: 0;
        color: #333;
        font-size: 1rem;
      }
      .shipping-time p:last-child {
        margin: 5px 0 0 0;
        color: #666;
        font-size: 0.9rem;
      }
      .divider {
        height: 1.5px;
        background: #111;
        margin: 24px 0;
        border: none;
      }
      .next-steps {
        background: #fff;
        border-left: 4px solid #111;
        padding: 14px 18px;
        border-radius: 0 8px 8px 0;
        margin: 18px 0;
      }
      .next-steps h3 {
        color: #111;
        margin-top: 0;
        font-size: 1rem;
        font-weight: 600;
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
        .order-summary, .totals, .shipping-info, .next-steps {
          padding: 10px !important;
        }
        .item {
          flex-direction: column;
          align-items: flex-start;
          gap: 6px;
        }
        .item-price {
          align-self: flex-end;
        }
      }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="brand">Nord Flex</div>
            <h1>Order Confirmation</h1>
        </div>
        
        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Hello {{ $customerName }},
            </div>
            <p>Thank you for your order! We're excited to confirm that we've received your purchase and it's being processed. Here are the details of your order:</p>
            <!-- Order Summary -->
            <div class="order-summary">
                <div class="order-number">Order #{{ $orderNumber }}</div>
                <span class="status-badge">Confirmed</span>
                <p style="margin-top: 10px; color: #444; font-size: 0.98rem;">Order Date: {{ $order->created_at->format('F j, Y \a\t g:i A') }}</p>
            </div>
            <hr class="divider">
            <!-- Order Items -->
            <div class="order-details">
                <h3>📦 Your Items</h3>
                @foreach($orderItems as $item)
                <div class="item">
                    <div class="item-details">
                        <div class="item-name">{{ $item->product_name }}</div>
                        <div class="item-variant">{{ $item->variant_name }}</div>
                        <div class="item-quantity">Quantity: {{ $item->quantity }}</div>
                    </div>
                    <div class="item-price">€{{ number_format($item->subtotal, 2) }}</div>
                </div>
                @endforeach
            </div>
            <!-- Order Totals -->
            <div class="totals">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span>€{{ number_format($order->subtotal, 2) }}</span>
                </div>
                <div class="total-row">
                    <span>Shipping:</span>
                    <span>€{{ number_format($order->shipping, 2) }}</span>
                </div>
                <div class="total-row">
                    <span>Tax (VAT 25%):</span>
                    <span>€{{ number_format($order->tax, 2) }}</span>
                </div>
                <div class="total-row">
                    <span>Total:</span>
                    <span>€{{ number_format($order->total, 2) }}</span>
                </div>
            </div>
            <!-- Shipping Information -->
            <div class="shipping-info">
                <h3>🚚 Shipping Address</h3>
                <div class="address">
                    <strong>{{ $shippingAddress['name'] }}</strong><br>
                    {{ $shippingAddress['address'] }}<br>
                    {{ $shippingAddress['city'] }}, {{ $shippingAddress['state'] }} {{ $shippingAddress['postal_code'] }}<br>
                    {{ $shippingAddress['country'] }}
                </div>
            </div>
            
            <!-- Estimated Shipping Time -->
            <div class="shipping-time" style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 16px; margin: 20px 0;">
                <h3 style="margin: 0 0 10px 0; color: #111; font-size: 1.1rem;">📦 Estimated Shipping Time</h3>
                <p style="margin: 0; color: #333; font-size: 1rem;"><strong>7-14 business days</strong></p>
                <p style="margin: 5px 0 0 0; color: #666; font-size: 0.9rem;">Your order will be processed and shipped within this timeframe</p>
            </div>
            
            <hr class="divider">
            <!-- Next Steps -->
            <div class="next-steps">
                <h3>What's Next?</h3>
                <ul style="margin: 10px 0; padding-left: 20px; color: #222;">
                    <li>We'll process your order within 1-2 business days</li>
                    <li>You'll receive a shipping confirmation email with tracking information</li>
                    <li><strong>Estimated shipping time: 7-14 business days</strong></li>
                    <li>Standard delivery takes 3-5 business days within Nordic countries</li>
                    <li>For any questions, please contact our customer service team</li>
                </ul>
            </div>
            <p style="margin-top: 24px;">Thank you for choosing Nord Flex. We appreciate your business and look forward to serving you again!</p>
            <p style="color: #444; font-size: 0.98rem; margin-top: 16px;">
                <strong>Need help?</strong> Contact our customer service team at 
                <a href="mailto:support@nordflex.shop">support@nordflex.shop</a> 
                or call us at +358 44 9782549.
            </p>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p><strong>Nord Flex</strong></p>
            <p>Bringing you the finest Nordic-inspired fashion</p>
            <p>
                <a href="mailto:support@nordflex.shop">support@nordflex.shop</a> | 
                <a href="tel:+4681234567">+358 44 9782549</a>
            </p>
            <p style="margin-top: 15px; font-size: 12px; color: #ccc;">
                © {{ date('Y') }} Nord Flex. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
