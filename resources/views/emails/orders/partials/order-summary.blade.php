<div style="margin-top: 30px;">
    <h3 style="color: #2c5aa0; margin-bottom: 10px;">Order Summary</h3>
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #f8f9fa;">
                <th align="left" style="padding: 8px; border-bottom: 1px solid #e9ecef;">Product</th>
                <th align="left" style="padding: 8px; border-bottom: 1px solid #e9ecef;">Variant</th>
                <th align="center" style="padding: 8px; border-bottom: 1px solid #e9ecef;">Qty</th>
                <th align="right" style="padding: 8px; border-bottom: 1px solid #e9ecef;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #e9ecef;">{{ $item->product_name }}</td>
                <td style="padding: 8px; border-bottom: 1px solid #e9ecef;">{{ $item->variant_name }}</td>
                <td align="center" style="padding: 8px; border-bottom: 1px solid #e9ecef;">{{ $item->quantity }}</td>
                <td align="right" style="padding: 8px; border-bottom: 1px solid #e9ecef;">€{{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div style="margin-top: 15px;">
        <strong>Subtotal:</strong> €{{ number_format($order->subtotal, 2) }}<br>
        <strong>Shipping:</strong> €{{ number_format($order->shipping, 2) }}<br>
        <strong>Tax (VAT 25%):</strong> €{{ number_format($order->tax, 2) }}<br>
        <strong>Total:</strong> €{{ number_format($order->total, 2) }}
    </div>
</div>
