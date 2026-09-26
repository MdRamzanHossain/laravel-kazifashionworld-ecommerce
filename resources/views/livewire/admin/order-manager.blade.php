<div class="max-w-7xl mx-auto my-10 px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Order Management</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">View and update customer order statuses</p>
        </div>

        <!-- Search & Filter Controls -->
        <div class="flex flex-col sm:flex-row items-center gap-3">
            <input 
                type="text" 
                wire:model.live.debounce.300ms="search" 
                placeholder="Search order #, name, phone..." 
                class="w-full sm:w-64 text-sm rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white"
            >

            <select 
                wire:model.live="statusFilter" 
                class="w-full sm:w-44 text-sm rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white"
            >
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="shipped">Shipped</option>
                <option value="delivered">Delivered</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="mb-4 p-4 rounded-lg bg-green-100 text-green-700 text-sm dark:bg-green-900/30 dark:text-green-400">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-4 rounded-lg bg-red-100 text-red-700 text-sm dark:bg-red-900/30 dark:text-red-400">
            {{ session('error') }}
        </div>
    @endif

    <!-- Orders Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs uppercase text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-3">Order Number</th>
                        <th class="px-6 py-3">Customer</th>
                        <th class="px-6 py-3">Total Amount</th>
                        <th class="px-6 py-3">Payment</th>
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($orders as $order)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                            <td class="px-6 py-4 font-mono font-medium text-gray-900 dark:text-white">
                                {{ $order->order_number }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900 dark:text-white">{{ $order->customer_name }}</div>
                                <div class="text-xs text-gray-500">{{ $order->customer_phone }}</div>
                            </td>
                            <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">
                                BDT {{ number_format($order->grand_total, 2) }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="uppercase text-xs font-semibold px-2 py-1 rounded bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                    {{ $order->payment_method }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs whitespace-nowrap">
                                {{ $order->created_at->format('M d, Y h:i A') }}
                            </td>
                            <td class="px-6 py-4">
                                <!-- Status Selector Dropdown -->
                                <select 
                                    wire:change="updateStatus({{ $order->id }}, $event.target.value)"
                                    class="text-xs font-semibold rounded-full px-3 py-1 border-0 ring-1 ring-inset focus:ring-2 focus:ring-indigo-500 cursor-pointer
                                        @if($order->order_status === 'pending') bg-amber-50 text-amber-700 ring-amber-600/20
                                        @elseif($order->order_status === 'processing') bg-blue-50 text-blue-700 ring-blue-600/20
                                        @elseif($order->order_status === 'shipped') bg-purple-50 text-purple-700 ring-purple-600/20
                                        @elseif($order->order_status === 'delivered') bg-green-50 text-green-700 ring-green-600/20
                                        @elseif($order->order_status === 'cancelled') bg-red-50 text-red-700 ring-red-600/20
                                        @endif"
                                >
                                    <option value="pending" @selected($order->order_status === 'pending')>Pending</option>
                                    <option value="processing" @selected($order->order_status === 'processing')>Processing</option>
                                    <option value="shipped" @selected($order->order_status === 'shipped')>Shipped</option>
                                    <option value="delivered" @selected($order->order_status === 'delivered')>Delivered</option>
                                    <option value="cancelled" @selected($order->order_status === 'cancelled')>Cancelled</option>
                                </select>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a 
                                    href="{{ route('order.success', $order->order_number) }}" 
                                    target="_blank" 
                                    class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 text-xs font-semibold"
                                >
                                    View Invoice &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                No orders found matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $orders->links() }}
        </div>
    </div>
</div>