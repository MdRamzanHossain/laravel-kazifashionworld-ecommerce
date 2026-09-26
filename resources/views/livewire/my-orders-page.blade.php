<div class="space-y-8">
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-100 pb-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Order History</h1>
            <p class="text-xs text-gray-500 mt-1">Track live parcel delivery and review your past purchases</p>
        </div>
        <a href="{{ route('home') }}" wire:navigate class="text-xs font-bold text-brand-600 hover:text-brand-700 transition">
            &larr; Back to Shop
        </a>
    </div>

    @if($orders->isEmpty())
        <div class="text-center py-16 bg-white rounded-3xl border border-gray-100 shadow-sm p-8 space-y-4 max-w-lg mx-auto">
            <div class="w-20 h-20 bg-brand-50 text-brand-600 rounded-full flex items-center justify-center mx-auto text-3xl">
                🛍️
            </div>
            <h3 class="text-lg font-bold text-gray-900">No orders found</h3>
            <p class="text-xs text-gray-500 max-w-xs mx-auto">You haven't placed any orders yet. Discover our collection of authentic beauty & fashion!</p>
            <div class="pt-2">
                <a href="{{ route('home') }}" wire:navigate class="inline-block px-6 py-3 bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold rounded-2xl shadow-md transition">
                    Start Shopping &rarr;
                </a>
            </div>
        </div>
    @else
        <div class="space-y-6">
            @foreach($orders as $order)
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <!-- Order Header -->
                    <div class="bg-gray-50/80 px-6 py-4 flex flex-wrap items-center justify-between gap-4 border-b border-gray-100 text-xs">
                        <div class="flex flex-wrap items-center gap-6">
                            <div>
                                <span class="block text-[10px] uppercase font-bold tracking-wider text-gray-400">Order Placed</span>
                                <span class="font-bold text-gray-900">{{ $order->created_at->format('M d, Y') }}</span>
                            </div>
                            <div>
                                <span class="block text-[10px] uppercase font-bold tracking-wider text-gray-400">Order #</span>
                                <span class="font-mono font-bold text-brand-600">{{ $order->order_number }}</span>
                            </div>
                            <div>
                                <span class="block text-[10px] uppercase font-bold tracking-wider text-gray-400">Total Amount</span>
                                <span class="font-extrabold text-gray-900">BDT {{ number_format($order->grand_total ?? $order->total_amount, 2) }}</span>
                            </div>
                        </div>

                        <!-- Status Badges & Quick Action -->
                        <div class="flex items-center gap-2">
                            <span class="px-3 py-1 text-[10px] font-bold rounded-full uppercase {{ match($order->order_status) {
                                'delivered'  => 'bg-emerald-100 text-emerald-800',
                                'shipped'    => 'bg-blue-100 text-blue-800',
                                'processing' => 'bg-amber-100 text-amber-800',
                                'cancelled'  => 'bg-red-100 text-red-800',
                                default      => 'bg-gray-200 text-gray-800',
                            } }}">
                                {{ $order->order_status }}
                            </span>

                            <a href="{{ route('order.track', $order->order_number) }}" wire:navigate class="px-3 py-1.5 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl text-xs transition shadow-sm">
                                Track &rarr;
                            </a>
                        </div>
                    </div>

                    <!-- Items List -->
                    <div class="px-6 py-4 divide-y divide-gray-100 text-xs">
                        @foreach($order->orderItems as $item)
                            <div class="py-3 flex justify-between items-center text-gray-700">
                                <div>
                                    <h4 class="font-bold text-gray-900">{{ $item->product_name }}</h4>
                                    <p class="text-gray-400">Qty: {{ $item->quantity }} &times; BDT {{ number_format($item->unit_price ?? $item->price, 2) }}</p>
                                </div>
                                <span class="font-extrabold text-gray-900">
                                    BDT {{ number_format($item->total_price ?? ($item->price * $item->quantity), 2) }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <!-- Footer Details -->
                    <div class="px-6 py-3 bg-gray-50/50 border-t border-gray-100 flex flex-wrap items-center justify-between gap-3 text-xs">
                        <span class="text-gray-500">Payment: <strong class="uppercase text-gray-800">{{ $order->payment_method ?? 'COD' }}</strong> ({{ ucfirst($order->payment_status ?? 'pending') }})</span>
                        <a href="{{ route('order.invoice.download', $order->order_number) }}" class="text-brand-600 hover:text-brand-700 font-bold">
                            Download Invoice PDF &darr;
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination Links -->
        <div class="pt-4">
            {{ $orders->links() }}
        </div>
    @endif
</div>
