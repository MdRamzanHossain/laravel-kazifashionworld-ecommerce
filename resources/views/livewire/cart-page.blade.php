<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-100 pb-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Shopping Bag</h1>
            <p class="text-xs text-gray-500 mt-1">Review your items before proceeding to secure checkout</p>
        </div>
        <a href="{{ route('home') }}" wire:navigate class="text-xs font-bold text-brand-600 hover:text-brand-700 transition flex items-center gap-1">
            <span>&larr; Continue Shopping</span>
        </a>
    </div>

    @if(count($cart) > 0)
        <!-- Free Shipping Threshold Banner -->
        @if($isFreeShippingEnabled)
            @php
                $remainingForFree = max(0, $freeShippingThreshold - $subtotal);
                $progressPct = min(100, round(($subtotal / $freeShippingThreshold) * 100));
            @endphp

            <div class="bg-gradient-to-r from-brand-50 to-pink-50 border border-brand-100 rounded-2xl p-4 sm:p-5 space-y-2">
                <div class="flex justify-between items-center text-xs font-bold">
                    <span class="text-brand-900 flex items-center gap-1.5">
                        <span>✨</span>
                        @if($remainingForFree > 0)
                            <span>Add <strong class="text-brand-600">BDT {{ number_format($remainingForFree, 2) }}</strong> more to unlock <strong>FREE Inside Dhaka Delivery</strong> (Threshold: BDT {{ number_format($freeShippingThreshold) }})!</span>
                        @else
                            <span class="text-emerald-700">🎉 Congratulations! You have unlocked <strong>FREE Inside Dhaka Delivery</strong>!</span>
                        @endif
                    </span>
                    <span class="text-brand-700">{{ $progressPct }}%</span>
                </div>
                <div class="w-full bg-white rounded-full h-2 overflow-hidden shadow-inner">
                    <div class="bg-gradient-to-r from-brand-500 to-pink-500 h-full rounded-full transition-all duration-500" style="width: {{ $progressPct }}%"></div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <!-- Left: Cart Items List (8 Cols) -->
            <div class="lg:col-span-8 space-y-4">
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm divide-y divide-gray-100 overflow-hidden">
                    @foreach($cart as $item)
                        <div class="p-4 sm:p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 group">
                            <div class="flex items-center gap-4">
                                <!-- Image Thumbnail (4:5 Ratio) -->
                                <a href="{{ route('product.detail', $item['slug']) }}" wire:navigate class="w-16 h-20 sm:w-20 sm:h-24 aspect-[4/5] rounded-2xl bg-gray-50 border border-gray-100 overflow-hidden shrink-0 flex items-center justify-center">
                                    @if(!empty($item['image']))
                                        <img src="{{ asset('storage/' . $item['image']) }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-gray-300 text-xs">No Image</span>
                                    @endif
                                </a>

                                <!-- Title & Details -->
                                <div class="space-y-1">
                                    <a href="{{ route('product.detail', $item['slug']) }}" wire:navigate class="font-bold text-sm sm:text-base text-gray-900 hover:text-brand-600 transition line-clamp-2">
                                        {{ $item['name'] }}
                                    </a>
                                    @if(!empty($item['variation_name']))
                                        <p class="text-[11px] text-brand-600 font-extrabold bg-brand-50 inline-block px-1.5 py-0.5 rounded">{{ $item['variation_name'] }}</p>
                                    @endif
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="text-xs text-gray-600">Unit Price: <strong class="text-gray-900">BDT {{ number_format($item['price'], 2) }}</strong></p>
                                        @if(($item['is_flash_sale'] ?? false) || (isset($item['original_price']) && $item['original_price'] > $item['price']))
                                            <span class="text-[9px] font-extrabold text-rose-600 bg-rose-50 px-1.5 py-0.5 rounded border border-rose-200">⚡ Flash Deal</span>
                                            <span class="text-[11px] text-gray-400 line-through">BDT {{ number_format($item['original_price'], 2) }}</span>
                                        @endif
                                    </div>
                                    <span class="text-[10px] text-emerald-600 font-bold bg-emerald-50 px-2 py-0.5 rounded-md inline-block">In Stock</span>
                                </div>
                            </div>

                            <!-- Stepper & Actions -->
                            <div class="flex items-center justify-between sm:justify-end gap-6 w-full sm:w-auto border-t sm:border-t-0 pt-3 sm:pt-0 border-gray-50">
                                <!-- Stepper -->
                                <div class="flex items-center border border-gray-200 rounded-xl bg-gray-50 p-1">
                                    <button 
                                        type="button"
                                        wire:click="updateQuantity('{{ $item['cart_key'] }}', {{ $item['quantity'] - 1 }})"
                                        class="w-7 h-7 rounded-lg bg-white hover:bg-gray-100 text-gray-800 font-bold flex items-center justify-center transition shadow-sm text-xs"
                                    >
                                        -
                                    </button>
                                    <span class="px-3 font-extrabold text-xs text-gray-900">{{ $item['quantity'] }}</span>
                                    <button 
                                        type="button"
                                        wire:click="updateQuantity('{{ $item['cart_key'] }}', {{ $item['quantity'] + 1 }})"
                                        class="w-7 h-7 rounded-lg bg-white hover:bg-gray-100 text-gray-800 font-bold flex items-center justify-center transition shadow-sm text-xs"
                                    >
                                        +
                                    </button>
                                </div>

                                <!-- Total for item -->
                                <div class="text-right min-w-[90px]">
                                    <span class="text-sm sm:text-base font-extrabold text-gray-900 block">
                                        BDT {{ number_format($item['price'] * $item['quantity'], 2) }}
                                    </span>
                                    <button 
                                        type="button"
                                        wire:click="removeItem('{{ $item['cart_key'] }}')"
                                        class="text-[11px] text-red-500 hover:text-red-700 font-semibold transition mt-0.5"
                                    >
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Right: Order Summary Sidebar with Promo Code & Auto Spend Discounts (4 Cols) -->
            <div class="lg:col-span-4 bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-sm space-y-6 lg:sticky lg:top-28">
                <h2 class="text-lg font-extrabold text-gray-900 pb-3 border-b border-gray-100">Order Summary</h2>

                <!-- Promo / Coupon Code Box -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700">Have a Promo Code?</label>

                    @if(session()->has('coupon_success'))
                        <div class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center justify-between">
                            <span>✓ {{ session('coupon_success') }}</span>
                        </div>
                    @endif

                    @if(session()->has('coupon_removed'))
                        <div class="p-2 rounded-xl bg-gray-50 border border-gray-200 text-gray-600 text-xs">
                            {{ session('coupon_removed') }}
                        </div>
                    @endif

                    @if($appliedCoupon)
                        <!-- Applied Coupon Badge -->
                        <div class="p-3 bg-brand-50/70 border border-brand-200 rounded-2xl flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span class="text-base">🎟️</span>
                                <div>
                                    <span class="font-extrabold text-xs text-brand-900 font-mono">{{ $appliedCoupon['code'] }}</span>
                                    <span class="text-[10px] text-brand-600 font-bold block">-BDT {{ number_format($promoDiscount, 2) }} applied</span>
                                </div>
                            </div>
                            <button 
                                type="button" 
                                wire:click="removeCoupon"
                                class="text-xs text-red-500 hover:text-red-700 font-bold px-2 py-1 rounded-lg hover:bg-red-50 transition"
                                title="Remove coupon"
                            >
                                Remove &times;
                            </button>
                        </div>
                    @else
                        <!-- Promo Input Form -->
                        <form wire:submit.prevent="applyCoupon" class="flex gap-2">
                            <input 
                                type="text" 
                                wire:model="couponCode" 
                                placeholder="e.g. EID2026"
                                class="flex-1 px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500 outline-none text-xs uppercase font-mono transition"
                            >
                            <button 
                                type="submit" 
                                class="px-4 py-2.5 bg-gradient-to-r from-primary to-accent hover:from-primary-hover hover:to-primary text-white text-xs font-bold rounded-xl transition shadow-sm"
                            >
                                Apply
                            </button>
                        </form>
                        @error('couponCode') <span class="text-xs text-red-500 block mt-1">{{ $message }}</span> @enderror
                    @endif
                </div>

                <div class="space-y-3 text-xs pt-2 border-t border-gray-100">
                    <div class="flex justify-between text-gray-600">
                        <span>Bag Subtotal</span>
                        <span class="font-bold text-gray-900">BDT {{ number_format($subtotal, 2) }}</span>
                    </div>

                    @if($promoDiscount > 0)
                        <div class="flex justify-between text-brand-600 font-bold">
                            <span>Promo Discount ({{ $appliedCoupon['code'] }})</span>
                            <span>- BDT {{ number_format($promoDiscount, 2) }}</span>
                        </div>
                    @endif

                    @if($autoDiscountAmount > 0)
                        <div class="flex justify-between text-emerald-600 font-bold">
                            <span>🎉 {{ $autoDiscountData['name'] }}</span>
                            <span>- BDT {{ number_format($autoDiscountAmount, 2) }}</span>
                        </div>
                    @endif

                    <div class="flex justify-between items-center text-gray-600">
                        <span>Estimated Shipping</span>
                        @if($estimatedShippingFee == 0)
                            <span class="font-extrabold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                                🎉 FREE Inside Dhaka
                            </span>
                        @else
                            <span class="font-medium text-gray-900">BDT {{ number_format($estimatedShippingFee, 2) }} (Inside Dhaka)</span>
                        @endif
                    </div>

                    <div class="flex justify-between text-gray-600">
                        <span>VAT / Taxes</span>
                        <span class="font-medium text-emerald-600">Included</span>
                    </div>

                    <div class="flex justify-between font-extrabold text-base border-t border-gray-100 pt-3 text-gray-900">
                        <span>Estimated Total</span>
                        <span class="text-brand-600 text-lg">BDT {{ number_format($payableTotal, 2) }}</span>
                    </div>
                </div>

                <a 
                    href="{{ route('checkout') }}" 
                    wire:navigate
                    class="block text-center w-full py-4 px-6 bg-gradient-to-r from-primary to-accent hover:from-primary-hover hover:to-primary text-white text-xs sm:text-sm font-bold rounded-2xl shadow-lg shadow-brand-500/25 transition duration-200"
                >
                    Proceed to Secure Checkout &rarr;
                </a>

                <!-- Trust Badges Under Summary -->
                <div class="space-y-2 pt-2 border-t border-gray-100 text-[11px] text-gray-500">
                    <div class="flex items-center gap-2">
                        <span class="text-brand-600 font-bold">✓</span>
                        <span>100% Original Products Guaranteed</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-brand-600 font-bold">✓</span>
                        <span>bKash Direct, Cards & Cash on Delivery</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-brand-600 font-bold">✓</span>
                        <span>7-Day Easy Replacement Policy</span>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Empty Cart State -->
        <div class="bg-white rounded-3xl p-12 sm:p-16 text-center border border-gray-100 shadow-sm max-w-lg mx-auto space-y-4">
            <div class="w-20 h-20 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center mx-auto text-3xl shadow-sm">
                🛍️
            </div>
            <h2 class="text-xl font-bold text-gray-900">Your shopping bag is empty</h2>
            <p class="text-xs text-gray-500 max-w-xs mx-auto">Looks like you haven't added any luxury beauty or apparel items yet.</p>
            <div class="pt-2">
                <a href="{{ route('home') }}" wire:navigate class="inline-block px-7 py-3.5 bg-primary hover:bg-primary-hover text-white font-bold text-xs rounded-2xl shadow-md transition">
                    Explore Best Sellers &rarr;
                </a>
            </div>
        </div>
    @endif
</div>
