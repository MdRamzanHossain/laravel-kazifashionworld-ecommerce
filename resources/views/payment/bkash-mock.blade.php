<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>bKash Payment - Tokenized Checkout Sandbox</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#f0f2f5] min-h-screen flex items-center justify-center p-4">
    <div class="max-w-sm w-full bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-200">
        <!-- bKash Header -->
        <div class="bg-[#e2136e] p-6 text-white text-center relative">
            <div class="flex items-center justify-center gap-2 mb-1">
                <span class="text-2xl font-black tracking-wider">bKash</span>
            </div>
            <p class="text-xs text-pink-100 font-medium">Merchant Checkout (Sandbox)</p>
            <div class="mt-4 bg-white/10 backdrop-blur-md rounded-xl p-3 text-left flex justify-between items-center">
                <div>
                    <span class="text-[11px] text-pink-200 block">Merchant Invoice</span>
                    <span class="text-xs font-mono font-bold">{{ $order->order_number }}</span>
                </div>
                <div class="text-right">
                    <span class="text-[11px] text-pink-200 block">Amount</span>
                    <span class="text-sm font-bold">BDT {{ number_format($order->grand_total ?? $order->total_amount, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Simulator Content -->
        <div class="p-6 space-y-4">
            <div class="bg-pink-50 border border-pink-200 rounded-xl p-3.5 text-xs text-pink-900">
                <p class="font-bold flex items-center gap-1.5 mb-1">
                    <svg class="w-4 h-4 text-[#e2136e]" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <span>bKash Tokenized Sandbox Mode</span>
                </p>
                <p>Simulate OTP verification and PIN authorization to test immediate payment execution.</p>
            </div>

            <!-- Simulation Action Forms -->
            <form action="{{ route('payment.bkash.callback') }}" method="GET" class="space-y-3">
                <input type="hidden" name="paymentID" value="{{ $paymentID }}">
                <input type="hidden" name="order_number" value="{{ $order->order_number }}">
                <input type="hidden" name="status" value="success">
                <input type="hidden" name="amount" value="{{ $order->grand_total ?? $order->total_amount }}">
                <input type="hidden" name="customerMsisdn" value="{{ $order->customer_phone ?: '01735940279' }}">
                <input type="hidden" name="is_mock" value="1">

                <button type="submit" class="w-full py-3.5 px-4 bg-[#e2136e] hover:bg-[#c90f61] text-white font-bold rounded-xl flex items-center justify-center gap-2 transition shadow-md">
                    <span>Authorize & Confirm Payment</span>
                    <span class="text-xs bg-white/20 px-2 py-0.5 rounded">&rarr;</span>
                </button>
            </form>

            <div class="grid grid-cols-2 gap-2.5 pt-1">
                <!-- Fail Button -->
                <form action="{{ route('payment.bkash.callback') }}" method="GET">
                    <input type="hidden" name="paymentID" value="{{ $paymentID }}">
                    <input type="hidden" name="order_number" value="{{ $order->order_number }}">
                    <input type="hidden" name="status" value="failure">
                    <button type="submit" class="w-full py-2 px-3 bg-red-50 hover:bg-red-100 text-red-600 text-xs font-semibold rounded-xl border border-red-200 transition">
                        Simulate Fail
                    </button>
                </form>

                <!-- Cancel Button -->
                <form action="{{ route('payment.bkash.callback') }}" method="GET">
                    <input type="hidden" name="paymentID" value="{{ $paymentID }}">
                    <input type="hidden" name="order_number" value="{{ $order->order_number }}">
                    <input type="hidden" name="status" value="cancel">
                    <button type="submit" class="w-full py-2 px-3 bg-gray-50 hover:bg-gray-100 text-gray-600 text-xs font-semibold rounded-xl border border-gray-200 transition">
                        Cancel Payment
                    </button>
                </form>
            </div>
        </div>

        <!-- Footer -->
        <div class="p-3.5 bg-gray-50 border-t border-gray-100 text-center text-[11px] text-gray-400">
            Powered by bKash Tokenized Checkout API &bull; 16247
        </div>
    </div>
</body>
</html>
