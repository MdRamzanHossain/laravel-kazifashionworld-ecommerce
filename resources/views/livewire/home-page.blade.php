<div 
    class="space-y-16 py-4"
    x-data="{

        // Video Reel Carousel State
        isReelModalOpen: false,
        activeReel: null,
        activeReelIndex: 0,
        isMutedGlobal: true,

        async addToBagDirect(productObj) {
            if (!productObj || this.isAddingToCart) return;
            if (productObj.has_variations) {
                this.$dispatch('open-quick-view', productObj);
                return;
            }
            this.isAddingToCart = true;
            await $wire.addToCart(productObj.id, 1);
            this.isAddingToCart = false;
        },

        openReelModal(reel, index) {
            this.$dispatch('close-quick-view');
            if (!reel && window.reelsGlobalList && window.reelsGlobalList[index]) {
                reel = window.reelsGlobalList[index];
            }
            if (!reel) return;
            this.activeReel = reel;
            this.activeReelIndex = index;
            this.isReelModalOpen = true;
            this.isMutedGlobal = false;
            document.body.style.overflow = 'hidden';
            this.$nextTick(() => {
                const modalVid = document.getElementById('active-reel-modal-video');
                if (modalVid) {
                    modalVid.muted = false;
                    modalVid.currentTime = 0;
                    modalVid.play().catch(() => {});
                }
            });
        },
        closeReelModal() {
            const modalVid = document.getElementById('active-reel-modal-video');
            if (modalVid) {
                modalVid.pause();
            }
            this.isReelModalOpen = false;
            this.activeReel = null;
            document.body.style.overflow = '';
        },
        nextReel() {
            if (!this.reelsList || this.reelsList.length <= 1) return;
            this.activeReelIndex = (this.activeReelIndex + 1) % this.reelsList.length;
            this.activeReel = this.reelsList[this.activeReelIndex];
            this.$nextTick(() => {
                const modalVid = document.getElementById('active-reel-modal-video');
                if (modalVid) {
                    modalVid.currentTime = 0;
                    modalVid.play().catch(() => {});
                }
            });
        },
        prevReel() {
            if (!this.reelsList || this.reelsList.length <= 1) return;
            this.activeReelIndex = (this.activeReelIndex - 1 + this.reelsList.length) % this.reelsList.length;
            this.activeReel = this.reelsList[this.activeReelIndex];
            this.$nextTick(() => {
                const modalVid = document.getElementById('active-reel-modal-video');
                if (modalVid) {
                    modalVid.currentTime = 0;
                    modalVid.play().catch(() => {});
                }
            });
        },
        toggleMuteGlobal() {
            this.isMutedGlobal = !this.isMutedGlobal;
            const modalVid = document.getElementById('active-reel-modal-video');
            if (modalVid) {
                modalVid.muted = this.isMutedGlobal;
            }
        },
        scrollReels(direction) {
            const container = this.$refs.reelsScrollTrack;
            if (container) {
                const scrollAmount = direction === 'left' ? -340 : 340;
                container.scrollBy({ left: scrollAmount, behavior: 'smooth' });
            }
        },

    }"
    @open-reel-modal.window="openReelModal($event.detail.reel || (window.reelsGlobalList ? window.reelsGlobalList[$event.detail.index] : null), $event.detail.index)"
    @close-reel-modal.window="closeReelModal()"
    @keydown.escape.window="closeQuickView(); closeReelModal();"
>

    @if (session()->has('cart_success'))
        <div class="fixed bottom-6 right-6 z-50 bg-gray-950 text-white px-5 py-3.5 rounded-2xl shadow-2xl border border-gray-800 flex items-center gap-3 transition-all duration-300 transform translate-y-0" role="alert">
            <span class="w-7 h-7 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-sm">Ã¢Å“â€œ</span>
            <span class="text-xs font-semibold">{{ session('cart_success') }}</span>
            <a href="{{ route('cart') }}" wire:navigate class="text-xs bg-gradient-to-r from-primary to-accent hover:from-primary-hover hover:to-primary text-white font-bold px-3 py-1.5 rounded-xl ml-2 transition">View Bag &rarr;</a>
        </div>
    @endif

    <section class="relative rounded-3xl overflow-hidden bg-gradient-to-br from-gray-950 via-[#150712] to-gray-900 text-white p-6 sm:p-8 md:p-10 lg:px-14 lg:py-8 shadow-2xl border border-gray-800/80">
        
        <div class="absolute -top-28 -left-28 w-96 h-96 bg-primary/25 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-28 -right-28 w-96 h-96 bg-pink-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute top-1/2 right-1/4 w-64 h-64 bg-purple-600/15 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
            
            <div class="lg:col-span-7 space-y-5 sm:space-y-6">
                
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/15 text-[11px] sm:text-xs font-semibold text-brand-300 shadow-inner">
                    <span class="w-2 h-2 rounded-full bg-brand-400 animate-ping"></span>
                    <span>{{ $heroSettings['badge_text'] ?? 'Ã°Å¸Å’Â¸ 2026 Luxury Beauty & Festive Apparel' }}</span>
                </div>

                <h1 class="text-3xl sm:text-5xl lg:text-6xl xl:text-7xl font-extrabold tracking-tight leading-[1.08] text-white">
                    {{ $heroSettings['title_prefix'] ?? 'Reveal Your' }} <br class="hidden sm:inline">
                    <span class="font-serif italic font-normal text-transparent bg-clip-text bg-gradient-to-r from-brand-300 via-pink-200 to-rose-300 drop-shadow-sm">
                        {{ $heroSettings['title_highlight'] ?? 'True Elegance' }}
                    </span> 
                    {{ $heroSettings['title_suffix'] ?? '& Glow.' }}
                </h1>

                <p class="text-xs sm:text-sm md:text-base text-gray-300 max-w-xl leading-relaxed font-normal">
                    {{ $heroSettings['description'] ?? 'Discover 100% authentic international skincare, viral cosmetics, and handcrafted couture attire curated for every skin tone & occasion.' }}
                </p>

                <div class="flex flex-wrap items-center gap-3 sm:gap-4 pt-1">
                    <a 
                        href="{{ $heroSettings['primary_btn_url'] ?? '#products-section' }}" 
                        class="px-6 sm:px-8 py-3.5 bg-gradient-to-r from-brand-600 to-pink-500 hover:from-brand-500 hover:to-pink-400 text-white text-xs sm:text-sm font-bold rounded-2xl shadow-xl shadow-brand-500/25 transition transform hover:-translate-y-0.5 active:translate-y-0"
                    >
                        {{ $heroSettings['primary_btn_text'] ?? 'Shop Best Sellers Ã¢â€ â€™' }}
                    </a>
                    <a 
                        href="{{ $heroSettings['secondary_btn_url'] ?? '/track-order' }}" 
                        wire:navigate 
                        class="px-5 sm:px-7 py-3.5 bg-white/10 hover:bg-white/20 text-white text-xs sm:text-sm font-semibold rounded-2xl border border-white/15 backdrop-blur-md transition"
                    >
                        {{ $heroSettings['secondary_btn_text'] ?? 'Track Delivery' }}
                    </a>
                </div>

                <div class="grid grid-cols-3 gap-3 sm:gap-6 pt-5 sm:pt-6 border-t border-white/10 max-w-lg">
                    <div>
                        <span class="block text-lg sm:text-2xl font-extrabold text-white">{{ $heroSettings['stat1_value'] ?? '100%' }}</span>
                        <span class="text-[10px] sm:text-[11px] text-gray-400 block mt-0.5 leading-tight">{{ $heroSettings['stat1_label'] ?? 'Authentic Guaranteed' }}</span>
                    </div>
                    <div>
                        <span class="block text-lg sm:text-2xl font-extrabold text-brand-300">{{ $heroSettings['stat2_value'] ?? '5,000+' }}</span>
                        <span class="text-[10px] sm:text-[11px] text-gray-400 block mt-0.5 leading-tight">{{ $heroSettings['stat2_label'] ?? 'Happy Customers' }}</span>
                    </div>
                    <div>
                        <span class="block text-lg sm:text-2xl font-extrabold text-white">{{ $heroSettings['stat3_value'] ?? '24-48h' }}</span>
                        <span class="text-[10px] sm:text-[11px] text-gray-400 block mt-0.5 leading-tight">{{ $heroSettings['stat3_label'] ?? 'Express Delivery' }}</span>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-5 relative mt-8 lg:mt-0 w-full max-w-sm mx-auto perspective-1000" wire:ignore
                 x-data="{
                     initSwiper() {
                         if (typeof Swiper !== 'undefined') {
                             new Swiper('.hero-premium-swiper', {
                                   effect: 'slide',
                                   slidesPerView: 1,
                                   spaceBetween: 15,
                                   grabCursor: true,
                                   loop: true,
                                   speed: 400,
                                   autoplay: {
                                       delay: 3500,
                                       disableOnInteraction: false,
                                   },
                                   pagination: {
                                       el: '.swiper-pagination',
                                       clickable: true,
                                       dynamicBullets: true,
                                   },
                                   navigation: {
                                       nextEl: '.swiper-button-next-hero',
                                       prevEl: '.swiper-button-prev-hero',
                                   },
                               });
                         }
                     }
                 }"
                 x-init="setTimeout(() => initSwiper(), 150)"
            >
                <style>
                                        .hero-premium-swiper { touch-action: pan-y;
                        width: 100%;
                        max-width: 280px; 
                        padding-bottom: 35px; 
                    }
                    .hero-premium-swiper .swiper-slide {
                        aspect-ratio: 4 / 5; 
                        height: auto;
                        border-radius: 20px;
                        overflow: hidden;
                        background-color: #111;
                        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
                        border: 1px solid rgba(255,255,255,0.15);
                    }
                    .hero-premium-swiper .swiper-pagination-bullet {
                        background-color: rgba(255,255,255,0.4);
                    }
                    .hero-premium-swiper .swiper-pagination-bullet-active {
                        background-color: #fff;
                    }
                    .swiper-button-next-hero, .swiper-button-prev-hero {
                        position: absolute;
                        top: 50%;
                        transform: translateY(-50%);
                        z-index: 10;
                        width: 36px;
                        height: 36px;
                        background-color: rgba(255, 255, 255, 0.2);
                        backdrop-filter: blur(8px);
                        border: 1px solid rgba(255, 255, 255, 0.3);
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        color: white;
                        cursor: pointer;
                        transition: all 0.3s ease;
                    }
                    .swiper-button-next-hero:hover, .swiper-button-prev-hero:hover {
                        background-color: rgba(255, 255, 255, 0.4);
                        transform: translateY(-50%) scale(1.1);
                    }
                    .swiper-button-prev-hero { left: -15px; }
                    .swiper-button-next-hero { right: -15px; }
                    .swiper-button-next-hero::after, .swiper-button-prev-hero::after {
                        font-family: swiper-icons;
                        font-size: 14px;
                        font-weight: bold;
                    }
                    .swiper-button-prev-hero::after { content: 'prev'; }
                    .swiper-button-next-hero::after { content: 'next'; }
                </style>

                <div class="relative">
                    <div class="swiper hero-premium-swiper relative">
                        <div class="swiper-wrapper">
                        
                        @if(!empty($heroSettings['carousel_slides']))
                            @foreach($heroSettings['carousel_slides'] as $slide)
                                @if(!empty($slide['link_url']))
                                <a href="{{ $slide['link_url'] }}" class="swiper-slide group relative block cursor-pointer">
                                @else
                                <div class="swiper-slide group relative">
                                @endif
                                    <img loading="eager" fetchpriority="high" src="{{ asset('storage/' . $slide['image']) }}" alt="{{ $slide['title'] ?? 'Hero Collection' }}" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent pointer-events-none"></div>
                                    <div class="absolute bottom-6 left-5 right-5 text-white">
                                        <span class="text-[10px] font-bold uppercase tracking-widest text-brand-300 block mb-1">
                                            {{ $slide['tag'] ?? 'Featured Drop' }}
                                        </span>
                                        <h3 class="text-sm font-bold truncate">{{ $slide['title'] ?? 'Exclusive Collection' }}</h3>
                                    </div>
                                    @if(!empty($slide['badge']))
                                    <span class="absolute top-5 right-5 bg-white/20 backdrop-blur-md border border-white/20 text-white text-[10px] font-extrabold px-3 py-1.5 rounded-xl shadow-md">
                                        {{ $slide['badge'] }}
                                    </span>
                                    @endif
                                @if(!empty($slide['link_url']))
                                </a>
                                @else
                                </div>
                                @endif
                            @endforeach
                        @endif

                        @if(!empty($heroSettings['show_featured_products']) && $heroSettings['show_featured_products'])
                            @forelse($heroProducts as $product)
                                @if($product->image)
                                    <div class="swiper-slide group cursor-pointer relative" @click="$dispatch('open-quick-view', {{ json_encode([
                                        'id' => $product->id,
                                        'name' => $product->name,
                                        'price' => $product->price,
                                        'sale_price' => $product->sale_price,
                                        'effective_price' => $product->sale_price ? $product->sale_price : $product->price,
                                        'image' => asset('storage/' . $product->image),
                                        'images' => $product->gallery_image_urls,
                                        'category_name' => $product->category?->name ?? 'Beauty',
                                        'brand_name' => $product->brand?->name ?? null,
                                        'stock_quantity' => $product->stock_quantity ?? 50,
                                          'has_variations' => $product->has_variations,
                                  'attributes_schema' => $product->attributes_schema,
                                          'variations' => $product->has_variations ? $product->variations->map(fn($v) => [
                                              'id' => $v->id,
                                              'name' => $v->variation_name,
                                              'price' => (float)$v->price,
                                              'sale_price' => (float)$v->sale_price,
                                              'stock_quantity' => $v->stock_quantity,
                                          ])->toArray() : [],
                                        'average_rating' => $product->average_rating,
                                        'reviews_count' => $product->approved_reviews_count,
                                        'has_variations' => $product->has_variations,
                                          'attributes_schema' => $product->attributes_schema,
                                        'variations' => $product->has_variations ? $product->variations->map(fn($v) => [
                                            'id' => $v->id,
                                            'name' => $v->name,
                                            'price' => $v->price,
                                            'sale_price' => $v->sale_price,
                                        ])->toArray() : [],
                                        'url' => route('product.detail', $product->slug)
                                    ]) }})">
                                        <img loading="eager" fetchpriority="high" src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent pointer-events-none"></div>
                                        <div class="absolute bottom-6 left-5 right-5 text-white flex justify-between items-end gap-2">
                                            <div class="min-w-0">
                                                <span class="text-[10px] font-bold uppercase tracking-widest text-brand-300 block mb-1 truncate">
                                                    {{ $product->category?->name ?? 'Premium Drop' }}
                                                </span>
                                                <h3 class="text-sm font-bold leading-tight">{{ $product->name }}</h3>
                                            </div>
                                            <div class="shrink-0 text-right">
                                                @if($product->sale_price)
                                                    <span class="block text-[10px] line-through text-gray-400">Ã Â§Â³{{ number_format($product->price) }}</span>
                                                    <span class="block text-sm font-extrabold text-white">Ã Â§Â³{{ number_format($product->sale_price) }}</span>
                                                @else
                                                    <span class="block text-sm font-extrabold text-white">Ã Â§Â³{{ number_format($product->price) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <span class="absolute top-5 right-5 bg-gradient-to-r from-brand-600 to-pink-500 text-white text-[10px] font-extrabold px-3 py-1.5 rounded-xl shadow-md transition-transform group-hover:scale-105">
                                            Shop Now
                                        </span>
                                    </div>
                                @endif
                            @empty
                            @endforelse
                        @endif
                        
                        @if(empty($heroSettings['carousel_slides']) && (empty($heroSettings['show_featured_products']) || $heroProducts->isEmpty()))
                            
                            <div class="swiper-slide bg-gradient-to-tr from-brand-950 via-brand-900 to-pink-800 flex items-center justify-center">
                                <div class="text-center p-6 text-white space-y-2">
                                    <span class="text-4xl">Ã¢Å“Â¨</span>
                                    <p class="text-lg font-serif italic">Kazi Fashion World</p>
                                    <span class="text-xs bg-white/20 px-3 py-1 rounded-full inline-block">Exclusive Luxury</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="swiper-pagination"></div>
                </div>
                
                <div class="swiper-button-prev-hero"></div>
                <div class="swiper-button-next-hero"></div>
            </div>
        </div>
    </section>

    <section class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-[11px] uppercase font-bold tracking-widest text-brand-600">Browse by Department</span>
                <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900">Featured Categories</h2>
            </div>
            <span class="text-xs text-gray-500 font-medium hidden sm:inline">Tap to explore collection</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 sm:gap-6">
            @forelse($categories as $cat)
                <a 
                    href="{{ route('category.show', $cat->slug) }}" 
                    wire:navigate
                    class="group flex flex-col items-center gap-3 cursor-pointer"
                >
                    <div class="w-full aspect-square rounded-3xl overflow-hidden bg-gray-50 relative border border-gray-100 shadow-sm group-hover:shadow-xl group-hover:border-primary/40 transition-all duration-300">
                        @if($cat->image)
                            <img loading="lazy" src="{{ asset('storage/' . $cat->image) }}" alt="{{ $cat->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-out">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-tr from-brand-100 to-pink-50">
                                <svg class="w-10 h-10 text-brand-500 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                            </div>
                        @endif

                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors duration-300 flex items-center justify-center">
                            <span class="bg-white/95 backdrop-blur-sm text-gray-900 text-xs font-bold py-1.5 px-4 rounded-full opacity-0 group-hover:opacity-100 transform translate-y-4 group-hover:translate-y-0 transition-all duration-300 shadow-sm">
                                Explore
                            </span>
                        </div>
                    </div>
                    
                    <div class="text-center w-full px-1">
                        <h3 class="text-sm font-extrabold text-gray-900 group-hover:text-primary transition-colors truncate">{{ $cat->name }}</h3>
                        <span class="text-[11px] text-gray-500 font-semibold mt-0.5 flex items-center justify-center gap-1 group-hover:text-primary transition-colors">
                            Shop Now
                            <svg class="w-3 h-3 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </span>
                    </div>
                </a>
            @empty
                <p class="col-span-full text-xs text-gray-400">Add categories from the Admin Panel to display here.</p>
            @endforelse
        </div>
    </section>

    @if(isset($videoReels) && $videoReels->isNotEmpty())
    <section 
        class="space-y-4 pt-2 pb-2 relative min-h-[500px]"
        x-data="{ isReelsLoaded: false }"
        x-init="
            window.reelsGlobalList = [
                @foreach($videoReels as $reel)
                {
                    id: {{ $reel->id }},
                    title: {{ json_encode($reel->title) }},
                    overlay_heading: {{ json_encode($reel->overlay_heading) }},
                    badge_text: {{ json_encode($reel->badge_text) }},
                    video_src: {{ json_encode($reel->display_video_src) }},
                    poster_src: {{ json_encode($reel->display_poster_src) }},
                    product_name: {{ json_encode($reel->display_product_name) }},
                    category_name: {{ json_encode($reel->display_category) }},
                    price: {{ $reel->display_price }},
                    original_price: {{ $reel->display_original_price ?: 'null' }},
                    product_image: {{ json_encode($reel->display_product_image) }},
                    product_url: {{ json_encode($reel->product ? route('product.detail', $reel->product->slug) : ($reel->custom_product_url ?: '#')) }},
                    product_id: {{ $reel->product_id ?: 'null' }},
                    product_obj: {{ $reel->product ? json_encode([
                        'id' => $reel->product->id,
                        'name' => $reel->product->name,
                        'slug' => $reel->product->slug,
                        'price' => $reel->product->price,
                        'sale_price' => $reel->product->sale_price,
                        'effective_price' => $reel->product->effective_price,
                        'discount_pct' => $reel->product->discount_percentage,
                        'original_price' => $reel->product->original_price,
                        'image' => $reel->product->image ? asset('storage/' . $reel->product->image) : null,
                        'images' => $reel->product->gallery_image_urls,
                        'category_name' => $reel->product->category?->name ?? 'Beauty',
                        'brand_name' => $reel->product->brand?->name ?? null,
                        'stock_quantity' => $reel->product->stock_quantity ?? 50,
                          'average_rating' => $reel->product->average_rating,
                          'reviews_count' => $reel->product->approved_reviews_count,
                        'short_description' => $reel->product->short_description ?? '',
                        'has_variations' => $reel->product->has_variations,
                                  'attributes_schema' => $reel->product->attributes_schema,
                        'variations' => $reel->product->has_variations ? $reel->product->variations->map(fn($v) => [
                            'id' => $v->id,
                            'name' => $v->variation_name,
                            'price' => (float)$v->price,
                            'sale_price' => (float)$v->sale_price,
                            'stock_quantity' => $v->stock_quantity
                        ])->toArray() : [],
                        'url' => route('product.detail', $reel->product->slug)
                    ]) : 'null' }}
                }{{ !$loop->last ? ',' : '' }}
                @endforeach
            ];
            $nextTick(() => {
                if (typeof window.initBeautyReelsSwiper === 'function') {
                    window.initBeautyReelsSwiper();
                }
                // Simulate loading time to let images/videos buffer slightly and Swiper to calculate 3D layout
                setTimeout(() => { isReelsLoaded = true; }, 600);
            });
        "
    >
        <div class="px-1 sm:px-2 mb-3">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight uppercase font-sans flex items-center gap-2">
                    <svg class="w-6 h-6 sm:w-8 sm:h-8 text-brand-500" fill="currentColor" viewBox="0 0 24 24"><path d="M10 16.5l6-4.5-6-4.5v9zM12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/></svg>
                    Featured Reels
                </h2>
                <a href="/shop" class="text-sm font-semibold text-brand-600 hover:text-brand-700 hidden sm:block">View All</a>
            </div>
        </div>

        <style>
            @media (max-width: 767px) {
                .beauty-reels-swiper .swiper-slide:not(.swiper-slide-active):not(.swiper-slide-prev):not(.swiper-slide-next) {
                    opacity: 0 !important;
                    pointer-events: none !important;
                }
                .beauty-reels-swiper .swiper-slide-prev, 
                .beauty-reels-swiper .swiper-slide-next {
                    opacity: 0.7 !important;
                }
            }

            @media (min-width: 768px) {
                .beauty-reels-swiper .swiper-slide {
                    opacity: 0 !important;
                    pointer-events: none !important;
                }
                .beauty-reels-swiper .swiper-slide-active {
                    opacity: 1 !important;
                    pointer-events: auto !important;
                }
                .beauty-reels-swiper .swiper-slide-prev, 
                .beauty-reels-swiper .swiper-slide-next {
                    opacity: 0.85 !important;
                    pointer-events: auto !important;
                }
                .beauty-reels-swiper .swiper-slide-next + .swiper-slide,
                .beauty-reels-swiper .swiper-slide:has(+ .swiper-slide-prev) {
                    opacity: 0.5 !important;
                    pointer-events: auto !important;
                }
            }
        </style>

        <div class="grid grid-cols-1 grid-rows-1 w-full relative min-h-[480px]">
            <div class="col-start-1 row-start-1 w-full mx-auto overflow-hidden py-2 transition-opacity duration-700 ease-in-out opacity-0" :class="isReelsLoaded ? 'opacity-100' : 'opacity-0'" wire:ignore>
            <div class="swiper swiper-coverflow swiper-3d centered-swiper beauty-reels-swiper -mt-2 md:-mt-4 mb-2 overflow-visible !overflow-visible !pt-[10px] !pb-[20px]">
                <div class="swiper-wrapper">
                    @foreach($videoReels as $index => $reel)
                        <div class="swiper-slide !w-[260px] sm:!w-[275px] md:!w-[280px] !h-auto select-none !transition-all !duration-400 ease-out will-change-transform [&.swiper-slide-active]:!z-50 [&.swiper-slide-active]:!opacity-100 [&.swiper-slide-active]:drop-shadow-2xl [&.swiper-slide-prev]:!z-40 [&.swiper-slide-next]:!z-40 opacity-95" data-reel-index="{{ $index }}">
                            <div class="product-card transition-all duration-300 relative group">
                                
                                <div 
                                    onclick="window.triggerOpenReelModal({{ $index }})"
                                    data-reel-trigger="{{ $index }}"
                                    class="relative w-full h-[430px] sm:h-[460px] rounded-2xl overflow-hidden bg-black shadow-lg cursor-pointer ring-1 ring-white/10"
                                >
                                    
                                    <img loading="lazy" 
                                        src="{{ $reel->display_poster_src }}" 
                                        alt="{{ $reel->title }}" 
                                        class="absolute inset-0 w-full h-full object-cover pointer-events-none transition-transform duration-700 group-hover:scale-105"
                                    >

                                    <video 
                                        src="{{ $reel->display_video_src }}" 
                                        poster="{{ $reel->display_poster_src }}" 
                                        loop 
                                        playsinline 
                                        muted 
                                        preload="none" 
                                        class="absolute inset-0 w-full h-full object-cover cursor-pointer reel-card-video opacity-0 transition-opacity duration-500"
                                    ></video>

                                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-black/30 pointer-events-none opacity-80 group-hover:opacity-70 transition-opacity duration-300"></div>

                                    <div class="absolute top-3 left-3 px-2 py-1 bg-black/50 backdrop-blur-md rounded-md border border-white/10 flex items-center gap-1">
                                        <svg class="w-3 h-3 text-rose-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .78 1.29 1.5 1.87.565.45 1.25 1.13 1.25 2.13 0 .75-.38 1.5-1.38 2.12z" clip-rule="evenodd"></path></svg>
                                        <span class="text-white text-[10px] font-bold tracking-wider uppercase">Trending</span>
                                    </div>

                                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none reel-play-overlay transition-opacity duration-300">
                                        <div class="bg-white/20 backdrop-blur-md rounded-full p-3.5 shadow-[0_0_30px_rgba(255,255,255,0.3)] transition-all duration-300 group-hover:scale-110 group-hover:bg-white/30 border border-white/30">
                                            <svg width="32" height="32" viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M8 5V19L19 12L8 5Z"/>
                                            </svg>
                                        </div>
                                    </div>

                                    <button 
                                        type="button" 
                                        onclick="event.stopPropagation(); window.toggleReelsGlobalAudio(event);"
                                        class="absolute top-3 right-3 w-8 h-8 bg-black/40 backdrop-blur-md hover:bg-black/60 rounded-full flex items-center justify-center text-white transition-all z-10 cursor-pointer shadow border border-white/10"
                                        title="Toggle Audio"
                                    >
                                        <svg class="mute-svg-muted w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M16.5 12c0-1.77-1.02-3.29-2.5-4.03v2.21l2.45 2.45c.03-.2.05-.41.05-.63zm2.5 0c0 .94-.2 1.82-.54 2.64l1.51 1.51C20.63 14.91 21 13.5 21 12c0-4.28-2.99-7.86-7-8.77v2.06c2.89.86 5 3.54 5 6.71zM4.27 3L3 4.27 7.73 9H3v6h4l5 5v-6.73l4.25 4.25c-.67.52-1.42.93-2.25 1.18v2.06c1.38-.31 2.63-.95 3.69-1.81L19.73 21 21 19.73l-9-9L4.27 3zM12 4L9.91 6.09 12 8.18V4z"></path>
                                        </svg>
                                        <svg class="mute-svg-unmuted w-4 h-4 hidden" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"></path>
                                        </svg>
                                    </button>

                                    @if($reel->overlay_heading)
                                        <div class="absolute bottom-5 inset-x-4 z-10 text-left pointer-events-none">
                                            <p class="text-white font-bold text-sm sm:text-base leading-snug drop-shadow-md">
                                                {{ $reel->overlay_heading }}
                                            </p>
                                        </div>
                                    @endif
                                </div>

                                <div 
                                    onclick="window.triggerOpenReelModal({{ $index }})"
                                    data-reel-trigger="{{ $index }}"
                                    class="relative -mt-6 mx-3 bg-white/80 backdrop-blur-xl shadow-lg hover:shadow-2xl hover:bg-white rounded-2xl cursor-pointer transition-all duration-300 p-2.5 border border-white group/card z-20"
                                >
                                    <div class="flex items-center gap-3">
                                        <div class="relative w-14 h-14 shrink-0 rounded-xl overflow-hidden bg-gray-50 border border-gray-100 shadow-inner">
                                            <img loading="lazy" 
                                                alt="{{ $reel->display_product_name }}" 
                                                class="w-full h-full object-cover transition-transform duration-500 group-hover/card:scale-110" 
                                                src="{{ $reel->display_product_image }}"
                                            >
                                        </div>
                                        <div class="flex-1 min-w-0 py-0.5">
                                            <div class="font-bold text-gray-900 text-xs sm:text-[13px] truncate mb-0.5">
                                                {{ $reel->display_product_name ?: $reel->display_category }}
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-black text-brand-600 text-sm">BDT {{ number_format($reel->display_price) }}</span>
                                                @if($reel->display_original_price && $reel->display_original_price > $reel->display_price)
                                                    <span class="text-[10px] text-gray-400 font-medium line-through">BDT {{ number_format($reel->display_original_price) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="w-8 h-8 shrink-0 bg-brand-50 text-brand-600 rounded-full flex items-center justify-center transition-colors duration-300 group-hover/card:bg-brand-600 group-hover/card:text-white">
                                            <svg class="w-4 h-4 translate-x-[1px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    @endif

    @if($activeFlashSale && $activeFlashSale->flashSaleProducts->isNotEmpty())
        <section 
            x-data="{
                secondsLeft: {{ $activeFlashSale->remaining_seconds }},
                days: 0,
                hours: 0,
                minutes: 0,
                seconds: 0,
                timer: null,

                init() {
                    this.updateTime();
                    this.timer = setInterval(() => {
                        if (this.secondsLeft > 0) {
                            this.secondsLeft--;
                            this.updateTime();
                        } else {
                            clearInterval(this.timer);
                        }
                    }, 1000);
                },

                updateTime() {
                    this.days = Math.floor(this.secondsLeft / 86400);
                    this.hours = Math.floor((this.secondsLeft % 86400) / 3600);
                    this.minutes = Math.floor((this.secondsLeft % 3600) / 60);
                    this.seconds = this.secondsLeft % 60;
                },

                formatNumber(n) {
                    return String(n).padStart(2, '0');
                }
            }"
            class="rounded-3xl bg-gradient-to-r from-rose-950 via-gray-950 to-brand-950 text-white p-6 sm:p-8 md:p-10 border border-rose-900/40 shadow-2xl space-y-6 relative overflow-hidden"
        >
            
            <div class="absolute -right-20 -top-20 w-80 h-80 bg-rose-600/20 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6 pb-6 border-b border-white/10">
                <div class="space-y-1.5">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-500/20 text-rose-300 border border-rose-500/30 text-xs font-extrabold uppercase tracking-wider">
                        <span class="w-2 h-2 rounded-full bg-rose-400 animate-ping"></span>
                        <span>Ã¢Å¡Â¡ Limited Time Flash Deals</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                        {{ $activeFlashSale->title }}
                    </h2>
                    @if($activeFlashSale->subtitle)
                        <p class="text-xs sm:text-sm text-gray-300 font-normal max-w-xl">
                            {{ $activeFlashSale->subtitle }}
                        </p>
                    @endif
                </div>

                <div class="flex items-center gap-2 sm:gap-3 bg-black/40 backdrop-blur-md px-4 sm:px-6 py-3 rounded-2xl border border-white/10 shrink-0">
                    <span class="text-xs font-bold uppercase tracking-wider text-rose-300 mr-1 hidden sm:inline">Ends In:</span>

                    <template x-if="days > 0">
                        <div class="flex items-center gap-1.5">
                            <div class="bg-white/10 px-2.5 sm:px-3 py-1.5 rounded-xl text-center min-w-[40px]">
                                <span class="block text-base sm:text-xl font-black font-mono" x-text="formatNumber(days)"></span>
                                <span class="block text-[8px] text-gray-400 uppercase font-bold">Days</span>
                            </div>
                            <span class="text-rose-400 font-bold">:</span>
                        </div>
                    </template>

                    <div class="bg-white/10 px-2.5 sm:px-3 py-1.5 rounded-xl text-center min-w-[40px]">
                        <span class="block text-base sm:text-xl font-black font-mono" x-text="formatNumber(hours)"></span>
                        <span class="block text-[8px] text-gray-400 uppercase font-bold">Hours</span>
                    </div>
                    <span class="text-rose-400 font-bold">:</span>

                    <div class="bg-white/10 px-2.5 sm:px-3 py-1.5 rounded-xl text-center min-w-[40px]">
                        <span class="block text-base sm:text-xl font-black font-mono" x-text="formatNumber(minutes)"></span>
                        <span class="block text-[8px] text-gray-400 uppercase font-bold">Mins</span>
                    </div>
                    <span class="text-rose-400 font-bold">:</span>

                    <div class="bg-rose-600 px-2.5 sm:px-3 py-1.5 rounded-xl text-center min-w-[40px] text-white shadow-md">
                        <span class="block text-base sm:text-xl font-black font-mono" x-text="formatNumber(seconds)"></span>
                        <span class="block text-[8px] text-rose-200 uppercase font-bold">Secs</span>
                    </div>
                </div>
            </div>

            <div class="relative z-10 grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                @foreach($activeFlashSale->flashSaleProducts as $flashDeal)
                    @php
                        $p = $flashDeal->product;
                    @endphp
                    @if($p)
                        @php
                            $prodPayload = [
                                'id'                => $p->id,
                                'name'              => $p->name,
                                'image'             => $p->image ? asset('storage/' . $p->image) : null,
                                'images'            => $p->gallery_image_urls,
                                'price'             => (float)$p->price,
                                'effective_price'   => (float)$flashDeal->flash_price,
                                'discount_pct'      => $flashDeal->discount_percentage,
                                'is_flash_sale'     => true,
                                'category_name'     => $p->category ? $p->category->name : 'Flash Deals',
                                'brand_name'        => $p->brand ? $p->brand->name : null,
                                'short_description' => $p->short_description ?: '100% genuine verified formulation imported directly for authentic luxury and radiance.',
                                'stock_quantity'    => $p->stock_quantity,
                                'average_rating'    => $p->average_rating,
                                'reviews_count'     => $p->approved_reviews_count,
                                'sold_count'        => $flashDeal->sold_count,
                                'quantity_limit'    => $flashDeal->quantity_limit,
                                'has_variations'    => $p->has_variations,
                                  'attributes_schema' => $p->attributes_schema,
                                'variations'        => $p->has_variations ? $p->variations->map(fn($v) => [
                                    'id' => $v->id,
                                    'name' => $v->variation_name,
                                    'price' => (float)$v->price,
                                    'sale_price' => (float)$v->sale_price,
                                    'stock_quantity' => $v->stock_quantity
                                ])->toArray() : [],
                                'url'               => route('product.detail', $p->slug),
                            ];
                        @endphp
                        <div 
                            @click="$dispatch('open-quick-view', {{ json_encode($prodPayload) }})"
                            class="bg-white text-gray-900 rounded-3xl p-3.5 sm:p-4 shadow-xl border border-gray-100 flex flex-col justify-between group transition-all duration-300 hover:shadow-2xl hover:-translate-y-1 cursor-pointer relative"
                        >
                            <div>
                                
                                <div class="relative mb-3.5 overflow-hidden rounded-2xl bg-gradient-to-b from-gray-50 to-pink-50/20 aspect-[4/5] flex items-center justify-center">
                                    @if($p->image)
                                        <img loading="lazy" src="{{ asset('storage/' . $p->image) }}" alt="{{ $p->name }}" class="w-full h-full object-cover group-hover:scale-108 transition-transform duration-700 ease-out">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-300 text-xs">No Image</div>
                                    @endif

                                    <span class="absolute top-2.5 left-2.5 bg-gradient-to-r from-rose-600 to-pink-600 text-white text-[9px] sm:text-[10px] font-black px-2.5 py-0.5 rounded-lg shadow-md">
                                        -{{ $flashDeal->discount_percentage }}% OFF
                                    </span>

                                    <span class="absolute top-2.5 right-2.5 bg-gray-950/80 backdrop-blur-md text-amber-400 text-[10px] font-bold px-2 py-0.5 rounded-lg flex items-center gap-1">
                                        <span>Ã°Å¸â€Â¥</span>
                                        <span class="text-white text-[9px]">Flash</span>
                                    </span>

                                    <div class="absolute inset-x-3 bottom-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none hidden sm:block">
                                        <span class="w-full block text-center py-2 bg-gray-950/80 backdrop-blur-md text-white text-[10px] font-extrabold uppercase tracking-wider rounded-xl shadow-lg border border-white/10">
                                            Quick View Details
                                        </span>
                                    </div>
                                </div>

                                <div class="mb-1 flex items-center justify-between gap-1 text-[10px] font-extrabold uppercase tracking-[0.16em] text-brand-600">
                                    <span class="truncate">{{ $p->category ? $p->category->name : 'Flash Deal' }}</span>
                                    @if($p->brand)
                                        <span class="text-gray-400 truncate">{{ $p->brand->name }}</span>
                                    @endif
                                </div>

                                <h3 class="font-sans font-bold text-xs sm:text-sm text-gray-900 group-hover:text-brand-600 transition line-clamp-2 leading-snug tracking-tight">
                                    {{ $p->name }}
                                </h3>
                            </div>

                            <div class="space-y-2 pt-3 border-t border-gray-100 mt-2.5">
                                
                                <div class="flex items-baseline gap-2 overflow-hidden">
                                    <span class="text-sm sm:text-base font-black text-rose-600 whitespace-nowrap">
                                        BDT {{ number_format($flashDeal->flash_price) }}
                                    </span>
                                    <span class="text-[10px] sm:text-xs text-gray-400 line-through whitespace-nowrap">
                                        BDT {{ number_format($p->price) }}
                                    </span>
                                </div>

                                <div class="space-y-1">
                                    <div class="flex justify-between text-[10px] font-bold text-gray-500">
                                        <span class="text-rose-600">Ã°Å¸â€Â¥ {{ $flashDeal->sold_count }} Claimed</span>
                                        <span>{{ $flashDeal->quantity_limit - $flashDeal->sold_count }} Left</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                        <div class="bg-gradient-to-r from-rose-500 to-pink-500 h-full rounded-full transition-all duration-500" style="width: {{ $flashDeal->sold_percentage }}%"></div>
                                    </div>
                                </div>

                                <div class="pt-1 flex items-center justify-between text-xs font-bold text-gray-700 group-hover:text-rose-600 transition">
                                    <span class="text-[11px]">Explore Deal</span>
                                    <span>&rarr;</span>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </section>
    @endif

    <section id="products-section" class="space-y-6 pt-4">
        @if(!empty($search))
            <div class="p-4 bg-brand-50 border border-brand-200 rounded-2xl flex flex-wrap items-center justify-between gap-3 shadow-sm">
                <div class="flex items-center gap-2 text-xs">
                    <span class="font-bold text-brand-900">Showing search results for:</span>
                    <span class="font-extrabold text-brand-700 bg-white px-3 py-1 rounded-lg border border-brand-200 shadow-sm">"{{ $search }}"</span>
                    <span class="text-brand-600 font-medium">({{ $products->count() }} items found)</span>
                </div>
                <button 
                    type="button" 
                    wire:click="clearSearch"
                    class="text-xs font-bold text-brand-700 hover:text-brand-900 bg-white/80 hover:bg-white px-3 py-1 rounded-lg border border-brand-200 transition flex items-center gap-1"
                >
                    <span>Clear Search</span>
                    <span class="font-black">&times;</span>
                </button>
            </div>
        @endif

        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-gray-200 pb-4">
            <div>
                <span class="text-[11px] uppercase font-bold tracking-widest text-brand-600">Curated For You</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900">Explore Collection</h2>
            </div>

            <div class="flex flex-wrap items-center gap-2 overflow-x-auto pb-1">
                <button 
                    wire:click="selectCategory('all')" 
                    class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $selectedCategory === 'all' ? 'bg-gray-900 text-white shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}"
                >
                    All Products
                </button>

                <button 
                    wire:click="selectCategory('featured')" 
                    class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $selectedCategory === 'featured' ? 'bg-primary text-white shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}"
                >
                    Ã¢Å“Â¨ Featured
                </button>

                {{-- <button 
                    wire:click="selectCategory('sale')" 
                    class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $selectedCategory === 'sale' ? 'bg-rose-600 text-white shadow-sm' : 'bg-white text-rose-600 hover:bg-rose-50 border border-rose-200' }}"
                >
                    Ã°Å¸â€Â¥ Flash Deals
                </button> --}}

                @foreach($categories->take(5) as $cat)
                    <button 
                        wire:click="selectCategory({{ $cat->id }})" 
                        class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $selectedCategory == $cat->id ? 'bg-gray-900 text-white shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}"
                    >
                        {{ $cat->name }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
            @forelse($products as $product)
                @php
                    $flashDeal = $product->getActiveFlashSaleDeal();
                    $effectivePrice = $product->current_price;
                    $originalPrice = (float) $product->price;
                    $hasDiscount = ($effectivePrice < $originalPrice);
                    $discountPct = $hasDiscount ? round((($originalPrice - $effectivePrice) / $originalPrice) * 100) : 0;

                    $prodPayload = [
                        'id'                => $product->id,
                        'name'              => $product->name,
                        'image'             => $product->image ? asset('storage/' . $product->image) : null,
                        'images'            => $product->gallery_image_urls,
                        'price'             => $originalPrice,
                        'effective_price'   => (float)$effectivePrice,
                        'discount_pct'      => $discountPct,
                        'is_flash_sale'     => $flashDeal ? true : false,
                        'category_name'     => $product->category ? $product->category->name : 'Luxury Collection',
                        'brand_name'        => $product->brand ? $product->brand->name : null,
                        'short_description' => $product->short_description ?: '100% genuine verified formulation and couture apparel tailored for authentic luxury.',
                        'stock_quantity'    => $product->stock_quantity,
                                'average_rating'    => $product->average_rating,
                                'reviews_count'     => $product->approved_reviews_count,
                        'has_variations'    => $product->has_variations,
                                  'attributes_schema' => $product->attributes_schema,
                        'variations'        => $product->has_variations ? $product->variations->map(fn($v) => [
                            'id' => $v->id,
                            'name' => $v->variation_name,
                            'price' => (float)$v->price,
                            'sale_price' => (float)$v->sale_price,
                            'stock_quantity' => $v->stock_quantity
                        ])->toArray() : [],
                        'url'               => route('product.detail', $product->slug),
                    ];
                @endphp

                <div 
                    @click="$dispatch('open-quick-view', {{ json_encode($prodPayload) }})"
                    class="bg-white rounded-3xl border border-gray-100 p-3.5 sm:p-4 shadow-sm hover:shadow-2xl hover:border-brand-200 transition-all duration-500 flex flex-col justify-between group cursor-pointer relative transform hover:-translate-y-1"
                >
                    <div>
                        
                        <div class="relative mb-3.5 overflow-hidden rounded-2xl bg-gradient-to-b from-gray-50 to-pink-50/20 aspect-[4/5] flex items-center justify-center">
                            @if($product->image)
                                <img loading="lazy" 
                                    src="{{ asset('storage/' . $product->image) }}" 
                                    alt="{{ $product->name }}" 
                                    class="w-full h-full object-cover object-center group-hover:scale-108 transition-transform duration-700 ease-out"
                                >
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-300 font-medium text-xs">
                                    No Image
                                </div>
                            @endif

                            <div class="absolute top-2.5 left-2.5 flex flex-col gap-1 z-10 pointer-events-none">
                                @if($flashDeal)
                                    <span class="bg-rose-600 text-white text-[9px] sm:text-[10px] font-black px-2.5 py-0.5 rounded-lg shadow-md">
                                        Ã°Å¸â€Â¥ Flash Deal
                                    </span>
                                @elseif($hasDiscount)
                                    <span class="bg-gradient-to-r from-rose-600 to-pink-600 text-white text-[9px] sm:text-[10px] font-black px-2.5 py-0.5 rounded-lg shadow-md">
                                        -{{ $discountPct }}% OFF
                                    </span>
                                @endif

                                @if($product->is_featured)
                                    <span class="bg-gray-900/90 backdrop-blur-md text-white text-[8px] sm:text-[9px] font-bold px-2 py-0.5 rounded-md shadow-sm border border-white/10">
                                        Ã¢Å“Â¨ Luxury
                                    </span>
                                @endif
                            </div>

                            <div class="absolute inset-x-3 bottom-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none hidden sm:block">
                                <span class="w-full block text-center py-2 bg-gray-950/80 backdrop-blur-md text-white text-[10px] font-extrabold uppercase tracking-wider rounded-xl shadow-lg border border-white/10">
                                    Quick View Details
                                </span>
                            </div>
                        </div>

                        <div class="mb-1 flex items-center justify-between gap-1 text-[10px] font-extrabold uppercase tracking-[0.16em] text-brand-600">
                            <span class="truncate">
                                @if($product->category)
                                    {{ $product->category->name }}
                                @else
                                    Luxury Apparel
                                @endif
                            </span>
                            @if($product->brand)
                                <span class="text-gray-400 truncate">{{ $product->brand->name }}</span>
                            @endif
                        </div>

                        <h3 class="font-sans font-bold text-xs sm:text-sm text-gray-900 group-hover:text-brand-600 transition line-clamp-2 leading-snug tracking-tight">
                            {{ $product->name }}
                        </h3>

                          <div class="flex items-center justify-between text-[11px] mt-2 mb-2">
                              <div class="flex items-center text-amber-400 font-semibold gap-1">
                                  <span>★★★★★</span>
                                  <span class="text-gray-400 text-[10px] font-bold">4.9</span>
                              </div>
                            <span class="text-[10px] font-bold {{ ($product->stock_quantity ?? 0) > 0 ? 'text-emerald-600' : 'text-red-500' }}">
                                {{ ($product->stock_quantity ?? 0) > 0 ? 'In Stock' : 'Out of Stock' }}
                            </span>
                        </div>
                    </div>

                    <div class="space-y-2 pt-2.5 border-t border-gray-100 mt-2">
                        <div class="flex items-baseline gap-2 overflow-hidden">
                            <span class="text-sm sm:text-base font-black {{ $flashDeal ? 'text-rose-600' : 'text-gray-900' }} whitespace-nowrap">
                                BDT {{ number_format($effectivePrice) }}
                            </span>
                            @if($hasDiscount)
                                <span class="text-[10px] sm:text-xs text-gray-400 line-through whitespace-nowrap">
                                    BDT {{ number_format($originalPrice) }}
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center justify-between text-xs font-bold text-gray-800 group-hover:text-brand-600 transition pt-1">
                            <span class="text-[11px]">Explore Details</span>
                            <span>&rarr;</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 text-center bg-white rounded-3xl border border-gray-100 p-8 space-y-3">
                    <span class="text-4xl">Ã°Å¸â€ºÂÃ¯Â¸Â</span>
                    <h3 class="text-base font-bold text-gray-900">No products found</h3>
                    <p class="text-xs text-gray-400 max-w-sm mx-auto">There are currently no products available in this category. Try selecting another category or check back soon!</p>
                </div>
            @endforelse
        </div>
    </section>

    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 pt-8">
        <div class="p-6 rounded-2xl bg-white border border-gray-100 shadow-sm flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div>
                <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Fast Nationwide Delivery</h4>
                <p class="text-xs text-gray-500 mt-1">24-48h in Dhaka, 2-3 days nationwide via Steadfast & Pathao.</p>
            </div>
        </div>

        <div class="p-6 rounded-2xl bg-white border border-gray-100 shadow-sm flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div>
                <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wider">100% Authentic Guarantee</h4>
                <p class="text-xs text-gray-500 mt-1">Direct from official brand distributors with verification.</p>
            </div>
        </div>

        <div class="p-6 rounded-2xl bg-white border border-gray-100 shadow-sm flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-pink-50 text-[#e2136e] flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wider">bKash & Card Gateway</h4>
                <p class="text-xs text-gray-500 mt-1">Instant bKash OTP checkout, Cards, or Cash on Delivery.</p>
            </div>
        </div>

        <div class="p-6 rounded-2xl bg-white border border-gray-100 shadow-sm flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <div>
                <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wider">7-Day Easy Returns</h4>
                <p class="text-xs text-gray-500 mt-1">Hassle-free replacement policy with live SMS tracking.</p>
            </div>
        </div>
    </section>

    <section class="rounded-3xl bg-white p-8 md:p-12 border border-gray-100 shadow-sm space-y-8">
        <div class="text-center max-w-xl mx-auto space-y-2">
            <span class="text-[11px] uppercase font-bold tracking-widest text-brand-600">Loved by Thousands</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900">What Our Clients Say</h2>
            <p class="text-xs text-gray-500">Read genuine feedback from verified buyers across Bangladesh.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="p-6 rounded-2xl bg-gray-50/80 border border-gray-100 space-y-3">
                <div class="text-amber-400 text-sm">Ã¢Ëœâ€¦Ã¢Ëœâ€¦Ã¢Ëœâ€¦Ã¢Ëœâ€¦Ã¢Ëœâ€¦</div>
                <p class="text-xs text-gray-700 leading-relaxed italic">
                    "The Sharara set I ordered for Eid arrived within 24 hours in Dhaka. The embroidery and fabric quality exceeded my expectations. 100% recommended!"
                </p>
                <div class="flex items-center gap-3 pt-2">
                    <div class="w-8 h-8 rounded-full bg-brand-500 text-white font-bold text-xs flex items-center justify-center">N</div>
                    <div>
                        <h5 class="text-xs font-bold text-gray-900">Nusrat Jahan</h5>
                        <span class="text-[10px] text-emerald-600 font-semibold">Verified Buyer &bull; Gulshan</span>
                    </div>
                </div>
            </div>

            <div class="p-6 rounded-2xl bg-gray-50/80 border border-gray-100 space-y-3">
                <div class="text-amber-400 text-sm">Ã¢Ëœâ€¦Ã¢Ëœâ€¦Ã¢Ëœâ€¦Ã¢Ëœâ€¦Ã¢Ëœâ€¦</div>
                <p class="text-xs text-gray-700 leading-relaxed italic">
                    "Finally a store with genuine Korean skincare in Bangladesh. bKash payment was so smooth, and the live SMS tracking link kept me updated throughout!"
                </p>
                <div class="flex items-center gap-3 pt-2">
                    <div class="w-8 h-8 rounded-full bg-pink-500 text-white font-bold text-xs flex items-center justify-center">S</div>
                    <div>
                        <h5 class="text-xs font-bold text-gray-900">Sadia Rahman</h5>
                        <span class="text-[10px] text-emerald-600 font-semibold">Verified Buyer &bull; Dhanmondi</span>
                    </div>
                </div>
            </div>

            <div class="p-6 rounded-2xl bg-gray-50/80 border border-gray-100 space-y-3">
                <div class="text-amber-400 text-sm">Ã¢Ëœâ€¦Ã¢Ëœâ€¦Ã¢Ëœâ€¦Ã¢Ëœâ€¦Ã¢Ëœâ€¦</div>
                <p class="text-xs text-gray-700 leading-relaxed italic">
                    "The gown fitting is perfect! Customer support was so courteous on WhatsApp. Will definitely be shopping again for upcoming weddings."
                </p>
                <div class="flex items-center gap-3 pt-2">
                    <div class="w-8 h-8 rounded-full bg-indigo-500 text-white font-bold text-xs flex items-center justify-center">T</div>
                    <div>
                        <h5 class="text-xs font-bold text-gray-900">Tanjina Akhter</h5>
                        <span class="text-[10px] text-emerald-600 font-semibold">Verified Buyer &bull; Chittagong</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <x-quick-view-modal />

    <template x-teleport="body">
        <div 
            x-show="isReelModalOpen"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[99999] bg-black/85 backdrop-blur-md flex items-center justify-center p-3 sm:p-6 overflow-y-auto"
            style="display: none;"
            @click.self="closeReelModal()"
        >
            <div 
                x-show="isReelModalOpen"
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                class="relative w-full max-w-3xl sm:max-w-4xl mx-auto bg-white rounded-2xl overflow-hidden shadow-2xl border border-gray-100"
                @click.stop
            >
                
                <button 
                    type="button" 
                    @click="closeReelModal()" 
                    class="absolute top-4 right-4 z-30 w-8 h-8 bg-black/60 hover:bg-black/80 rounded-full flex items-center justify-center text-white transition-all cursor-pointer shadow-md"
                    title="Close"
                >
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M12 4L4 12M4 4L12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                    </svg>
                </button>

                <template x-if="activeReel">
                    <div class="flex flex-col lg:flex-row h-full max-h-[90vh] lg:max-h-[78vh]">
                        
                        <div class="lg:w-1/2 relative bg-gray-950 flex items-center justify-center p-4 sm:p-6 shrink-0">
                            <div class="relative w-[240px] sm:w-[260px] h-auto">
                                <div class="relative w-full h-auto rounded-xl overflow-hidden shadow-2xl bg-black">
                                    <video 
                                        id="active-reel-modal-video"
                                        :src="activeReel.video_src" 
                                        :poster="activeReel.poster_src"
                                        loop 
                                        playsinline 
                                        autoplay 
                                        preload="metadata" 
                                        class="w-full h-full object-cover cursor-pointer w-full h-auto object-contain rounded-lg" 
                                        style="aspect-ratio: 9 / 16;"
                                    ></video>

                                    <button 
                                        type="button" 
                                        @click="toggleMuteGlobal()"
                                        class="absolute top-4 right-4 w-8 h-8 bg-black bg-opacity-50 hover:bg-opacity-70 rounded-full flex items-center justify-center text-white transition-all z-10 cursor-pointer shadow"
                                        title="Toggle Audio"
                                    >
                                        <svg x-show="isMutedGlobal" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M16.5 12c0-1.77-1.02-3.29-2.5-4.03v2.21l2.45 2.45c.03-.2.05-.41.05-.63zm2.5 0c0 .94-.2 1.82-.54 2.64l1.51 1.51C20.63 14.91 21 13.5 21 12c0-4.28-2.99-7.86-7-8.77v2.06c2.89.86 5 3.54 5 6.71zM4.27 3L3 4.27 7.73 9H3v6h4l5 5v-6.73l4.25 4.25c-.67.52-1.42.93-2.25 1.18v2.06c1.38-.31 2.63-.95 3.69-1.81L19.73 21 21 19.73l-9-9L4.27 3zM12 4L9.91 6.09 12 8.18V4z"></path>
                                        </svg>
                                        <svg x-show="!isMutedGlobal" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"></path>
                                        </svg>
                                    </button>

                                    <template x-if="activeReel.overlay_heading">
                                        <div class="absolute bottom-3 inset-x-3 z-10 text-center pointer-events-none">
                                            <p class="text-white font-extrabold text-xs uppercase tracking-tight leading-snug drop-shadow-[0_2px_8px_rgba(0,0,0,0.9)]" x-text="activeReel.overlay_heading"></p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div class="lg:w-1/2 flex flex-col justify-between p-4 sm:p-6 bg-white overflow-y-auto">
                            <div class="space-y-3">
                                <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wider flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-primary"></span>
                                    Related Products
                                </h4>

                                <div class="relative bg-white border border-gray-100 shadow-md hover:shadow-lg rounded-xl p-4 overflow-hidden group transition-all">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="w-fit on-sale bg-rose-600 rounded-xl text-white font-bold text-xs px-2.5 py-0.5 shadow-sm">
                                            ON SALE
                                        </div>
                                        <span class="text-xs uppercase font-extrabold text-brand-600" x-text="activeReel.category_name"></span>
                                    </div>

                                    <a :href="activeReel.product_url" class="block py-2">
                                        <img loading="lazy" 
                                            :src="activeReel.product_image" 
                                            :alt="activeReel.product_name"
                                            class="mx-auto w-40 h-40 sm:w-48 sm:h-48 object-contain rounded-lg group-hover:scale-105 transition-all duration-300"
                                        >
                                    </a>

                                    <div class="bg-white pt-2 space-y-1.5 text-left">
                                        <span class="text-xs text-brand-500 uppercase font-black tracking-wider block" x-text="activeReel.category_name"></span>
                                        <a :href="activeReel.product_url">
                                            <h4 class="text-xs sm:text-sm font-bold text-gray-800 line-clamp-2 hover:text-brand-600 transition" x-text="activeReel.product_name"></h4>
                                        </a>
                                        <div class="flex items-center gap-x-2 pt-1">
                                            <p class="text-base sm:text-lg font-bold text-brand-600" x-text="`Ã Â§Â³ ${Number(activeReel.price).toLocaleString('en-US')}`"></p>
                                            <template x-if="activeReel.original_price && activeReel.original_price > activeReel.price">
                                                <p class="text-xs text-gray-500 line-through font-normal" x-text="`Ã Â§Â³ ${Number(activeReel.original_price).toLocaleString('en-US')}`"></p>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 pt-4 border-t border-gray-100 mt-4">
                                <template x-if="activeReel.product_obj">
                                    <button 
                                        type="button" 
                                        @click="addToBagDirect(activeReel.product_obj)" 
                                        :disabled="isAddingToCart"
                                        class="flex-1 py-3 px-4 bg-gradient-to-r from-primary to-accent hover:from-primary-hover hover:to-primary text-white font-bold text-xs sm:text-sm rounded-xl shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer active:scale-95 disabled:bg-gray-400"
                                    >
                                        <svg x-show="!activeReel.product_obj.has_variations" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 11h14l1 12H4L5 11z"/></svg>
                                        <span x-text="isAddingToCart ? 'Adding...' : (activeReel.product_obj.has_variations ? 'Select Options' : 'Add to Bag')"></span>
                                    </button>
                                </template>
                                <template x-if="!activeReel.product_obj">
                                    <a 
                                        :href="activeReel.product_url" 
                                        class="flex-1 py-3 px-4 bg-gradient-to-r from-primary to-accent hover:from-primary-hover hover:to-primary text-white font-bold text-xs sm:text-sm rounded-xl shadow-md transition-all flex items-center justify-center gap-2"
                                    >
                                        <span>Shop Product &rarr;</span>
                                    </a>
                                </template>

                                <a 
                                    :href="activeReel.product_url" 
                                    class="py-3 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs sm:text-sm rounded-xl transition"
                                >
                                    Details
                                </a>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </template>

</div>

