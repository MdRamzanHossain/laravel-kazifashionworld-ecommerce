<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Status Update - Kazi Fashion World</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #1f2937; background-color: #fdf2f8; margin: 0; padding: 24px 12px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; padding: 32px 24px; border: 1px solid #fce7f3; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .header { text-align: center; border-bottom: 2px solid #db2777; padding-bottom: 20px; margin-bottom: 24px; }
        .brand-title { font-size: 20px; font-weight: 800; color: #831843; letter-spacing: 1px; margin: 8px 0 0 0; }
        .badge { display: inline-block; padding: 6px 16px; font-size: 13px; font-weight: 800; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.5px; }
        .status-pending { background-color: #fef3c7; color: #92400e; }
        .status-processing { background-color: #dbeafe; color: #1e40af; }
        .status-shipped { background-color: #e0e7ff; color: #3730a3; }
        .status-delivered { background-color: #d1fae5; color: #065f46; }
        .status-cancelled { background-color: #fee2e2; color: #991b1b; }
        .status-box { background-color: #fdf2f8; border: 1px solid #fbcfe8; border-radius: 12px; padding: 18px; margin: 20px 0; text-align: center; }
        .tracking-card { margin: 20px 0; padding: 20px; background: linear-gradient(135deg, #eef2ff 0%, #fdf2f8 100%); border: 1px solid #c7d2fe; border-radius: 12px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        .table th, .table td { padding: 10px; border-bottom: 1px solid #f3f4f6; text-align: left; font-size: 13px; }
        .table th { background-color: #f9fafb; color: #4b5563; font-weight: 600; }
        .btn-track { display: inline-block; padding: 12px 24px; background: linear-gradient(135deg, #db2777 0%, #be185d 100%); color: #ffffff !important; text-decoration: none; font-size: 13px; font-weight: bold; border-radius: 10px; box-shadow: 0 2px 4px rgba(219,39,119,0.25); text-align: center; }
        .footer { margin-top: 28px; font-size: 12px; color: #9ca3af; text-align: center; border-top: 1px solid #f3f4f6; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="brand-title">KAZI FASHION WORLD</h1>
            <p style="color: #6b7280; font-size: 13px; margin: 4px 0 0 0;">Exclusive Beauty & Luxury Apparel</p>
            <p style="margin: 8px 0 0 0; color: #4b5563; font-size: 13px;">Order #<strong>{{ $order->order_number }}</strong></p>
        </div>

        <p style="font-size: 15px; margin: 0 0 12px 0;">Dear <strong>{{ $order->customer_name }}</strong>,</p>
        <p style="font-size: 13px; color: #4b5563; margin: 0 0 16px 0;">
            Your order status has been updated. Here are the latest details:
        </p>

        <div class="status-box">
            <p style="margin: 0 0 8px 0; font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; font-weight: bold;">Current Status</p>
            <span class="badge status-{{ strtolower($newStatus) }}">
                {{ ucfirst(str_replace('_', ' ', $newStatus)) }}
            </span>
        </div>

        @if($newStatus === 'processing')
            <p style="font-size: 13px; color: #374151;">Your order has been confirmed and is currently being packed and quality-checked by our team.</p>
        @elseif($newStatus === 'shipped')
            <p style="font-size: 13px; color: #374151;">Great news! Your package has been handed over to our delivery partner and is currently on its way to your destination.</p>
        @elseif($newStatus === 'delivered')
            <p style="font-size: 13px; color: #374151;">Your order has been successfully delivered! We hope you love your selections.</p>
        @elseif($newStatus === 'cancelled')
            <p style="font-size: 13px; color: #374151;">Your order has been marked as cancelled. If this was unexpected, please contact our support team.</p>
        @endif

        <!-- Prominent Courier & Tracking Card -->
        @if($order->courier_name || $order->tracking_number || $order->tracking_url)
            <div class="tracking-card">
                <h4 style="margin: 0 0 10px 0; color: #3730a3; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: bold;">
                    📦 Courier & Shipment Details
                </h4>

                @if($order->courier_name)
                    <p style="margin: 4px 0; font-size: 13px; color: #1e1b4b;">
                        <strong>Courier Partner:</strong> {{ $order->courier_name }}
                    </p>
                @endif

                @if($order->tracking_number)
                    <p style="margin: 6px 0; font-size: 13px; color: #1e1b4b;">
                        <strong>Tracking Code / Consignment ID:</strong> 
                        <span style="font-family: monospace; background-color: #ffffff; padding: 2px 8px; border: 1px solid #cbd5e1; border-radius: 4px; color: #db2777; font-weight: bold;">
                            {{ $order->tracking_number }}
                        </span>
                    </p>
                @endif

                <div style="margin-top: 14px;">
                    @if($order->tracking_url)
                        <a href="{{ $order->tracking_url }}" target="_blank" style="display: inline-block; padding: 10px 18px; background-color: #4f46e5; color: #ffffff; text-decoration: none; font-size: 12px; font-weight: bold; border-radius: 6px; margin-right: 8px; margin-bottom: 6px;">
                            Track on {{ $order->courier_name ?: 'Courier' }} Website &rarr;
                        </a>
                    @endif

                    <a href="{{ route('order.track', $order->order_number) }}" style="display: inline-block; padding: 10px 18px; background-color: #db2777; color: #ffffff; text-decoration: none; font-size: 12px; font-weight: bold; border-radius: 6px; margin-bottom: 6px;">
                        Live Order Timeline &rarr;
                    </a>
                </div>
            </div>
        @endif

        <h3 style="font-size: 14px; margin-top: 24px; color: #111827; text-transform: uppercase; letter-spacing: 0.5px;">Order Summary</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th style="text-align: center;">Qty</th>
                    <th style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->orderItems as $item)
                    <tr>
                        <td>{{ $item->product_name }}</td>
                        <td style="text-align: center;">{{ $item->quantity }}</td>
                        <td style="text-align: right;">BDT {{ number_format($item->total ?? ($item->price * $item->quantity), 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top: 16px; text-align: right; font-size: 13px;">
            <p style="margin: 4px 0;"><strong>Grand Total:</strong> <span style="color: #db2777; font-weight: 800;">BDT {{ number_format($order->grand_total ?? $order->total_amount, 2) }}</span></p>
            <p style="margin: 4px 0; font-size: 12px; color: #6b7280;">Payment Method: {{ strtoupper($order->payment_method ?? 'COD') }}</p>
        </div>

        <div style="text-align: center; margin: 28px 0 10px 0;">
            <a href="{{ route('order.track', $order->order_number) }}" class="btn-track">
                Track Live Order Status &rarr;
            </a>
        </div>

        <div class="footer">
            <p>Thank you for choosing Kazi Fashion World! For questions, reply directly to this email.</p>
            <p style="margin: 4px 0 0 0; font-weight: 600; color: #6b7280;">&copy; {{ date('Y') }} Kazi Fashion World. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
