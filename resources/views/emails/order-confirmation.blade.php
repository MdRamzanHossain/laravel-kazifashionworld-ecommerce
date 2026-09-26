<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Kazi Fashion World</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #1f2937; background-color: #fdf2f8; margin: 0; padding: 24px 12px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; padding: 32px 24px; border: 1px solid #fce7f3; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .header { text-align: center; border-bottom: 2px solid #db2777; padding-bottom: 20px; margin-bottom: 24px; }
        .brand-title { font-size: 20px; font-weight: 800; color: #831843; letter-spacing: 1px; margin: 8px 0 0 0; }
        .order-badge { display: inline-block; background-color: #fdf2f8; color: #db2777; font-weight: 800; padding: 4px 12px; border-radius: 9999px; font-size: 13px; margin-top: 8px; border: 1px solid #fbcfe8; }
        .table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .table th, .table td { padding: 12px 10px; border-bottom: 1px solid #f3f4f6; text-align: left; font-size: 13px; }
        .table th { background-color: #fdf2f8; color: #831843; font-weight: 700; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; }
        .summary-box { background-color: #fafafa; border: 1px solid #f3f4f6; border-radius: 12px; padding: 16px; margin: 20px 0; }
        .btn-track { display: inline-block; padding: 12px 24px; background: linear-gradient(135deg, #db2777 0%, #be185d 100%); color: #ffffff !important; text-decoration: none; font-size: 13px; font-weight: bold; border-radius: 10px; box-shadow: 0 2px 4px rgba(219,39,119,0.25); text-align: center; }
        .footer { margin-top: 28px; font-size: 12px; color: #9ca3af; text-align: center; border-top: 1px solid #f3f4f6; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="brand-title">KAZI FASHION WORLD</h1>
            <p style="color: #6b7280; font-size: 13px; margin: 4px 0 0 0;">Exclusive Beauty & Luxury Apparel</p>
            <div class="order-badge">Order #{{ $order->order_number }}</div>
        </div>

        <p style="font-size: 15px; margin: 0 0 12px 0;">Dear <strong>{{ $order->customer_name }}</strong>,</p>
        <p style="font-size: 13px; color: #4b5563; margin: 0 0 20px 0;">
            Thank you for shopping with us! Your order has been placed successfully and is being prepared with utmost care.
        </p>

        <table class="table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th style="text-align: center;">Qty</th>
                    <th style="text-align: right;">Unit Price</th>
                    <th style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->orderItems as $item)
                    <tr>
                        <td>
                            <strong style="color: #111827;">{{ $item->product_name }}</strong>
                        </td>
                        <td style="text-align: center; color: #4b5563;">{{ $item->quantity }}</td>
                        <td style="text-align: right; color: #4b5563;">BDT {{ number_format($item->price ?? $item->unit_price, 2) }}</td>
                        <td style="text-align: right; font-weight: 700; color: #111827;">BDT {{ number_format($item->total ?? ($item->price * $item->quantity), 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary-box">
            <table style="width: 100%; font-size: 13px; border-collapse: collapse;">
                <tr>
                    <td style="padding: 4px 0; color: #6b7280;">Subtotal:</td>
                    <td style="padding: 4px 0; text-align: right; font-weight: 600;">BDT {{ number_format($order->subtotal, 2) }}</td>
                </tr>
                @if($order->discount_amount > 0)
                <tr>
                    <td style="padding: 4px 0; color: #db2777; font-weight: 600;">
                        Promo Discount {{ $order->coupon_code ? '(' . $order->coupon_code . ')' : '' }}:
                    </td>
                    <td style="padding: 4px 0; text-align: right; color: #db2777; font-weight: 700;">- BDT {{ number_format($order->discount_amount, 2) }}</td>
                </tr>
                @endif
                <tr>
                    <td style="padding: 4px 0; color: #6b7280;">Delivery Fee:</td>
                    <td style="padding: 4px 0; text-align: right; font-weight: 600;">
                        {{ $order->shipping_fee == 0 ? 'FREE (BDT 0.00)' : 'BDT ' . number_format($order->shipping_fee, 2) }}
                    </td>
                </tr>
                <tr style="border-top: 1px solid #e5e7eb;">
                    <td style="padding: 10px 0 4px 0; font-size: 15px; font-weight: 800; color: #111827;">Total Payable:</td>
                    <td style="padding: 10px 0 4px 0; text-align: right; font-size: 16px; font-weight: 800; color: #db2777;">
                        BDT {{ number_format($order->grand_total ?? $order->total_amount, 2) }}
                    </td>
                </tr>
            </table>
        </div>

        <div style="background-color: #fdf2f8; border: 1px solid #fce7f3; border-radius: 12px; padding: 14px; margin-bottom: 20px; font-size: 13px;">
            <div style="margin-bottom: 6px;"><strong>Payment Method:</strong> {{ strtoupper($order->payment_method ?? 'COD') }} ({{ ucfirst($order->payment_status ?? 'Pending') }})</div>
            <div><strong>Delivery Address:</strong> {{ $order->shipping_address }}</div>
        </div>

        <div style="text-align: center; margin: 28px 0 10px 0;">
            <a href="{{ route('order.track', $order->order_number) }}" class="btn-track">
                Track Live Order Status &rarr;
            </a>
        </div>

        <div class="footer">
            <p>Need help? Contact our customer support team or reply directly to this email.</p>
            <p style="margin: 4px 0 0 0; font-weight: 600; color: #6b7280;">&copy; {{ date('Y') }} Kazi Fashion World. All rights reserved.</p>
        </div>
    </div>
</body>
</html>