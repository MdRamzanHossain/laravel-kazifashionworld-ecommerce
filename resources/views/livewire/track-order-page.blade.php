<div class="max-w-4xl mx-auto space-y-8">
    <!-- Search Section -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 md:p-8">

        <div class="text-center max-w-xl mx-auto mb-6">
            <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 mb-2">Track Your Order</h1>
            <p class="text-gray-500 text-sm">Enter your Order Number (e.g. <span class="font-mono font-medium text-pink-600">ORD-6A8...</span>) or Phone Number to check real-time delivery status.</p>
        </div>

        <form wire:submit="searchOrder" class="max-w-xl mx-auto">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <input 
                        type="text" 
                        wire:model="orderNumber" 
                        placeholder="Order Number (e.g. ORD-6A8...)"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-pink-500 focus:border-pink-500 text-sm uppercase transition"
                    >
                </div>
                <div class="flex-1">
                    <input 
                        type="text" 
                        wire:model="phone" 
                        placeholder="Phone Number (e.g. 017...)"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-pink-500 focus:border-pink-500 text-sm transition"
                    >
                </div>
                <button 
                    type="submit" 
                    class="px-6 py-3 bg-pink-600 hover:bg-pink-700 text-white font-semibold rounded-xl text-sm transition flex items-center justify-center gap-2 shadow-sm"
                >
                    <svg wire:loading.remove wire:target="searchOrder" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <svg wire:loading wire:target="searchOrder" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span>Track</span>
                </button>
            </div>

            @error('search')
                <p class="text-red-500 text-xs mt-2 text-center">{{ $message }}</p>
            @enderror

            @if(session()->has('error'))
                <div class="mt-4 p-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg text-center">
                    {{ session('error') }}
                </div>
            @endif
        </form>
    </div>

    <!-- Order Tracking Result -->
    @if($order)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <!-- Order Header Banner -->
            <div class="bg-gradient-to-r from-pink-50 to-indigo-50 border-b border-gray-100 p-6 md:p-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <span class="text-xs font-semibold tracking-wider text-pink-600 uppercase">Order Details</span>
                    <h2 class="text-2xl font-bold text-gray-900 mt-1 flex items-center gap-2">
                        <span>{{ $order->order_number }}</span>
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">Placed on {{ $order->created_at->format('M d, Y \a\t h:i A') }}</p>
                </div>

                <div class="flex items-center gap-3">
                    <span class="text-xs text-gray-500 font-medium">Status:</span>
                    @php
                        $statusClass = match(strtolower((string)$order->order_status)) {
                            'pending'    => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                            'processing' => 'bg-blue-100 text-blue-800 border-blue-200',
                            'shipped'    => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                            'delivered'  => 'bg-green-100 text-green-800 border-green-200',
                            'cancelled'  => 'bg-red-100 text-red-800 border-red-200',
                            default      => 'bg-gray-100 text-gray-800 border-gray-200',
                        };
                    @endphp
                    <span class="px-4 py-1.5 rounded-full text-xs font-bold uppercase border {{ $statusClass }}">
                        {{ ucfirst(str_replace('_', ' ', $order->order_status)) }}
                    </span>
                </div>
            </div>

            <!-- Cancelled Order Banner -->
            @if($statusStep === -1)
                <div class="p-6 bg-red-50 border-b border-red-100 flex items-start gap-4">
                    <div class="p-2 bg-red-100 text-red-600 rounded-full">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-red-900">This Order Has Been Cancelled</h3>
                        <p class="text-sm text-red-700 mt-1">This order is no longer active. If you requested this cancellation or have questions about a refund, please contact our support team.</p>
                    </div>
                </div>
            @else
                <!-- Visual Status Timeline / Stepper -->
                <div class="p-6 md:p-10 border-b border-gray-100">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-gray-400 mb-8 text-center md:text-left">Delivery Progress</h3>

                    <div class="relative">
                        <!-- Connecting Progress Bar Line (Desktop) -->
                        <div class="hidden md:block absolute top-1/2 left-8 right-8 h-1 bg-gray-200 -translate-y-6 z-0">
                            @php
                                $progressWidth = match($statusStep) {
                                    1 => '0%',
                                    2 => '33.33%',
                                    3 => '66.66%',
                                    4 => '100%',
                                    default => '0%',
                                };
                            @endphp
                            <div class="h-full bg-pink-600 transition-all duration-700" style="width: {{ $progressWidth }}"></div>
                        </div>

                        <!-- Stepper Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 relative z-10">
                            <!-- Step 1: Order Placed -->
                            <div class="flex md:flex-col items-center md:text-center gap-4 md:gap-3">
                                <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold text-sm shrink-0 {{ $statusStep >= 1 ? 'bg-pink-600 text-white shadow-md ring-4 ring-pink-100' : 'bg-gray-200 text-gray-500' }}">
                                    @if($statusStep > 1)
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    @else
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    @endif
                                </div>
                                <div>
                                    <h4 class="font-bold text-sm {{ $statusStep >= 1 ? 'text-gray-900' : 'text-gray-400' }}">Order Placed</h4>
                                    <p class="text-xs text-gray-500 mt-0.5">Order received</p>
                                    <span class="text-[11px] text-pink-600 font-medium block mt-0.5">{{ $order->created_at->format('M d, h:i A') }}</span>
                                </div>
                            </div>

                            <!-- Step 2: Processing -->
                            <div class="flex md:flex-col items-center md:text-center gap-4 md:gap-3">
                                <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold text-sm shrink-0 {{ $statusStep >= 2 ? 'bg-pink-600 text-white shadow-md ring-4 ring-pink-100' : ($statusStep === 1 ? 'bg-white border-2 border-pink-600 text-pink-600 animate-pulse' : 'bg-gray-200 text-gray-500') }}">
                                    @if($statusStep > 2)
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    @else
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                    @endif
                                </div>
                                <div>
                                    <h4 class="font-bold text-sm {{ $statusStep >= 2 ? 'text-gray-900' : 'text-gray-400' }}">Processing</h4>
                                    <p class="text-xs text-gray-500 mt-0.5">Packing & quality check</p>
                                </div>
                            </div>

                            <!-- Step 3: Shipped -->
                            <div class="flex md:flex-col items-center md:text-center gap-4 md:gap-3">
                                <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold text-sm shrink-0 {{ $statusStep >= 3 ? 'bg-pink-600 text-white shadow-md ring-4 ring-pink-100' : ($statusStep === 2 ? 'bg-white border-2 border-gray-300 text-gray-400' : 'bg-gray-200 text-gray-500') }}">
                                    @if($statusStep > 3)
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    @else
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                        </svg>
                                    @endif
                                </div>
                                <div>
                                    <h4 class="font-bold text-sm {{ $statusStep >= 3 ? 'text-gray-900' : 'text-gray-400' }}">On The Way</h4>
                                    <p class="text-xs text-gray-500 mt-0.5">Handed to courier</p>
                                </div>
                            </div>

                            <!-- Step 4: Delivered -->
                            <div class="flex md:flex-col items-center md:text-center gap-4 md:gap-3">
                                <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold text-sm shrink-0 {{ $statusStep >= 4 ? 'bg-green-600 text-white shadow-md ring-4 ring-green-100' : 'bg-gray-200 text-gray-500' }}">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-bold text-sm {{ $statusStep >= 4 ? 'text-green-700' : 'text-gray-400' }}">Delivered</h4>
                                    <p class="text-xs text-gray-500 mt-0.5">Package received</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Dedicated Courier & Live Tracking Card -->
            @if($order->courier_name || $order->tracking_number || $order->tracking_url)
                <div class="mx-6 md:mx-8 my-6 p-5 bg-gradient-to-r from-indigo-50 to-pink-50 border border-indigo-100 rounded-2xl flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-indigo-600 text-white flex items-center justify-center shadow-md shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-semibold text-indigo-700 uppercase tracking-wider">Courier Dispatch</span>
                                @if($order->courier_name)
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-white text-indigo-900 border border-indigo-200">
                                        {{ $order->courier_name }}
                                    </span>
                                @endif
                            </div>
                            <h4 class="text-base font-bold text-gray-900 mt-0.5">
                                @if($order->tracking_number)
                                    Tracking ID: <span class="font-mono text-pink-600 font-semibold">{{ $order->tracking_number }}</span>
                                @else
                                    Package Dispatched via {{ $order->courier_name }}
                                @endif
                            </h4>
                        </div>
                    </div>

                    @if($order->tracking_url)
                        <a 
                            href="{{ $order->tracking_url }}" 
                            target="_blank" 
                            rel="noopener noreferrer"
                            class="w-full md:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl transition shadow-md hover:shadow-lg shrink-0"
                        >
                            <span>Track on {{ $order->courier_name ?: 'Courier' }} Website</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                    @endif
                </div>
            @endif

            <!-- Order Details & Summary Grid -->
            <div class="p-6 md:p-8 grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50/50">
                <!-- Shipping Info -->
                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Delivery Information</h3>
                    <p class="text-sm font-semibold text-gray-900">{{ $order->customer_name }}</p>
                    <p class="text-xs text-gray-600 mt-1 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        <span>{{ $order->customer_phone }}</span>
                    </p>
                    <p class="text-xs text-gray-600 mt-1 flex items-start gap-1.5">
                        <svg class="w-4 h-4 text-gray-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>{{ $order->shipping_address }}</span>
                    </p>
                </div>

                <!-- Payment Info -->
                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Payment Information</h3>
                    <div class="flex justify-between items-center text-xs py-1">
                        <span class="text-gray-500">Payment Method:</span>
                        <span class="font-semibold uppercase text-gray-900">{{ $order->payment_method ?? 'Cash on Delivery' }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs py-1">
                        <span class="text-gray-500">Payment Status:</span>
                        <span class="font-semibold uppercase {{ $order->payment_status === 'paid' ? 'text-green-600' : 'text-yellow-600' }}">
                            {{ $order->payment_status ?? 'Pending' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center text-xs py-1">
                        <span class="text-gray-500">Shipping Fee:</span>
                        <span class="font-semibold text-gray-900">BDT {{ number_format($order->shipping_fee, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm font-bold pt-2 border-t border-gray-100 mt-1">
                        <span class="text-gray-900">Total Amount:</span>
                        <span class="text-pink-600">BDT {{ number_format($order->grand_total ?? $order->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Items Ordered Breakdown -->
            <div class="p-6 md:p-8">
                <h3 class="text-base font-bold text-gray-900 mb-4">Items Ordered ({{ $order->orderItems->count() }})</h3>

                <div class="divide-y divide-gray-100 border border-gray-100 rounded-xl overflow-hidden">
                    @foreach($order->orderItems as $item)
                        <div class="p-4 flex items-center justify-between gap-4 hover:bg-gray-50/50 transition">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-pink-50 text-pink-600 flex items-center justify-center font-bold text-xs shrink-0">
                                    {{ $item->quantity }}x
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-900">{{ $item->product_name }}</h4>
                                    <p class="text-xs text-gray-500">Unit Price: BDT {{ number_format($item->unit_price ?? $item->price, 2) }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-bold text-gray-900">BDT {{ number_format($item->total ?? ($item->price * $item->quantity), 2) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Footer Action Buttons -->
            <div class="p-6 bg-gray-50 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <a href="{{ route('home') }}" wire:navigate class="text-xs font-semibold text-gray-600 hover:text-pink-600 transition flex items-center gap-1">
                    &larr; Return to Shopping
                </a>

                <a 
                    href="{{ route('order.invoice.download', $order->order_number) }}" 
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-gray-900 hover:bg-gray-800 text-white text-xs font-semibold rounded-xl transition shadow-sm"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Download PDF Invoice</span>
                </a>
            </div>
        </div>
    @elseif($searched)
        <div class="text-center py-12 bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <div class="w-16 h-16 bg-gray-100 text-gray-400 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-1">No Order Found</h3>
            <p class="text-sm text-gray-500 max-w-md mx-auto mb-6">We couldn't find an order with the details you entered. Please double-check your Order Number (e.g. from your SMS or email confirmation).</p>
            <a href="{{ route('home') }}" class="px-5 py-2.5 bg-pink-600 text-white rounded-xl text-xs font-semibold hover:bg-pink-700 transition">Browse Products</a>
        </div>
    @endif
</div>
