<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Order Received</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background: #f9f9f9;
            padding: 30px;
            border: 1px solid #ddd;
        }
        .order-info {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #d4af37;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: bold;
            color: #666;
        }
        .value {
            color: #333;
        }
        .button {
            display: inline-block;
            background: #d4af37;
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .button:hover {
            background: #b8941f;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
        }
        .items-table th {
            background: #f5f5f5;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #ddd;
        }
        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        .total {
            font-size: 20px;
            font-weight: bold;
            color: #d4af37;
            text-align: right;
            margin-top: 20px;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🛍️ New Order Received!</h1>
        <p>Order #{{ $order->id }}</p>
    </div>

    <div class="content">
        <p>You have received a new order. Please review and process it.</p>

        <div class="order-info">
            <h3>Customer Information</h3>
            <div class="info-row">
                <span class="label">Name:</span>
                <span class="value">{{ $order->shipping_name }}</span>
            </div>
            <div class="info-row">
                <span class="label">Email:</span>
                <span class="value">{{ $order->shipping_email }}</span>
            </div>
            <div class="info-row">
                <span class="label">Phone:</span>
                <span class="value">{{ $order->shipping_phone ?? 'N/A' }}</span>
            </div>
        </div>

        <div class="order-info">
            <h3>Shipping Address</h3>
            <div class="info-row">
                <span class="label">Address:</span>
                <span class="value">{{ $order->shipping_address }}</span>
            </div>
            <div class="info-row">
                <span class="label">City:</span>
                <span class="value">{{ $order->shipping_city }}</span>
            </div>
            <div class="info-row">
                <span class="label">Postal Code:</span>
                <span class="value">{{ $order->shipping_postal_code }}</span>
            </div>
            <div class="info-row">
                <span class="label">Country:</span>
                <span class="value">{{ $order->shipping_country }}</span>
            </div>
        </div>

        <h3>Order Items</h3>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>
                        {{ $item->product_name }}<br>
                        <small style="color: #666;">{{ $item->variant_color }} - {{ $item->variant_size }}</small>
                    </td>
                    <td>{{ $item->quantity }}</td>
                    <td>€{{ number_format($item->price, 2) }}</td>
                    <td>€{{ number_format($item->quantity * $item->price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="total">
            Total: €{{ number_format($order->total_amount, 2) }}
        </div>

        <div style="text-align: center;">
            <a href="https://nordflex.store/admin" class="button">
                View in Admin Dashboard
            </a>
        </div>

        <div class="order-info">
            <h3>Order Details</h3>
            <div class="info-row">
                <span class="label">Payment Method:</span>
                <span class="value">{{ ucfirst($order->payment_method) }}</span>
            </div>
            <div class="info-row">
                <span class="label">Payment Status:</span>
                <span class="value">{{ ucfirst($order->payment_status) }}</span>
            </div>
            <div class="info-row">
                <span class="label">Order Date:</span>
                <span class="value">{{ $order->created_at->format('F d, Y H:i') }}</span>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>This is an automated notification from NordFlex</p>
        <p>&copy; {{ date('Y') }} NordFlex. All rights reserved.</p>
    </div>
</body>
</html>
