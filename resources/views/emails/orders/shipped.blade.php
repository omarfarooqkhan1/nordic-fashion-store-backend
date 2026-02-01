<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Shipped - {{ $order->order_number }}</title>
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
      .tracking-panel {
        background: #fff;
        border-left: 4px solid #111;
        padding: 14px 18px;
        border-radius: 0 8px 8px 0;
        margin: 18px 0;
      }
      .tracking-number {
        font-size: 1.1rem;
        font-weight: 700;
        color: #111;
        margin-bottom: 8px;
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
      .divider {
        height: 1.5px;
        background: #111;
        margin: 24px 0;
        border: none;
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
        .order-summary, .tracking-panel {
          padding: 10px !important;
        }
      }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="brand">Nord Flex</div>
            <h1>Order Shipped</h1>
        </div>
        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Hi {{ $order->shipping_name }},
            </div>
            <p>Your order <strong>#{{ $order->order_number }}</strong> has been dispatched and is on its way to you!</p>
            <!-- Order Summary -->
            <div class="order-summary">
                <div class="order-number">Order #{{ $order->order_number }}</div>
                <span class="status-badge">Shipped</span>
                <p style="margin-top: 10px; color: #444; font-size: 0.98rem;">Order Date: {{ $order->created_at->format('F j, Y \a\t g:i A') }}</p>
            </div>
            <hr class="divider">
            <!-- Tracking Panel -->
            <div class="tracking-panel">
                <div class="tracking-number">Tracking Number: {{ $order->tracking_number }}</div>
                @if($order->shipping_service)
                <div class="shipping-service">Shipping Service: <strong>{{ $order->shipping_service }}</strong></div>
                @endif
                <p style="margin-bottom: 8px; color: #222;">You can use this number to track your package with the courier.</p>
            </div>
            <hr class="divider">
            <!-- Order Items and Totals -->
            @include('emails.orders.partials.order-summary', ['order' => $order])
            <hr class="divider">
            <p style="margin-top: 24px;">Thank you for shopping with us! We hope you enjoy your purchase.</p>
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
                <a href="tel:+4681234567">+358 44 9782549</a>
            </p>
            <p style="margin-top: 15px; font-size: 12px; color: #ccc;">
                © {{ date('Y') }} Nord Flex. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
