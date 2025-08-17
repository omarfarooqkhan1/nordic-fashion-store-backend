<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Shipped - {{ $order->order_number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #2c5aa0 0%, #1e3a5f 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 300;
            letter-spacing: 1px;
        }
        .brand {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #2c5aa0;
        }
        .order-summary {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 25px;
            margin: 30px 0;
        }
        .order-number {
            font-size: 24px;
            font-weight: 600;
            color: #2c5aa0;
            margin-bottom: 15px;
        }
        .status-badge {
            background-color: #6f42c1;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .tracking-panel {
            background-color: #e8f4f8;
            border-left: 4px solid #2c5aa0;
            padding: 20px;
            border-radius: 0 8px 8px 0;
            margin: 25px 0;
        }
        .tracking-number {
            font-size: 18px;
            font-weight: 600;
            color: #2c5aa0;
            margin-bottom: 10px;
        }
        .footer {
            background-color: #2c5aa0;
            color: white;
            padding: 30px;
            text-align: center;
            font-size: 14px;
        }
        .footer a {
            color: #ffd700;
            text-decoration: none;
        }
        .footer p {
            margin: 5px 0;
        }
        .divider {
            height: 2px;
            background: linear-gradient(90deg, #2c5aa0, #ffd700, #2c5aa0);
            margin: 30px 0;
            border: none;
        }
        @media only screen and (max-width: 600px) {
            .container {
                margin: 0;
                box-shadow: none;
            }
            .header, .content, .footer {
                padding: 20px;
            }
            .order-summary, .tracking-panel {
                padding: 15px;
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
                <p style="margin-top: 15px; color: #6c757d;">Order Date: {{ $order->created_at->format('F j, Y \a\t g:i A') }}</p>
            </div>
            <hr class="divider">
            <!-- Tracking Panel -->
            <div class="tracking-panel">
                <div class="tracking-number">Tracking Number: {{ $order->tracking_number }}</div>
                @if($order->shipping_service)
                <div class="shipping-service">Shipping Service: <strong>{{ $order->shipping_service }}</strong></div>
                @endif
                <p style="margin-bottom: 10px;">You can use this number to track your package with the courier.</p>
                <!-- Optionally, add a tracking link if available -->
            </div>
            <hr class="divider">
            <!-- Order Items and Totals -->
            @include('emails.orders.partials.order-summary', ['order' => $order])
            <hr class="divider">
            <p style="margin-top: 30px;">Thank you for shopping with us! We hope you enjoy your purchase.</p>
            <p style="color: #6c757d; font-size: 14px; margin-top: 20px;">
                <strong>Need help?</strong> Contact our customer service team at 
                <a href="mailto:support@nordflex.shop" style="color: #2c5aa0;">support@nordflex.shop</a> 
                or call us at +46 8 123 456 78.
            </p>
        </div>
        <!-- Footer -->
        <div class="footer">
            <p><strong>Nord Flex</strong></p>
            <p>Bringing you the finest Nordic-inspired fashion</p>
            <p>
                <a href="mailto:support@nordflex.shop">support@nordflex.shop</a> | 
                <a href="tel:+4681234567">+46 8 123 456 78</a>
            </p>
            <p style="margin-top: 15px; font-size: 12px; color: #ccc;">
                © {{ date('Y') }} Nord Flex. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
