<div class="space-y-8">
    @if (session()->has('error'))
        <div class="rounded-2xl bg-red-50 border border-red-200 p-4 text-xs sm:text-sm text-red-700 flex items-center gap-2" role="alert">
            <span>⚠️</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="border-b border-gray-100 pb-4">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Complete Your Order</h1>
        <p class="text-xs text-gray-500 mt-1">Provide your delivery address and choose your preferred payment method</p>
    </div>

    <form wire:submit.prevent="placeOrder" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <!-- Customer & Delivery Information (8 Cols) -->
        <div class="lg:col-span-8 bg-white p-6 sm:p-8 rounded-3xl shadow-sm border border-gray-100 space-y-6">
            <h2 class="text-base font-bold text-gray-900 pb-3 border-b border-gray-100 flex items-center gap-2">
                <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span>Delivery Information</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Full Name</label>
                    <input type="text" wire:model="name" placeholder="Your Full Name" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500 outline-none text-xs transition">
                    @error('name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Phone Number (For SMS & Tracking)</label>
                    <input type="text" wire:model="phone" placeholder="017XXXXXXXX" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500 outline-none text-xs transition">
                    @error('phone') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Email Address</label>
                    <input type="email" wire:model="email" placeholder="you@example.com" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500 outline-none text-xs transition">
                    @error('email') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Delivery Destination</label>
                    <select wire:model.live="city" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500 outline-none text-xs transition">
                        <option value="inside_dhaka">Inside Dhaka {{ $isFreeShipping && $city === 'inside_dhaka' ? '🎉 (FREE Delivery Unlocked!)' : '(BDT ' . number_format(App\Services\SettingService::getStandardShippingFees()['inside_dhaka']) . ')' }}</option>
                        <option value="outside_dhaka">Outside Dhaka {{ $isFreeShipping && $city === 'outside_dhaka' ? '🎉 (FREE Delivery Unlocked!)' : '(BDT ' . number_format(App\Services\SettingService::getStandardShippingFees()['outside_dhaka']) . ')' }}</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Full Shipping Address</label>
                <textarea wire:model="address" rows="3" placeholder="House/Road no, Flat/Apartment, Area, Thana..." class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500 outline-none text-xs transition"></textarea>
                @error('address') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Payment Method Selection -->
            @php
                $bkashConfig = App\Services\PaymentSettingService::getBkashConfig();
                $sslConfig = App\Services\PaymentSettingService::getSslCommerzConfig();
                $codConfig = App\Services\PaymentSettingService::getCodConfig();
                $bankConfig = App\Services\PaymentSettingService::getBankTransferConfig();
            @endphp
            <div class="pt-3 border-t border-gray-100 space-y-3">
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-900 flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    <span>Select Payment Method</span>
                </label>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <!-- bKash Option -->
                    @if($bkashConfig['enabled'])
                    <label class="relative flex flex-col p-4 border rounded-2xl cursor-pointer transition {{ $paymentMethod === 'bkash' ? 'border-[#e2136e] bg-pink-50/60 ring-2 ring-pink-300' : 'border-gray-200 hover:bg-gray-50' }}">
                        <div class="flex items-center justify-between mb-1.5">
                            <input type="radio" wire:model.live="paymentMethod" value="bkash" class="w-4 h-4 text-[#e2136e] focus:ring-[#e2136e]">
                            <span class="bg-[#e2136e] text-white text-[9px] font-extrabold px-2 py-0.5 rounded">bKash</span>
                        </div>
                        <span class="block text-xs font-bold text-gray-900">bKash Direct</span>
                        <span class="block text-[10px] text-gray-500 mt-0.5 line-clamp-2">{{ $bkashConfig['instructions'] ?: 'Instant bKash OTP & PIN checkout' }}</span>
                    </label>
                    @endif

                    <!-- Online Cards / Other MFS Option -->
                    @if($sslConfig['enabled'])
                    <label class="relative flex flex-col p-4 border rounded-2xl cursor-pointer transition {{ $paymentMethod === 'online' ? 'border-indigo-600 bg-indigo-50/60 ring-2 ring-indigo-300' : 'border-gray-200 hover:bg-gray-50' }}">
                        <div class="flex items-center justify-between mb-1.5">
                            <input type="radio" wire:model.live="paymentMethod" value="online" class="w-4 h-4 text-indigo-600 focus:ring-indigo-500">
                            <span class="bg-indigo-100 text-indigo-700 text-[9px] font-bold px-1.5 py-0.5 rounded">Cards & MFS</span>
                        </div>
                        <span class="block text-xs font-bold text-gray-900">Cards / Nagad / Rocket</span>
                        <span class="block text-[10px] text-gray-500 mt-0.5 line-clamp-2">{{ $sslConfig['instructions'] ?: 'Visa, Master, Internet Banking, MFS' }}</span>
                    </label>
                    @endif

                    <!-- COD Option -->
                    @if($codConfig['enabled'])
                    <label class="relative flex flex-col p-4 border rounded-2xl cursor-pointer transition {{ $paymentMethod === 'cod' ? 'border-brand-600 bg-brand-50/60 ring-2 ring-brand-200' : 'border-gray-200 hover:bg-gray-50' }}">
                        <div class="flex items-center justify-between mb-1.5">
                            <input type="radio" wire:model.live="paymentMethod" value="cod" class="w-4 h-4 text-brand-600 focus:ring-brand-500">
                            <span class="bg-gray-100 text-gray-700 text-[9px] font-bold px-1.5 py-0.5 rounded">Cash</span>
                        </div>
                        <span class="block text-xs font-bold text-gray-900">Cash on Delivery</span>
                        <span class="block text-[10px] text-gray-500 mt-0.5 line-clamp-2">{{ $codConfig['instructions'] ?: 'Pay upon parcel arrival' }}</span>
                    </label>
                    @endif
                </div>
                @error('paymentMethod') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Order Summary Sidebar (4 Cols) -->
        <div class="lg:col-span-4 bg-white p-6 sm:p-8 rounded-3xl shadow-sm border border-gray-100 space-y-5 lg:sticky lg:top-28">
            <h2 class="text-base font-extrabold text-gray-900 pb-3 border-b border-gray-100">Order Summary</h2>

            <div class="divide-y divide-gray-100 max-h-56 overflow-y-auto pr-1 text-xs">
                @foreach($cart as $item)
                    <div class="py-2.5 flex justify-between items-center gap-2">
                        <div class="min-w-0">
                            <span class="text-gray-800 font-medium block truncate">{{ $item['name'] }} <span class="text-gray-400 font-normal">(x{{ $item['quantity'] }})</span></span>
                            @if(($item['is_flash_sale'] ?? false) || (isset($item['original_price']) && $item['original_price'] > $item['price']))
                                <span class="text-[9px] font-extrabold text-rose-600 bg-rose-50 px-1 rounded border border-rose-200">⚡ Flash Price (BDT {{ number_format($item['price']) }})</span>
                            @endif
                        </div>
                        <span class="font-bold text-gray-900 shrink-0">BDT {{ number_format($item['price'] * $item['quantity'], 2) }}</span>
                    </div>
                @endforeach
            </div>

            <!-- Loyalty Points Section -->
            @if(auth()->check() && \App\Services\SettingService::isLoyaltyEnabled() && auth()->user()->points_balance > 0)
                <div class="border-t border-gray-100 pt-3 space-y-2">
                    <label class="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-gray-700 cursor-pointer">
                        <span>Use Loyalty Points (Balance: {{ auth()->user()->points_balance }})</span>
                        <input type="checkbox" wire:model.live="useLoyaltyPoints" class="rounded text-brand-600 focus:ring-brand-500 w-4 h-4 transition">
                    </label>
                    <p class="text-[10px] text-gray-500">1 Point = BDT {{ number_format(\App\Services\SettingService::getLoyaltyRedemptionValue(), 2) }} discount.</p>
                </div>
            @endif

            <!-- Promo Code Section at Checkout -->
            <div class="border-t border-gray-100 pt-3 space-y-2">
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-700">Promo Code</label>

                @if(session()->has('coupon_success'))
                    <div class="p-2 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium">
                        ✓ {{ session('coupon_success') }}
                    </div>
                @endif

                @if($appliedCoupon)
                    <div class="p-2.5 bg-brand-50/70 border border-brand-200 rounded-xl flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5">
                            <span>🎟️</span>
                            <span class="font-mono font-extrabold text-xs text-brand-900">{{ $appliedCoupon['code'] }}</span>
                            <span class="text-[10px] text-brand-600 font-bold">(-BDT {{ number_format($promoDiscount, 2) }})</span>
                        </div>
                        <button type="button" wire:click="removeCoupon" class="text-xs text-red-500 hover:text-red-700 font-bold">
                            &times;
                        </button>
                    </div>
                @else
                    <div class="flex gap-2">
                        <input 
                            type="text" 
                            wire:model="couponCode" 
                            placeholder="Promo Code" 
                            class="flex-1 px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500 outline-none text-xs uppercase font-mono"
                        >
                        <button 
                            type="button" 
                            wire:click="applyCoupon" 
                            class="px-3.5 py-2 bg-gradient-to-r from-primary to-accent hover:from-primary-hover hover:to-primary text-white text-xs font-bold rounded-xl transition"
                        >
                            Apply
                        </button>
                    </div>
                    @error('couponCode') <span class="text-xs text-red-500 block">{{ $message }}</span> @enderror
                @endif
            </div>

            <!-- Price Breakdown -->
            <div class="border-t border-gray-100 pt-4 space-y-2.5 text-xs">
                <div class="flex justify-between text-gray-600">
                    <span>Subtotal</span>
                    <span class="font-semibold text-gray-900">BDT {{ number_format($subtotal, 2) }}</span>
                </div>

                @if($promoDiscount > 0)
                    <div class="flex justify-between text-brand-600 font-bold">
                        <span>Promo Code ({{ $appliedCoupon['code'] }})</span>
                        <span>- BDT {{ number_format($promoDiscount, 2) }}</span>
                    </div>
                @endif

                @if($autoDiscountAmount > 0)
                    <div class="flex justify-between text-emerald-600 font-bold">
                        <span>🎉 {{ $autoDiscountData['name'] }}</span>
                        <span>- BDT {{ number_format($autoDiscountAmount, 2) }}</span>
                    </div>
                @endif

                @if($loyaltyDiscount > 0)
                    <div class="flex justify-between text-purple-600 font-bold">
                        <span>Loyalty Points ({{ $loyaltyPointsUsed }} used)</span>
                        <span>- BDT {{ number_format($loyaltyDiscount, 2) }}</span>
                    </div>
                @endif

                <div class="flex justify-between items-center text-gray-600">
                    <span>Delivery Fee</span>
                    @if($isFreeShipping)
                        <span class="font-extrabold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200 flex items-center gap-1">
                            <span>🎉</span>
                            <span>FREE (BDT 0.00)</span>
                        </span>
                    @else
                        <span class="font-semibold text-gray-900">BDT {{ number_format($shippingFee, 2) }}</span>
                    @endif
                </div>

                <div class="flex justify-between font-extrabold text-base border-t border-gray-200 pt-3 text-gray-900">
                    <span>Total Payable</span>
                    <span class="text-brand-600 text-lg">BDT {{ number_format($grandTotal, 2) }}</span>
                </div>
            </div>

            <button 
                type="submit" 
                wire:loading.attr="disabled" 
                class="w-full {{ $paymentMethod === 'bkash' ? 'bg-[#e2136e] hover:bg-[#c90f61]' : 'bg-primary hover:bg-primary-hover' }} text-white py-4 px-6 rounded-2xl font-bold text-xs sm:text-sm transition duration-200 flex items-center justify-center gap-2 shadow-lg"
            >
                <span wire:loading.remove>
                    @if($paymentMethod === 'bkash')
                        Pay with bKash (BDT {{ number_format($grandTotal, 2) }}) &rarr;
                    @elseif($paymentMethod === 'online')
                        Proceed to Online Payment &rarr;
                    @else
                        Confirm Order (Cash on Delivery)
                    @endif
                </span>
                <span wire:loading class="flex items-center gap-2">
                    <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span>Processing Order...</span>
                </span>
            </button>
        </div>
    </form>
</div>


@if(\App\Models\Setting::get('meta_pixel_id'))
    <!-- Meta Pixel InitiateCheckout -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof fbq === 'function') {
                fbq('track', 'InitiateCheckout', {
                    value: {{ $cartTotal ?? 0 }},
                    currency: 'BDT'
                });
            }
        });
    </script>
@endif