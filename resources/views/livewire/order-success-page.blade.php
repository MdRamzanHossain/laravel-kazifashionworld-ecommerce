<div class="max-w-4xl mx-auto space-y-8 bg-white p-6 sm:p-10 rounded-3xl shadow-sm border border-gray-100">
    <!-- Meta Pixel / GA4 Purchase Tracking -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let orderTotal = {{ $order->grand_total }};
            let orderId = '{{ $order->order_number }}';

            if (typeof fbq === 'function') {
                fbq('track', 'Purchase', {
                    value: orderTotal,
                    currency: 'BDT',
                    content_ids: {!! json_encode($order->orderItems->pluck('product_id')->toArray()) !!},
                    content_type: 'product'
                }, { eventID: orderId });
            }

            if (typeof gtag === 'function') {
                gtag('event', 'purchase', {
                    transaction_id: orderId,
                    value: orderTotal,
                    currency: 'BDT',
                    tax: 0,
                    shipping: {{ $order->shipping_fee }},
                    items: {!! json_encode($order->orderItems->map(function($item) {
                        return [
                            'item_id' => $item->product_id,
                            'item_name' => $item->product_name,
                            'price' => $item->unit_price,
                            'quantity' => $item->quantity
                        ];
                    })->toArray()) !!}
                });
            }
        });
    </script>
    <!-- Success Banner -->
    <div class="text-center pb-8 border-b border-gray-100 space-y-3">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-emerald-50 text-emerald-600 rounded-2xl shadow-sm">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Thank You for Your Order!</h1>
        <p class="text-xs sm:text-sm text-gray-500">
            Order Number: <span class="font-mono font-bold text-brand-600">{{ $order->order_number }}</span>
        </p>
        <div class="inline-block bg-brand-50 text-brand-700 text-xs font-semibold px-3 py-1 rounded-full">
            Confirmation email & SMS have been dispatched!
        </div>
    </div>

    <!-- Order Summary Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Customer & Delivery Details -->
        <div class="p-5 bg-gray-50 rounded-2xl border border-gray-100 space-y-2 text-xs">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Delivery Information</h3>
            <p class="text-sm font-bold text-gray-900">{{ $order->customer_name }}</p>
            <p class="text-gray-600">📱 {{ $order->customer_phone }}</p>
            <p class="text-gray-600">📍 {{ $order->shipping_address }}</p>
            <div class="pt-2">
                <span class="text-gray-500">Payment Mode: </span>
                <span class="font-bold text-gray-900 uppercase">{{ $order->payment_method ?? 'Cash on Delivery' }}</span>
            </div>
        </div>

        <!-- Payment Breakdown -->
        <div class="p-5 bg-gray-50 rounded-2xl border border-gray-100 space-y-2 text-xs">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Payment Summary</h3>
            <div class="flex justify-between py-1 text-gray-600">
                <span>Subtotal:</span>
                <span class="font-bold text-gray-900">BDT {{ number_format($order->subtotal, 2) }}</span>
            </div>
            <div class="flex justify-between py-1 text-gray-600">
                <span>Delivery Fee:</span>
                <span class="font-bold text-gray-900">BDT {{ number_format($order->shipping_fee, 2) }}</span>
            </div>
            <div class="flex justify-between items-center text-sm font-extrabold border-t border-gray-200 pt-2 text-gray-900">
                <span>Total Amount:</span>
                <span class="text-brand-600 text-base">BDT {{ number_format($order->grand_total ?? $order->total_amount, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Items List -->
    <div class="space-y-3">
        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Ordered Items ({{ $order->orderItems->count() }})</h3>
        <div class="divide-y divide-gray-100 border border-gray-100 rounded-2xl overflow-hidden text-xs">
            @foreach($order->orderItems as $item)
                <div class="p-4 flex justify-between items-center hover:bg-gray-50/50 transition">
                    <div>
                        <h4 class="font-bold text-gray-900">{{ $item->product_name }}</h4>
                        <p class="text-gray-500">Qty: {{ $item->quantity }} &times; BDT {{ number_format($item->unit_price ?? $item->price, 2) }}</p>
                    </div>
                    <span class="font-extrabold text-gray-900">BDT {{ number_format($item->total_price ?? ($item->price * $item->quantity), 2) }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="pt-4 border-t border-gray-100 flex flex-wrap items-center justify-center gap-3 text-xs">
        <a 
            href="{{ route('order.track', $order->order_number) }}" 
            wire:navigate
            class="px-6 py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl shadow-md transition flex items-center gap-2"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span>Track Live Delivery &rarr;</span>
        </a>

        <a 
            href="{{ route('order.invoice.download', $order->order_number) }}" 
            class="px-5 py-3 bg-gray-900 hover:bg-gray-800 text-white font-bold rounded-xl transition flex items-center gap-2"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span>Download Invoice PDF</span>
        </a>

        <a href="{{ route('home') }}" wire:navigate class="px-5 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition">
            Continue Shopping
        </a>
    </div>
</div>
