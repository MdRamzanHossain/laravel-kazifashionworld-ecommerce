<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSLCommerz Payment Gateway (Sandbox Simulator)</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
        <!-- Gateway Header -->
        <div class="bg-gradient-to-r from-pink-600 to-indigo-600 p-6 text-white text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 bg-white/20 rounded-full mb-3 backdrop-blur-sm">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
            </div>
            <h2 class="text-xl font-bold">SSLCommerz Payment Gateway</h2>
            <p class="text-xs text-pink-100 mt-1">Sandbox Test & Simulation Environment</p>
        </div>

        <!-- Order Summary Box -->
        <div class="p-6 border-b border-gray-100 bg-gray-50/50">
            <div class="flex justify-between items-center text-sm py-1">
                <span class="text-gray-500">Order Number:</span>
                <span class="font-mono font-bold text-gray-900">{{ $order->order_number }}</span>
            </div>
            <div class="flex justify-between items-center text-sm py-1">
                <span class="text-gray-500">Customer:</span>
                <span class="font-medium text-gray-900">{{ $order->customer_name }}</span>
            </div>
            <div class="flex justify-between items-center text-sm py-1">
                <span class="text-gray-500">Phone:</span>
                <span class="font-medium text-gray-900">{{ $order->customer_phone }}</span>
            </div>
            <div class="flex justify-between items-center text-base font-bold pt-3 border-t border-gray-200 mt-2">
                <span class="text-gray-900">Total Amount:</span>
                <span class="text-pink-600 text-lg">BDT {{ number_format($order->grand_total ?? $order->total_amount, 2) }}</span>
            </div>
        </div>

        <!-- Payment Actions -->
        <div class="p-6 space-y-3">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider text-center mb-4">Choose a Test Payment Method</p>

            <!-- bKash Simulation Form -->
            <form action="{{ route('payment.sslcommerz.success') }}" method="POST">
                @csrf
                <input type="hidden" name="tran_id" value="{{ $tranId }}">
                <input type="hidden" name="val_id" value="MOCK-VAL-BKASH-{{ time() }}">
                <input type="hidden" name="amount" value="{{ $order->grand_total ?? $order->total_amount }}">
                <input type="hidden" name="card_type" value="bKash-Payment">
                <input type="hidden" name="card_brand" value="bKash">
                <input type="hidden" name="status" value="VALID">
                <input type="hidden" name="is_mock" value="1">
                <button type="submit" class="w-full py-3 px-4 bg-[#e2136e] hover:bg-[#c90f61] text-white font-bold rounded-xl flex items-center justify-between transition shadow-sm">
                    <span class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-white animate-ping"></span>
                        <span>Pay with bKash</span>
                    </span>
                    <span class="text-xs bg-white/20 px-2 py-0.5 rounded">Simulate Success &rarr;</span>
                </button>
            </form>

            <!-- Nagad Simulation Form -->
            <form action="{{ route('payment.sslcommerz.success') }}" method="POST">
                @csrf
                <input type="hidden" name="tran_id" value="{{ $tranId }}">
                <input type="hidden" name="val_id" value="MOCK-VAL-NAGAD-{{ time() }}">
                <input type="hidden" name="amount" value="{{ $order->grand_total ?? $order->total_amount }}">
                <input type="hidden" name="card_type" value="Nagad-Payment">
                <input type="hidden" name="card_brand" value="Nagad">
                <input type="hidden" name="status" value="VALID">
                <input type="hidden" name="is_mock" value="1">
                <button type="submit" class="w-full py-3 px-4 bg-[#f7931e] hover:bg-[#de7e12] text-white font-bold rounded-xl flex items-center justify-between transition shadow-sm">
                    <span>Pay with Nagad</span>
                    <span class="text-xs bg-white/20 px-2 py-0.5 rounded">Simulate Success &rarr;</span>
                </button>
            </form>

            <!-- Card Simulation Form -->
            <form action="{{ route('payment.sslcommerz.success') }}" method="POST">
                @csrf
                <input type="hidden" name="tran_id" value="{{ $tranId }}">
                <input type="hidden" name="val_id" value="MOCK-VAL-VISA-{{ time() }}">
                <input type="hidden" name="amount" value="{{ $order->grand_total ?? $order->total_amount }}">
                <input type="hidden" name="card_type" value="VISA-Card">
                <input type="hidden" name="card_brand" value="VISA">
                <input type="hidden" name="status" value="VALID">
                <input type="hidden" name="is_mock" value="1">
                <button type="submit" class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl flex items-center justify-between transition shadow-sm">
                    <span>Pay with Visa / MasterCard</span>
                    <span class="text-xs bg-white/20 px-2 py-0.5 rounded">Simulate Success &rarr;</span>
                </button>
            </form>

            <div class="grid grid-cols-2 gap-3 pt-2">
                <!-- Fail Simulation -->
                <form action="{{ route('payment.sslcommerz.fail') }}" method="POST">
                    @csrf
                    <input type="hidden" name="tran_id" value="{{ $tranId }}">
                    <input type="hidden" name="status" value="FAILED">
                    <button type="submit" class="w-full py-2.5 px-3 bg-red-50 hover:bg-red-100 text-red-600 text-xs font-semibold rounded-xl border border-red-200 transition">
                        Simulate Failure
                    </button>
                </form>

                <!-- Cancel Simulation -->
                <form action="{{ route('payment.sslcommerz.cancel') }}" method="POST">
                    @csrf
                    <input type="hidden" name="tran_id" value="{{ $tranId }}">
                    <input type="hidden" name="status" value="CANCELLED">
                    <button type="submit" class="w-full py-2.5 px-3 bg-gray-50 hover:bg-gray-100 text-gray-600 text-xs font-semibold rounded-xl border border-gray-200 transition">
                        Cancel Payment
                    </button>
                </form>
            </div>
        </div>

        <!-- Footer -->
        <div class="p-4 bg-gray-50 border-t border-gray-100 text-center text-xs text-gray-400">
            SSLCommerz Test Sandbox &bull; Secure Encrypted Checkout
        </div>
    </div>
</body>
</html>
