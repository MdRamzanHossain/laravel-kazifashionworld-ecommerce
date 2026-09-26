<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $order->order_number }} - Kazi Fashion World</title>
    <style>
        @page {
            margin: 20mm 15mm;
        }
        body {
            font-family: 'DejaVu Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1f2937;
            font-size: 12px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .invoice-container {
            width: 100%;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .header-table td {
            vertical-align: top;
        }
        .brand-title {
            font-size: 20px;
            font-weight: 800;
            color: #831843;
            letter-spacing: 0.5px;
            margin: 0 0 4px 0;
        }
        .brand-subtitle {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #be185d;
            font-weight: 700;
            margin: 0 0 8px 0;
        }
        .company-info {
            font-size: 10px;
            color: #6b7280;
            line-height: 1.4;
        }
        .invoice-meta-title {
            font-size: 22px;
            font-weight: 900;
            color: #111827;
            text-align: right;
            margin: 0 0 4px 0;
        }
        .invoice-meta-text {
            font-size: 11px;
            color: #4b5563;
            text-align: right;
            margin: 0;
        }
        .divider {
            border: none;
            border-top: 1.5px solid #fce7f3;
            margin: 15px 0 20px 0;
        }
        .address-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .address-box {
            width: 48%;
            background-color: #fdf2f8;
            border: 1px solid #fbcfe8;
            border-radius: 8px;
            padding: 12px 14px;
            vertical-align: top;
        }
        .address-box-title {
            font-size: 10px;
            text-transform: uppercase;
            font-weight: 800;
            color: #9d174d;
            letter-spacing: 0.8px;
            margin-bottom: 6px;
        }
        .address-box-content {
            font-size: 11px;
            color: #374151;
            line-height: 1.45;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background-color: #fdf2f8;
            color: #831843;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.5px;
            padding: 10px 12px;
            border-bottom: 2px solid #fbcfe8;
            text-align: left;
        }
        .items-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 11px;
            color: #1f2937;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .summary-container {
            width: 100%;
            margin-top: 10px;
        }
        .totals-table {
            width: 45%;
            float: right;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 6px 10px;
            font-size: 11px;
        }
        .totals-label {
            color: #6b7280;
            text-align: left;
        }
        .totals-value {
            text-align: right;
            font-weight: 600;
            color: #111827;
        }
        .grand-total-row {
            border-top: 2px solid #db2777;
            border-bottom: 2px solid #db2777;
        }
        .grand-total-label {
            font-size: 13px;
            font-weight: 800;
            color: #831843;
            padding: 8px 10px !important;
        }
        .grand-total-value {
            font-size: 14px;
            font-weight: 900;
            color: #db2777;
            text-align: right;
            padding: 8px 10px !important;
        }
        .clear {
            clear: both;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .badge-paid {
            background-color: #d1fae5;
            color: #065f46;
        }
        .badge-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        .footer {
            margin-top: 40px;
            border-top: 1px solid #f3f4f6;
            padding-top: 15px;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Top Header & Brand Bar -->
        <table class="header-table">
            <tr>
                <td style="width: 55%;">
                    <h1 class="brand-title">KAZI FASHION WORLD</h1>
                    <p class="brand-subtitle">Authentic Beauty & Luxury Apparel</p>
                    <div class="company-info">
                        Dhaka, Bangladesh<br>
                        Support & WhatsApp: +880 1735-940279<br>
                        Website: www.kazifashionworld.com
                    </div>
                </td>
                <td style="width: 45%;">
                    <h2 class="invoice-meta-title">INVOICE</h2>
                    <p class="invoice-meta-text"><strong>Invoice No:</strong> #{{ $order->order_number }}</p>
                    <p class="invoice-meta-text"><strong>Order Date:</strong> {{ $order->created_at->format('M d, Y') }}</p>
                    <p class="invoice-meta-text">
                        <strong>Payment:</strong> {{ strtoupper($order->payment_method ?? 'COD') }} 
                        <span class="badge {{ $order->payment_status === 'paid' ? 'badge-paid' : 'badge-pending' }}">
                            {{ strtoupper($order->payment_status ?? 'PENDING') }}
                        </span>
                    </p>
                </td>
            </tr>
        </table>

        <hr class="divider">

        <!-- Billing & Shipping Information Cards -->
        <table class="address-table">
            <tr>
                <td class="address-box">
                    <div class="address-box-title">Billed & Shipped To:</div>
                    <div class="address-box-content">
                        <strong style="color: #111827; font-size: 12px;">{{ $order->customer_name }}</strong><br>
                        Phone: {{ $order->customer_phone }}<br>
                        @if($order->customer_email)
                            Email: {{ $order->customer_email }}<br>
                        @endif
                        Address: {{ $order->shipping_address }}
                    </div>
                </td>
                <td style="width: 4%;"></td>
                <td class="address-box">
                    <div class="address-box-title">Fulfillment & Delivery Details:</div>
                    <div class="address-box-content">
                        <strong>Order Status:</strong> {{ ucfirst($order->order_status ?? 'Processing') }}<br>
                        @if($order->courier_name)
                            <strong>Courier:</strong> {{ $order->courier_name }}<br>
                        @endif
                        @if($order->tracking_number)
                            <strong>Tracking / Consignment ID:</strong> {{ $order->tracking_number }}<br>
                        @endif
                        <strong>Dispatch Location:</strong> Central Hub, Dhaka
                    </div>
                </td>
            </tr>
        </table>

        <!-- Itemized Products Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 50%;">Product Details</th>
                    <th style="width: 12%;" class="text-center">Qty</th>
                    <th style="width: 16%;" class="text-right">Unit Price</th>
                    <th style="width: 17%;" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->orderItems as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $item->product_name }}</strong>
                        </td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-right">BDT {{ number_format($item->unit_price ?? $item->price, 2) }}</td>
                        <td class="text-right" style="font-weight: 700;">BDT {{ number_format($item->total_price ?? ($item->price * $item->quantity), 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals & Payment Summary -->
        <div class="summary-container">
            <table class="totals-table">
                <tr>
                    <td class="totals-label">Subtotal:</td>
                    <td class="totals-value">BDT {{ number_format($order->subtotal, 2) }}</td>
                </tr>

                @if(($order->discount_amount ?? 0) > 0)
                    <tr>
                        <td class="totals-label" style="color: #db2777;">
                            Discount {{ $order->coupon_code ? '(' . $order->coupon_code . ')' : '' }}:
                        </td>
                        <td class="totals-value" style="color: #db2777;">
                            - BDT {{ number_format($order->discount_amount, 2) }}
                        </td>
                    </tr>
                @endif

                <tr>
                    <td class="totals-label">Shipping / Delivery Fee:</td>
                    <td class="totals-value">
                        {{ ($order->shipping_fee ?? 0) == 0 ? 'FREE (BDT 0.00)' : 'BDT ' . number_format($order->shipping_fee, 2) }}
                    </td>
                </tr>

                <tr class="grand-total-row">
                    <td class="grand-total-label">Grand Total:</td>
                    <td class="grand-total-value">BDT {{ number_format($order->grand_total ?? $order->total_amount, 2) }}</td>
                </tr>
            </table>
            <div class="clear"></div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p style="margin: 0 0 4px 0;">This is a computer-generated invoice. No physical signature is required.</p>
            <p style="margin: 0; font-weight: 700;">Thank you for shopping with Kazi Fashion World &bull; www.kazifashionworld.com</p>
        </div>
    </div>
</body>
</html>