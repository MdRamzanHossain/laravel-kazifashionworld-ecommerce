<div class="space-y-16 py-4">

    @php
        $schema = [
            '@context' => 'https://schema.org/',
            '@type' => 'Product',
            'name' => $product->name,
            'image' => collect([$product->image])->merge($product->images ?? [])->filter()->map(fn($img) => asset('storage/' . $img))->toArray(),
            'description' => strip_tags($product->short_description ?? $product->description),
            'sku' => $product->sku,
            'offers' => [
                '@type' => 'Offer',
                'url' => url()->current(),
                'priceCurrency' => 'BDT',
                'price' => $product->sale_price ?? $product->price,
                'itemCondition' => 'https://schema.org/NewCondition',
                'availability' => $product->stock_quantity > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                'seller' => [
                    '@type' => 'Organization',
                    'name' => config('app.name', 'Kazi Fashion World')
                ]
            ]
        ];

        if ($product->brand) {
            $schema['brand'] = [
                '@type' => 'Brand',
                'name' => $product->brand->name
            ];
        }

        if ($product->approved_reviews_count > 0) {
            $schema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => round($product->average_rating, 1),
                'reviewCount' => $product->approved_reviews_count
            ];
        }
    @endphp
    <script type="application/ld+json">
        {!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof fbq === 'function') {
                fbq('track', 'ViewContent', {
                    content_name: '{{ addslashes($product->name) }}',
                    content_ids: ['{{ $product->id }}'],
                    content_type: 'product',
                    value: {{ $product->sale_price ?? $product->price }},
                    currency: 'BDT'
                });
            }
            if (typeof gtag === 'function') {
                gtag('event', 'view_item', {
                    currency: 'BDT',
                    value: {{ $product->sale_price ?? $product->price }},
                    items: [{
                        item_id: '{{ $product->id }}',
                        item_name: '{{ addslashes($product->name) }}',
                        price: {{ $product->sale_price ?? $product->price }}
                    }]
                });
            }
        });
    </script>

    @if (session()->has('cart_success'))
        <div class="fixed bottom-6 right-6 z-50 bg-gray-950 text-white px-5 py-3.5 rounded-2xl shadow-2xl border border-gray-800 flex items-center gap-3 transition-all duration-300 transform translate-y-0" role="alert">
            <span class="w-7 h-7 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-sm">Ã¢Å“â€œ</span>
            <span class="text-xs font-semibold">{{ session('cart_success') }}</span>
            <a href="{{ route('cart') }}" wire:navigate class="text-xs bg-gradient-to-r from-primary to-accent hover:from-primary-hover hover:to-primary text-white font-bold px-3 py-1.5 rounded-xl ml-2 transition">View Bag &rarr;</a>
        </div>
    @endif

    <nav class="flex items-center space-x-2 text-xs text-gray-500 font-medium overflow-x-auto pb-1">
        <a href="{{ route('home') }}" wire:navigate class="hover:text-brand-600 transition">Home</a>
        <span>/</span>
        @if($product->category)
            @if($product->category->parent)
                <a href="{{ route('home') }}" wire:navigate class="hover:text-brand-600 transition">{{ $product->category->parent->name }}</a>
                <span>/</span>
            @endif
            <a href="{{ route('home') }}" wire:navigate class="hover:text-brand-600 transition">{{ $product->category->name }}</a>
            <span>/</span>
        @endif
        <span class="text-gray-900 font-bold truncate max-w-xs">{{ $product->name }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-start">

        @php
            $resolveImageUrl = function($path) {
                if (empty($path)) return asset('images/logo.png');
                if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                    return $path;
                }
                if (str_starts_with($path, 'images/')) {
                    return asset($path);
                }
                return asset('storage/' . $path);
            };

            $rawImages = array_filter(array_merge(
                [$product->image],
                is_array($product->images) ? $product->images : [],
                $product->productImages ? $product->productImages->pluck('image_path')->toArray() : []
            ));

            $galleryImages = array_values(array_unique(array_map($resolveImageUrl, $rawImages)));
            if (empty($galleryImages)) {
                $galleryImages = [asset('images/logo.png')];
            }
        @endphp

        <div 
            x-data="{
                images: {{ json_encode($galleryImages) }},
                activeIdx: 0,
                isZoomed: false,
                zoomX: 50,
                zoomY: 50,
                lightboxOpen: false,
                touchStartX: 0,
                touchEndX: 0,

                handleMouseMove(e) {
                    const rect = e.currentTarget.getBoundingClientRect();
                    const x = ((e.clientX - rect.left) / rect.width) * 100;
                    const y = ((e.clientY - rect.top) / rect.height) * 100;
                    this.zoomX = Math.max(0, Math.min(100, x));
                    this.zoomY = Math.max(0, Math.min(100, y));
                },

                nextImage() {
                    this.activeIdx = (this.activeIdx + 1) % this.images.length;
                },

                prevImage() {
                    this.activeIdx = (this.activeIdx - 1 + this.images.length) % this.images.length;
                },

                handleTouchStart(e) {
                    this.touchStartX = e.changedTouches[0].screenX;
                },

                handleTouchMove(e) {
                    if (window.innerWidth < 1024) {
                        this.isZoomed = true;
                        const rect = e.currentTarget.getBoundingClientRect();
                        const touch = e.touches[0];
                        const x = ((touch.clientX - rect.left) / rect.width) * 100;
                        const y = ((touch.clientY - rect.top) / rect.height) * 100;
                        this.zoomX = Math.max(0, Math.min(100, x));
                        this.zoomY = Math.max(0, Math.min(100, y));
                    }
                },
                handleTouchEnd(e) {
                    this.touchEndX = e.changedTouches[0].screenX;
                    if (this.touchStartX - this.touchEndX > 40) {
                        this.nextImage();
                    } else if (this.touchEndX - this.touchStartX > 40) {
                        this.prevImage();
                    }
                }
            }"
            @keydown.left.window="if (lightboxOpen) prevImage()"
            @keydown.right.window="if (lightboxOpen) nextImage()"
            @keydown.escape.window="lightboxOpen = false"
            class="lg:col-span-6 space-y-4 lg:sticky lg:top-28"
        >
            
            <div 
                class="relative aspect-[4/5] w-full rounded-3xl bg-gradient-to-b from-gray-50 to-pink-50/20 border border-gray-100 overflow-hidden shadow-sm flex items-center justify-center group cursor-crosshair select-none"
                @mousemove="handleMouseMove($event)"
                @mouseenter="isZoomed = true"
                @mouseleave="isZoomed = false"
                @touchstart="handleTouchStart($event)"
                @touchend="handleTouchEnd($event)"
            >
                
                <img loading="lazy" 
                    :src="images[activeIdx]" 
                    alt="{{ $product->name }}" 
                    class="w-full h-full object-cover object-center pointer-events-none transition-transform duration-100 ease-out"
                    :style="isZoomed 
                        ? `transform: scale(2.3); transform-origin: ${zoomX}% ${zoomY}%;` 
                        : 'transform: scale(1); transform-origin: center center;'"
                >

                <div class="absolute top-4 left-4 flex flex-col gap-2 z-10 pointer-events-none">
                    @if($product->sale_price && $product->sale_price < $product->price)
                        @php
                            $discountPct = round((($product->price - $product->sale_price) / $product->price) * 100);
                        @endphp
                        <span class="bg-gradient-to-r from-rose-600 to-pink-600 text-white text-[11px] font-extrabold px-3 py-1 rounded-xl shadow-lg tracking-wider">
                            -{{ $discountPct }}% OFF
                        </span>
                    @endif

                    @if($product->is_featured)
                        <span class="bg-gray-900/90 backdrop-blur-md text-white text-[10px] font-bold px-2.5 py-0.5 rounded-lg shadow-sm border border-white/10">
                            Ã¢Å“Â¨ Authentic Luxury
                        </span>
                    @endif
                </div>

                <button 
                    type="button"
                    @click.stop="lightboxOpen = true" @touchstart.stop @mouseenter.stop
                    class="absolute top-4 right-4 z-10 p-2.5 rounded-2xl bg-white/90 hover:bg-white text-gray-700 hover:text-brand-600 shadow-md backdrop-blur-md border border-gray-100 transition transform hover:scale-110 active:scale-95 group/btn cursor-pointer"
                    title="View Fullscreen & High-Res Zoom"
                >
                    <svg class="w-4 h-4 text-gray-700 group-hover/btn:text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                    </svg>
                </button>

                @if(count($galleryImages) > 1)
                    <div>
                        <button 
                            type="button" 
                            @click.stop="prevImage()" @touchstart.stop @mouseenter.stop
                            class="absolute left-3 top-1/2 -translate-y-1/2 z-10 w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-white/90 hover:bg-white text-gray-800 shadow-lg backdrop-blur-md border border-gray-200 flex items-center justify-center transition-all duration-300 opacity-0 group-hover:opacity-100 sm:opacity-75 sm:hover:opacity-100 cursor-pointer hover:scale-90 active:scale-75"
                            aria-label="Previous Image"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                        </button>

                        <button 
                            type="button" 
                            @click.stop="nextImage()" @touchstart.stop @mouseenter.stop
                            class="absolute right-3 top-1/2 -translate-y-1/2 z-10 w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-white/90 hover:bg-white text-gray-800 shadow-lg backdrop-blur-md border border-gray-200 flex items-center justify-center transition-all duration-300 opacity-0 group-hover:opacity-100 sm:opacity-75 sm:hover:opacity-100 cursor-pointer hover:scale-90 active:scale-75"
                            aria-label="Next Image"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                @endif

                <div class="absolute bottom-4 left-4 right-4 flex items-center justify-between z-10 pointer-events-none">
                    
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold backdrop-blur-md shadow-sm {{ ($product->stock_quantity ?? 0) > 0 ? 'bg-white/90 text-emerald-700 border border-emerald-200' : 'bg-white/90 text-red-700 border border-red-200' }}">
                        <span class="w-2 h-2 rounded-full {{ ($product->stock_quantity ?? 0) > 0 ? 'bg-emerald-500 animate-pulse' : 'bg-red-500' }}"></span>
                        <span>{{ ($product->stock_quantity ?? 0) > 0 ? 'In Stock (' . $product->stock_quantity . ')' : 'Out of Stock' }}</span>
                    </span>

                    <span class="hidden md:inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-gray-900/75 backdrop-blur-md text-white shadow-sm transition-opacity duration-300" :class="isZoomed ? 'opacity-0' : 'opacity-100'">
                        <svg class="w-3 h-3 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/></svg>
                        <span>Hover to Zoom</span>
                    </span>

                    @if(count($galleryImages) > 1)
                        <span class="md:hidden inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-black/60 backdrop-blur-md text-white">
                            <span x-text="activeIdx + 1"></span>/<span>{{ count($galleryImages) }}</span>
                        </span>
                    @endif
                </div>
            </div>

            @if(count($galleryImages) > 1)
                <div class="flex justify-start sm:justify-start -mt-8 sm:mt-2 relative z-20 px-2 sm:px-0">
                    <div class="flex items-center gap-2.5 overflow-x-auto p-1.5 sm:p-0 bg-white/70 sm:bg-transparent backdrop-blur-md sm:backdrop-blur-none rounded-2xl sm:rounded-none shadow-sm sm:shadow-none border border-white/60 sm:border-0 max-w-full scrollbar-hide snap-x">
                        @foreach($galleryImages as $index => $thumbSrc)
                            <button 
                                type="button" 
                                @click="activeIdx = {{ $index }}"
                                class="relative w-14 h-16 sm:w-16 sm:h-20 aspect-[4/5] rounded-xl sm:rounded-2xl overflow-hidden transition-all shrink-0 p-0 transform active:scale-95 cursor-pointer snap-center"
                                :class="activeIdx === {{ $index }} 
                                    ? 'border-[2px] border-gray-900 ring-2 ring-gray-900/20 scale-105 shadow-md opacity-100' 
                                    : 'border-[2px] border-transparent opacity-70 hover:opacity-100 hover:scale-105'"
                            >
                                <img loading="lazy" src="{{ $thumbSrc }}" alt="{{ $product->name }} Thumbnail {{ $index + 1 }}" class="w-full h-full object-cover rounded-lg sm:rounded-xl pointer-events-none">
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            <div 
                x-show="lightboxOpen" 
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 z-50 bg-black/95 backdrop-blur-xl flex flex-col items-center justify-between p-4 sm:p-8 select-none"
                style="display: none;"
                @click.self="lightboxOpen = false"
            >
                
                <div class="w-full flex items-center justify-between text-white max-w-5xl z-20">
                    <div class="flex items-center gap-3">
                        <span class="text-sm sm:text-base font-extrabold tracking-wide text-brand-300">{{ $product->name }}</span>
                        @if(count($galleryImages) > 1)
                            <span class="text-xs bg-white/10 px-2.5 py-1 rounded-full text-gray-300 font-mono">
                                <span x-text="activeIdx + 1"></span> / <span>{{ count($galleryImages) }}</span>
                            </span>
                        @endif
                    </div>

                    <button 
                        type="button" 
                        @click="lightboxOpen = false"
                        class="p-2.5 rounded-full bg-white/10 hover:bg-white/20 text-white transition text-sm font-bold flex items-center gap-1.5 cursor-pointer"
                    >
                        <span>Close</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="relative w-full max-w-4xl flex-1 flex items-center justify-center my-4 overflow-hidden">
                    @if(count($galleryImages) > 1)
                        <button 
                            type="button" 
                            @click="prevImage()"
                            class="absolute left-2 sm:left-4 z-20 w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition backdrop-blur-md cursor-pointer"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                    @endif

                    <img loading="lazy" 
                        :src="images[activeIdx]" 
                        alt="{{ $product->name }}" 
                        class="max-h-[75vh] max-w-full object-contain rounded-2xl shadow-2xl transition-all duration-300"
                    >

                    @if(count($galleryImages) > 1)
                        <button 
                            type="button" 
                            @click="nextImage()"
                            class="absolute right-2 sm:right-4 z-20 w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition backdrop-blur-md cursor-pointer"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    @endif
                </div>

                @if(count($galleryImages) > 1)
                    <div class="flex items-center gap-3 overflow-x-auto max-w-xl pb-2 z-20">
                        @foreach($galleryImages as $i => $thumb)
                            <button 
                                type="button" 
                                @click="activeIdx = {{ $i }}"
                                class="w-16 h-16 rounded-xl overflow-hidden border-2 transition shrink-0 cursor-pointer"
                                :class="activeIdx === {{ $i }} ? 'border-brand-500 ring-2 ring-brand-300 scale-105 opacity-100' : 'border-white/20 opacity-50 hover:opacity-80'"
                            >
                                <img loading="lazy" src="{{ $thumb }}" class="w-full h-full object-cover pointer-events-none">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="lg:col-span-6 space-y-6">

            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 pb-3">
                <div class="flex items-center gap-2">
                    @if($product->brand)
                        <span class="bg-gray-100 text-gray-800 text-[10px] font-extrabold uppercase tracking-widest px-2.5 py-1 rounded-lg">
                            {{ $product->brand->name }}
                        </span>
                    @endif

                    @if($product->category)
                        <span class="bg-brand-50 text-brand-700 text-[11px] font-semibold px-2.5 py-1 rounded-lg">
                            @if($product->category->parent)
                                {{ $product->category->parent->name }} &rsaquo; {{ $product->category->name }}
                            @else
                                {{ $product->category->name }}
                            @endif
                        </span>
                    @endif
                </div>

                @if($product->sku)
                    <span class="text-[11px] font-mono text-gray-400">SKU: <strong class="text-gray-600">{{ $product->sku }}</strong></span>
                @endif
            </div>

            <div class="space-y-2">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-gray-900 tracking-tight leading-tight">
                    {{ $product->name }}
                </h1>

                                  <div class="flex items-center gap-3 pt-1">
                      @if($product->approved_reviews_count > 0)
                          <div class="flex items-center text-amber-400 text-sm">
                              <div class="flex">
                                  @for($i = 1; $i <= 5; $i++)
                                      @if($i <= round($product->average_rating))
                                          <span>Ã¢Ëœâ€¦</span>
                                      @else
                                          <span class="text-gray-300">Ã¢Ëœâ€¦</span>
                                      @endif
                                  @endfor
                              </div>
                              <span class="text-xs font-bold text-gray-900 ml-1.5">{{ number_format($product->average_rating, 1) }}</span>
                          </div>
                          <span class="text-gray-300">&bull;</span>
                          <a href="#" wire:click.prevent="setActiveTab('reviews')" class="text-xs text-gray-500 font-medium hover:text-brand-600 transition">{{ $product->approved_reviews_count }} Verified Customer {{ Str::plural('Rating', $product->approved_reviews_count) }}</a>
                      @else
                          <span class="text-xs text-gray-400 italic">Not reviewed yet</span>
                      @endif
                      <span class="text-gray-300">&bull;</span>
                      <span class="text-[11px] text-emerald-600 font-bold">100% Authentic</span>
                  </div>
            </div>

            @php
                $flashDeal = $product->getActiveFlashSaleDeal();
                $effectivePrice = $product->current_price;
                $originalPrice = (float) $product->price;
                
                if ($selectedVariationId) {
                    $variation = $product->variations->firstWhere('id', $selectedVariationId);
                    if ($variation) {
                        $effectivePrice = $variation->sale_price ?: ($variation->price ?: $effectivePrice);
                        $originalPrice = $variation->price ?: $originalPrice;
                    }
                }
                
                $hasDiscount = ($effectivePrice < $originalPrice);
            @endphp
            <div class="p-4 sm:p-5 rounded-2xl {{ $flashDeal ? 'bg-gradient-to-r from-rose-950 via-gray-900 to-rose-950 text-white border border-rose-900/50 shadow-xl' : 'bg-gradient-to-r from-gray-50 via-brand-50/20 to-gray-50 border border-gray-100' }} space-y-2 relative overflow-hidden">
                @if($flashDeal)
                    <div class="flex items-center justify-between pb-1.5 border-b border-white/10">
                        <span class="inline-flex items-center gap-1.5 text-xs font-black text-rose-400 uppercase tracking-wider">
                            <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                            <span>Ã¢Å¡Â¡ Mega Flash Deal Active</span>
                        </span>
                        <span class="text-[10px] font-bold bg-rose-600 text-white px-2 py-0.5 rounded-md shadow-sm">
                            -{{ $flashDeal->discount_percentage }}% OFF
                        </span>
                    </div>
                @endif

                <div class="flex flex-wrap sm:flex-nowrap items-baseline gap-2 sm:gap-3">
                    <span class="text-2xl sm:text-3xl lg:text-4xl font-black {{ $flashDeal ? 'text-rose-400' : 'text-gray-900' }} tracking-tight whitespace-nowrap">
                        BDT {{ number_format($effectivePrice) }}
                    </span>
                    @if($hasDiscount)
                        <span class="text-sm sm:text-lg {{ $flashDeal ? 'text-gray-400' : 'text-gray-400' }} line-through font-semibold whitespace-nowrap">
                            BDT {{ number_format($originalPrice) }}
                        </span>
                        <span class="text-[10px] sm:text-xs font-bold {{ $flashDeal ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30' : 'bg-rose-100 text-rose-700' }} px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-lg whitespace-nowrap">
                            Save BDT {{ number_format($originalPrice - $effectivePrice) }}
                        </span>
                    @endif
                </div>
                <p class="text-[11px] {{ $flashDeal ? 'text-gray-300' : 'text-gray-500' }} flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Inclusive of all VAT &bull; Eligible for Free Delivery inside Dhaka</span>
                </p>
            </div>

            @php
                $currentStock = $product->stock_quantity ?? 0;
                if ($selectedVariationId) {
                    $variation = $product->variations->firstWhere('id', $selectedVariationId);
                    $currentStock = $variation ? $variation->stock_quantity : $currentStock;
                }
            @endphp
            @if($currentStock > 0 && $currentStock <= 5)
                <div class="flex items-center gap-2 p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-semibold">
                    <span class="text-base">Ã°Å¸â€Â¥</span>
                    <span>High demand item: Only <strong>{{ $currentStock }} items left</strong> in stock!</span>
                </div>
            @endif

              @if($product->has_variations && !empty($product->attributes_schema))
                  <div class="space-y-4 py-4 border-t border-b border-gray-100">
                      @foreach($product->attributes_schema as $attribute)
                          <div>
                              <h3 class="text-sm font-medium text-gray-700 mb-3">{{ $attribute['name'] ?? '' }}:</h3>
                              <div class="flex flex-wrap gap-3">
                                  @if(isset($attribute['type']) && $attribute['type'] === 'color' && isset($attribute['options']))
                                      @foreach($attribute['options'] as $option)
                                          <button 
                                              type="button"
                                              wire:click="selectAttribute('{{ $attribute['name'] }}', '{{ addslashes($option['label'] ?? '') }}', '{{ $option['image'] ?? '' }}', '{{ $option['price'] ?? 0 }}')"
                                              class="w-10 h-10 rounded-full flex items-center justify-center transition-all relative group {{ isset($selectedAttributes[$attribute['name']]) && $selectedAttributes[$attribute['name']] === ($option['label'] ?? '') ? 'ring-2 ring-offset-2 ring-gray-900 scale-110' : 'ring-1 ring-gray-200 hover:scale-105' }}"
                                              style="background-color: {{ $option['color_code'] ?? '#f3f4f6' }};"
                                          >
                                              
                                              <div class="absolute bottom-full mb-2 hidden group-hover:block whitespace-nowrap bg-gray-900 text-white text-[10px] px-2 py-1 rounded shadow-lg z-10">{{ $option['label'] ?? '' }}</div>
                                          </button>
                                      @endforeach
                                  @else
                                      @php
                                          // Fallback for old format or text buttons
                                          $optionsList = isset($attribute['options']) ? $attribute['options'] : [];
                                          if (isset($attribute['values'])) {
                                              foreach($attribute['values'] as $val) {
                                                  $optionsList[] = ['label' => $val];
                                              }
                                          }
                                      @endphp
                                      @foreach($optionsList as $option)
                                          <button 
                                              type="button"
                                              wire:click="selectAttribute('{{ $attribute['name'] }}', '{{ addslashes($option['label'] ?? '') }}')"
                                              class="min-w-[48px] px-4 py-2 border rounded-xl text-sm font-medium transition-all {{ isset($selectedAttributes[$attribute['name']]) && $selectedAttributes[$attribute['name']] === ($option['label'] ?? '') ? 'bg-gray-900 border-gray-900 text-white shadow-md' : 'bg-white text-gray-900 hover:bg-gray-50 border-gray-200' }}"
                                          >
                                              {{ $option['label'] ?? '' }}
                                          </button>
                                      @endforeach
                                  @endif
                              </div>
                          </div>
                      @endforeach
                  </div>
              @elseif($product->has_variations && $product->variations->isNotEmpty())
                  <div class="space-y-3 py-3 border-t border-b border-gray-100">
                      <h3 class="text-sm font-bold text-gray-900">Select Option:</h3>
                      <div class="flex flex-wrap gap-2">
                          @foreach($product->variations as $variation)
                              <button 
                                  type="button"
                                  wire:click="selectVariation({{ $variation->id }})"
                                  class="px-4 py-2 border-2 rounded-xl text-xs font-bold transition-all {{ $selectedVariationId === $variation->id ? 'border-brand-500 bg-[#ffeb99] text-gray-900 shadow-sm' : 'border-gray-200 text-gray-600 hover:border-gray-300 bg-white' }}"
                              >
                                  {{ $variation->variation_name }}
                              </button>
                          @endforeach
                      </div>
                  </div>
              @endif

            @if(session()->has('cart_error'))
                <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-bold flex items-center gap-2">
                    <span>Ã¢Å¡Â Ã¯Â¸Â</span>
                    <span>{{ session('cart_error') }}</span>
                </div>
            @endif

            <div class="space-y-3 pt-4 sm:pt-2 sticky bottom-0 z-50 sm:relative sm:z-auto bg-white/95 sm:bg-transparent backdrop-blur-xl sm:backdrop-blur-none p-4 sm:p-0 -mx-4 sm:mx-0 border-t sm:border-t-0 border-gray-200 shadow-[0_-15px_30px_-15px_rgba(0,0,0,0.15)] sm:shadow-none rounded-t-[24px] sm:rounded-none">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    
                    <div class="flex items-center justify-between border-2 border-gray-200 rounded-2xl bg-white px-3 py-1.5 w-full sm:w-36 shrink-0">
                        <button 
                            type="button" 
                            wire:click="decreaseQuantity" 
                            class="w-8 h-8 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold flex items-center justify-center transition disabled:opacity-30"
                            {{ $quantity <= 1 ? 'disabled' : '' }}
                        >
                            -
                        </button>
                        <span class="font-extrabold text-sm text-gray-900">{{ $quantity }}</span>
                        <button 
                            type="button" 
                            wire:click="increaseQuantity" 
                            class="w-8 h-8 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold flex items-center justify-center transition disabled:opacity-30"
                            {{ $quantity >= $currentStock ? 'disabled' : '' }}
                        >
                            +
                        </button>
                    </div>

                    <button 
                        type="button" 
                        wire:click="addToCart" onclick="if(typeof fbq === 'function') fbq('track', 'AddToCart', {content_ids: ['{{ $product->id }}'], content_type: 'product'});" 
                        wire:loading.attr="disabled"
                        class="flex-1 py-4 px-6 bg-gray-900 hover:bg-black disabled:bg-gray-200 disabled:text-gray-400 text-white font-bold text-sm sm:text-base rounded-2xl sm:rounded-[20px] shadow-xl hover:shadow-2xl shadow-gray-900/20 transition-all duration-300 flex items-center justify-center gap-2 cursor-pointer active:scale-95 border border-gray-800 disabled:border-transparent group"
                        {{ $currentStock <= 0 ? 'disabled' : '' }}
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 11h14l1 12H4L5 11z"/>
                        </svg>
                        <span>{{ $currentStock <= 0 ? 'Out of Stock' : ($product->has_variations && (!empty($product->attributes_schema) ? !$allAttributesSelected : !$selectedVariationId) ? 'Select Options' : 'Add to Cart') }}</span>
                    </button>
                </div>

                <button 
                    type="button" 
                    wire:click="buyNow" onclick="if(typeof fbq === 'function') fbq('track', 'InitiateCheckout', {content_ids: ['{{ $product->id }}'], content_type: 'product'});" 
                    wire:loading.attr="disabled"
                    class="w-full py-4 px-6 bg-gray-950 hover:bg-gray-900 text-white text-xs sm:text-sm font-bold rounded-2xl shadow-md  disabled:bg-gray-400 disabled:border-gray-400 disabled:opacity-60 disabled:cursor-not-allowed transition-all duration-300 flex items-center justify-center gap-2 border border-gray-800 "
                    {{ $currentStock <= 0 ? 'disabled' : '' }}
                >
                    <span>Ã¢Å¡Â¡ {{ $currentStock <= 0 ? 'Out of Stock' : ($product->has_variations && (!empty($product->attributes_schema) ? !$allAttributesSelected : !$selectedVariationId) ? 'Select Options' : 'Buy Now') }}</span>
                      @if($currentStock > 0) <span>&rarr;</span> @endif
                </button>
            </div>

            <div class="rounded-2xl bg-white border border-gray-100 p-5 shadow-sm divide-y divide-gray-100 text-xs">
                <div class="flex items-start gap-3.5 pb-3">
                    <div class="w-9 h-9 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900">Express Delivery</h4>
                        <p class="text-gray-500 mt-0.5">Inside Dhaka: <strong>24-48 Hours</strong> (BDT 80) &bull; Nationwide: <strong>2-3 Days</strong> (BDT 150)</p>
                    </div>
                </div>

                <div class="flex items-start gap-3.5 py-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900">100% Authentic Guarantee</h4>
                        <p class="text-gray-500 mt-0.5">Sourced directly from verified distributors with origin guarantee.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3.5 pt-3">
                    <div class="w-9 h-9 rounded-xl bg-pink-50 text-[#e2136e] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900">Flexible Payment</h4>
                        <p class="text-gray-500 mt-0.5">Instant bKash Tokenized Checkout, Cards, or Cash on Delivery.</p>
                    </div>
                </div>
            </div>

            @if($product->crossSells && $product->crossSells->isNotEmpty())
                <div class="rounded-2xl bg-white border border-gray-100 p-5 shadow-sm space-y-4">
                    <h3 class="font-bold text-gray-900 text-sm">Frequently Bought Together</h3>
                    
                    <div class="space-y-3">
                        
                        <div class="flex items-center gap-3 opacity-80">
                            <input type="checkbox" checked disabled class="rounded border-gray-300 text-gray-900 focus:ring-gray-900">
                            <div class="w-10 h-10 rounded-lg overflow-hidden shrink-0 border border-gray-100 bg-gray-50">
                                @if($product->image)
                                    <img loading="lazy" src="{{ asset('storage/' . $product->image) }}" class="w-full h-full object-cover">
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-xs font-bold text-gray-900 truncate">This item: {{ $product->name }}</h4>
                                <span class="text-xs text-brand-600 font-extrabold">BDT {{ number_format($product->sale_price ?? $product->price) }}</span>
                            </div>
                        </div>

                        @foreach($product->crossSells->where('is_active', true) as $crossSell)
                            <div class="flex items-center gap-3">
                                <input 
                                    type="checkbox" 
                                    wire:model.live="selectedCrossSells" 
                                    value="{{ $crossSell->id }}" 
                                    class="rounded border-gray-300 text-brand-600 focus:ring-brand-500 cursor-pointer"
                                >
                                <div class="w-10 h-10 rounded-lg overflow-hidden shrink-0 border border-gray-100 bg-gray-50">
                                    <a href="{{ route('product.detail', $crossSell->slug) }}" wire:navigate>
                                        @if($crossSell->image)
                                            <img loading="lazy" src="{{ asset('storage/' . $crossSell->image) }}" class="w-full h-full object-cover hover:scale-110 transition">
                                        @endif
                                    </a>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <a href="{{ route('product.detail', $crossSell->slug) }}" wire:navigate class="hover:text-brand-600 transition">
                                        <h4 class="text-[11px] font-medium text-gray-700 truncate">{{ $crossSell->name }}</h4>
                                    </a>
                                    <span class="text-xs font-bold text-gray-900">BDT {{ number_format($crossSell->sale_price ?? $crossSell->price) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    @php
                        $fbtTotalPrice = $product->sale_price ?? $product->price;
                        foreach($product->crossSells->where('is_active', true) as $cs) {
                            if(in_array($cs->id, $selectedCrossSells)) {
                                $fbtTotalPrice += ($cs->sale_price ?? $cs->price);
                            }
                        }
                    @endphp

                    <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
                        <div class="text-xs text-gray-500">
                            Total price: <br>
                            <span class="text-sm font-black text-gray-900">BDT {{ number_format($fbtTotalPrice) }}</span>
                        </div>
                        <button 
                            type="button" 
                            wire:click="addFrequentlyBoughtTogetherToCart" 
                            wire:loading.attr="disabled"
                            class="px-4 py-2 bg-brand-50 hover:bg-brand-100 text-brand-700 text-[11px] font-bold rounded-xl transition shadow-sm"
                        >
                            Add All to Bag
                        </button>
                    </div>
                </div>
            @endif

        </div>
    </div>

    <div class="bg-white rounded-3xl p-6 sm:p-10 border border-gray-100 shadow-sm space-y-6">
        <div class="flex items-center gap-3 border-b border-gray-100 overflow-x-auto pb-2">
            <button 
                type="button" 
                wire:click="setActiveTab('description')" 
                class="px-5 py-2.5 font-bold text-xs sm:text-sm rounded-xl transition whitespace-nowrap {{ $activeTab === 'description' ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50' }}"
            >
                Ã°Å¸â€œÂ Product Description & Benefits
            </button>

            <button 
                type="button" 
                wire:click="setActiveTab('how_to_use')" 
                class="px-5 py-2.5 font-bold text-xs sm:text-sm rounded-xl transition whitespace-nowrap {{ $activeTab === 'how_to_use' ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50' }}"
            >
                Ã°Å¸Å’Â¿ How to Use
            </button>

            <button 
                type="button" 
                wire:click="setActiveTab('ingredients')" 
                class="px-5 py-2.5 font-bold text-xs sm:text-sm rounded-xl transition whitespace-nowrap {{ $activeTab === 'ingredients' ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50' }}"
            >
                Ã°Å¸Â§Âª Ingredients & Material
            </button>

            <button 
                type="button" 
                wire:click="setActiveTab('shipping')" 
                class="px-5 py-2.5 font-bold text-xs sm:text-sm rounded-xl transition whitespace-nowrap {{ $activeTab === 'shipping' ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50' }}"
            >
                Ã°Å¸â€œÂ¦ Shipping & Returns Policy
            </button>

            <button 
                type="button" 
                wire:click="setActiveTab('reviews')" 
                class="px-5 py-2.5 font-bold text-xs sm:text-sm rounded-xl transition whitespace-nowrap flex items-center gap-1 {{ $activeTab === 'reviews' ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50' }}"
            >
                Ã¢Â­Â Reviews ({{ $product->approved_reviews_count }})
            </button>
        </div>

        <div class="text-xs sm:text-sm text-gray-700 leading-relaxed">
            @if($activeTab === 'description')
                <div class="space-y-4 max-w-3xl">
                    <h3 class="text-base font-bold text-gray-900">About {{ $product->name }}</h3>
                    <p class="whitespace-pre-line text-gray-600">
                        {{ $product->description ?: ($product->short_description ?: 'Experience premium luxury formulation and craftsmanship tailored to deliver maximum elegance and satisfaction.') }}
                    </p>
                    <ul class="space-y-2 pt-2 text-gray-600">
                        <li class="flex items-center gap-2">
                            <span class="text-emerald-500 font-bold">Ã¢Å“â€œ</span>
                            <span>100% Genuine, authentic formulation directly from brand origins.</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="text-emerald-500 font-bold">Ã¢Å“â€œ</span>
                            <span>Dermatologically tested / Couture craftsmanship standards.</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="text-emerald-500 font-bold">Ã¢Å“â€œ</span>
                            <span>Packaged securely in premium climate-controlled packaging.</span>
                        </li>
                    </ul>
                </div>
            @elseif($activeTab === 'how_to_use')
                <div class="space-y-3 max-w-3xl">
                    <h3 class="text-base font-bold text-gray-900">Recommended Application Ritual</h3>
                    <p class="text-gray-600">For optimal results and lasting radiance:</p>
                    <ol class="list-decimal pl-5 space-y-2 text-gray-600">
                        <li>Cleanse skin thoroughly or prepare fabric appropriately.</li>
                        <li>Apply a pea-sized amount evenly across face or style according to occasion.</li>
                        <li>Gently massage or drape until fully settled. Follow with moisturizer or suitable accessories.</li>
                        <li>Store in a cool, dry place away from direct sunlight.</li>
                    </ol>
                </div>
            @elseif($activeTab === 'ingredients')
                <div class="space-y-3 max-w-3xl">
                    <h3 class="text-base font-bold text-gray-900">Composition & Ingredients</h3>
                    <p class="text-gray-600">Formulated with premium active components and skin-loving nourishment:</p>
                    <div class="p-4 bg-gray-50 rounded-2xl font-mono text-xs text-gray-700 leading-relaxed border border-gray-100">
                        Aqua (Water), Niacinamide, Glycerin, Hyaluronic Acid, Centella Asiatica Extract, Tocopherol (Vitamin E), Allantoin, Ethylhexylglycerin, Phenoxyethanol.
                    </div>
                </div>
            @elseif($activeTab === 'shipping')
                <div class="space-y-3 max-w-3xl">
                    <h3 class="text-base font-bold text-gray-900">Shipping & 7-Day Hassle-Free Returns</h3>
                    <p class="text-gray-600">We take immense pride in ensuring rapid, secure parcel dispatch:</p>
                    <ul class="space-y-2 text-gray-600">
                        <li><strong>Inside Dhaka:</strong> Delivery within 24 to 48 hours via Steadfast / Pathao (BDT 80).</li>
                        <li><strong>Outside Dhaka:</strong> Delivery within 2 to 3 days across all 64 districts (BDT 150).</li>
                        <li><strong>Live Tracking:</strong> You will receive real-time SMS updates with live tracking link upon parcel dispatch.</li>
                        <li><strong>Returns:</strong> If the product arrives damaged or incorrect, contact us within 7 days for an instant replacement.</li>
                    </ul>
                </div>
            @elseif($activeTab === 'reviews')
                <div class="space-y-8 max-w-3xl">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Customer Reviews</h3>
                            <div class="flex items-center gap-2 mt-1">
                                <div class="flex text-amber-400 text-sm">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= round($product->average_rating))
                                            <span>Ã¢Ëœâ€¦</span>
                                        @else
                                            <span class="text-gray-300">Ã¢Ëœâ€¦</span>
                                        @endif
                                    @endfor
                                </div>
                                <span class="font-extrabold text-gray-900 text-lg">{{ number_format($product->average_rating, 1) }}</span>
                                <span class="text-gray-500 text-xs">({{ $product->approved_reviews_count }} reviews)</span>
                            </div>
                        </div>
                    </div>

                    @if($reviews->isEmpty())
                        <div class="text-center py-8 bg-gray-50 rounded-2xl border border-gray-100">
                            <p class="text-gray-500 font-medium">No reviews yet. Be the first to share your thoughts!</p>
                        </div>
                    @else
                        <div class="space-y-6">
                            @foreach($reviews as $review)
                                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                                    <div class="flex items-start justify-between mb-3">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <h4 class="font-bold text-gray-900">{{ $review->user->name ?? 'Anonymous' }}</h4>
                                                @if($review->is_verified_purchase)
                                                    <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-extrabold">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                        Verified Buyer
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="flex text-amber-400 text-xs mt-1">
                                                @for($i = 1; $i <= 5; $i++)
                                                    @if($i <= $review->rating) <span>Ã¢Ëœâ€¦</span> @else <span class="text-gray-200">Ã¢Ëœâ€¦</span> @endif
                                                @endfor
                                            </div>
                                        </div>
                                        <span class="text-[11px] text-gray-400">{{ $review->created_at->diffForHumans() }}</span>
                                    </div>

                                    @if($review->title)
                                        <h5 class="font-bold text-gray-800 text-sm mb-1">{{ $review->title }}</h5>
                                    @endif
                                    
                                    @if($review->comment)
                                        <p class="text-gray-600 text-xs sm:text-sm leading-relaxed mb-3">{{ $review->comment }}</p>
                                    @endif

                                    @if(!empty($review->images))
                                        <div class="flex flex-wrap gap-2 mt-2">
                                            @foreach($review->images as $img)
                                                <a href="{{ asset('storage/' . $img) }}" target="_blank" class="block w-16 h-16 rounded-xl overflow-hidden border border-gray-200 hover:border-brand-500 transition cursor-pointer">
                                                    <img loading="lazy" src="{{ asset('storage/' . $img) }}" class="w-full h-full object-cover">
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-8 bg-gray-50 p-6 rounded-3xl border border-gray-100">
                        <h4 class="font-extrabold text-gray-900 text-lg mb-4">Leave a Review</h4>
                        
                        @if(session()->has('review_success'))
                            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-bold mb-4 flex items-center gap-2">
                                <span>Ã°Å¸Å½â€°</span>
                                <span>{{ session('review_success') }}</span>
                            </div>
                        @elseif(session()->has('review_error'))
                            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-bold mb-4 flex items-center gap-2">
                                <span>Ã¢Å¡Â Ã¯Â¸Â</span>
                                <span>{{ session('review_error') }}</span>
                            </div>
                        @endif

                        @auth
                            <form wire:submit.prevent="submitReview" class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Rating <span class="text-rose-500">*</span></label>
                                    <div class="flex items-center gap-2">
                                        @for($i = 1; $i <= 5; $i++)
                                            <button type="button" wire:click="$set('reviewRating', {{ $i }})" class="text-2xl transition-transform hover:scale-110 {{ $reviewRating >= $i ? 'text-amber-400' : 'text-gray-300' }}">
                                                Ã¢Ëœâ€¦
                                            </button>
                                        @endfor
                                    </div>
                                    @error('reviewRating') <span class="text-[10px] text-rose-500 font-bold">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Title (Optional)</label>
                                    <input type="text" wire:model="reviewTitle" placeholder="Sum up your experience" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:border-brand-500 focus:ring-brand-500 transition">
                                    @error('reviewTitle') <span class="text-[10px] text-rose-500 font-bold">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Comment (Optional)</label>
                                    <textarea wire:model="reviewComment" rows="4" placeholder="What did you love about this product?" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:border-brand-500 focus:ring-brand-500 transition"></textarea>
                                    @error('reviewComment') <span class="text-[10px] text-rose-500 font-bold">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Upload Photos (Optional)</label>
                                    <input type="file" wire:model="reviewImages" multiple accept="image/*" class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 transition cursor-pointer">
                                    <div wire:loading wire:target="reviewImages" class="text-[10px] text-brand-600 font-bold mt-1">Uploading images...</div>
                                    @error('reviewImages.*') <span class="text-[10px] text-rose-500 font-bold">{{ $message }}</span> @enderror
                                    
                                    @if($reviewImages)
                                        <div class="flex flex-wrap gap-2 mt-3">
                                            @foreach($reviewImages as $img)
                                                <div class="w-16 h-16 rounded-xl overflow-hidden border border-gray-200">
                                                    <img loading="lazy" src="{{ $img->temporaryUrl() }}" class="w-full h-full object-cover">
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-primary to-accent hover:from-primary-hover hover:to-primary text-white text-sm font-bold rounded-xl transition shadow-md active:scale-95 w-full sm:w-auto">
                                    Submit Review
                                </button>
                            </form>
                        @else
                            <div class="text-center py-6">
                                <p class="text-gray-600 text-sm mb-4">You must be logged in to leave a review.</p>
                                <a href="{{ route('login') }}" class="inline-flex px-6 py-2.5 bg-brand-50 hover:bg-brand-100 text-brand-700 font-bold rounded-xl transition">
                                    Log In or Sign Up
                                </a>
                            </div>
                        @endauth
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if($relatedProducts->isNotEmpty())
        <section class="space-y-6 pt-4">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] uppercase font-bold tracking-widest text-brand-600">Discover More</span>
                    <h2 class="text-2xl font-extrabold text-gray-900">Related Products</h2>
                </div>
                <a href="{{ route('home') }}" wire:navigate class="text-xs font-bold text-brand-600 hover:text-brand-700 transition">View All &rarr;</a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                @foreach($relatedProducts as $related)
                    <div class="bg-white rounded-2xl border border-gray-100 p-3 sm:p-4 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between group">
                        <div>
                            <a href="{{ route('product.detail', $related->slug) }}" wire:navigate class="block relative mb-3 overflow-hidden rounded-xl bg-gray-50 aspect-[4/5]">
                                @if($related->image)
                                    <img loading="lazy" src="{{ asset('storage/' . $related->image) }}" alt="{{ $related->name }}" class="w-full h-full object-cover group-hover:scale-108 transition-transform duration-500">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-300 text-xs">No Image</div>
                                @endif

                                @if($related->sale_price && $related->sale_price < $related->price)
                                    <span class="absolute top-2.5 left-2.5 bg-rose-500 text-white text-[10px] font-extrabold px-2 py-0.5 rounded-lg shadow">
                                        Sale
                                    </span>
                                @endif
                            </a>

                            <a href="{{ route('product.detail', $related->slug) }}" wire:navigate class="block">
                                <h4 class="font-bold text-xs sm:text-sm text-gray-900 group-hover:text-brand-600 transition line-clamp-2">
                                    {{ $related->name }}
                                </h4>
                            </a>
                        </div>

                        <div class="space-y-2 pt-2 border-t border-gray-50 mt-2">
                            <div class="flex items-baseline gap-1.5 overflow-hidden">
                                <span class="text-xs sm:text-sm font-extrabold text-gray-900 whitespace-nowrap">
                                    BDT {{ number_format($related->sale_price ?? $related->price) }}
                                </span>
                                @if($related->sale_price && $related->sale_price < $related->price)
                                    <span class="text-[10px] text-gray-400 line-through whitespace-nowrap">
                                        BDT {{ number_format($related->price) }}
                                    </span>
                                @endif
                            </div>
                            <a href="{{ route('product.detail', $related->slug) }}" wire:navigate class="w-full py-2 bg-gradient-to-r from-primary to-accent hover:from-primary-hover hover:to-primary text-white text-[11px] font-bold rounded-xl transition flex items-center justify-center gap-1">
                                View Details &rarr;
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

</div>
