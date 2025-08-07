<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - {{ $orderNumber }}</title>
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
        
        .order-details {
            margin: 20px 0;
        }
        
        .order-details h3 {
            color: #2c5aa0;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .item:last-child {
            border-bottom: none;
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-name {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .item-variant {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 3px;
        }
        
        .item-quantity {
            font-size: 14px;
            color: #6c757d;
        }
        
        .item-price {
            font-weight: 600;
            color: #2c5aa0;
            font-size: 16px;
        }
        
        .totals {
            background-color: #ffffff;
            border: 2px solid #2c5aa0;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .total-row:last-child {
            border-bottom: none;
            border-top: 2px solid #2c5aa0;
            padding-top: 15px;
            margin-top: 10px;
            font-weight: 600;
            font-size: 18px;
            color: #2c5aa0;
        }
        
        .shipping-info {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }
        
        .shipping-info h3 {
            color: #2c5aa0;
            margin-bottom: 15px;
        }
        
        .address {
            line-height: 1.5;
        }
        
        .status-badge {
            background-color: #28a745;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
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
            
            .order-summary, .totals, .shipping-info {
                padding: 15px;
            }
            
            .item {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
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
            <div class="brand">Nordic Fashion Store</div>
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
                <p style="margin-top: 15px; color: #6c757d;">Order Date: {{ $order->created_at->format('F j, Y \a\t g:i A') }}</p>
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
            
            <hr class="divider">
            
            <!-- Next Steps -->
            <div style="background-color: #e8f4f8; border-left: 4px solid #2c5aa0; padding: 20px; border-radius: 0 8px 8px 0; margin: 25px 0;">
                <h3 style="color: #2c5aa0; margin-top: 0;">What's Next?</h3>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>We'll process your order within 1-2 business days</li>
                    <li>You'll receive a shipping confirmation email with tracking information</li>
                    <li>Standard delivery takes 3-5 business days within Nordic countries</li>
                    <li>For any questions, please contact our customer service team</li>
                </ul>
            </div>
            
            <p style="margin-top: 30px;">Thank you for choosing Nordic Fashion Store. We appreciate your business and look forward to serving you again!</p>
            
            <p style="color: #6c757d; font-size: 14px; margin-top: 20px;">
                <strong>Need help?</strong> Contact our customer service team at 
                <a href="mailto:support@nordicskin.com" style="color: #2c5aa0;">support@nordicskin.com</a> 
                or call us at +46 8 123 456 78.
            </p>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p><strong>Nordic Fashion Store</strong></p>
            <p>Bringing you the finest Nordic-inspired fashion</p>
            <p>
                <a href="mailto:support@nordicskin.com">support@nordicskin.com</a> | 
                <a href="tel:+4681234567">+46 8 123 456 78</a>
            </p>
            <p style="margin-top: 15px; font-size: 12px; color: #ccc;">
                © {{ date('Y') }} Nordic Fashion Store. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
