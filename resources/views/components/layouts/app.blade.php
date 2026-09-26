@php
    $themeSettings = App\Services\ThemeService::getThemeSettings();
@endphp
<!DOCTYPE html>
<html lang="en" class="scroll-smooth !overflow-x-hidden !max-w-[100vw] !w-full relative m-0 p-0">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? ($themeSettings['store_name'] . ' â€“ Luxury Beauty & Fashion Store') }}</title>
    <link rel="icon" href="{{ !empty($themeSettings['favicon_image']) ? asset('storage/' . $themeSettings['favicon_image']) : asset('images/logo.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;700;900&family=Cormorant+Garamond:ital,wght@0,600;0,700;1,600&family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    @include('components.tracking-scripts')

    {!! App\Services\ThemeService::renderDynamicThemeStyles() !!}
    
    <style>
        
        .livewire-progress-bar {
            background-color: var(--color-primary, #000) !important;
            height: 3px !important;
            box-shadow: 0 0 10px var(--color-primary, #000), 0 0 5px var(--color-primary, #000);
            z-index: 999999 !important;
        }
    </style>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        window.reelsGlobalList = window.reelsGlobalList || [];
        window.beautyReelsSwiperInstance = null;
        window.reelsGlobalAudioMuted = true;
        window.reelsIntersectionObserver = null;

        window.triggerOpenReelModal = function(index) {
            const idx = parseInt(index);
            const reel = (window.reelsGlobalList && window.reelsGlobalList[idx]) ? window.reelsGlobalList[idx] : null;
            if (reel) {
                window.dispatchEvent(new CustomEvent('open-reel-modal', { detail: { index: idx, reel: reel } }));
            }
        };

        window.toggleReelsGlobalAudio = function(event) {
            if (event) event.stopPropagation();
            window.reelsGlobalAudioMuted = !window.reelsGlobalAudioMuted;

            document.querySelectorAll('.beauty-reels-swiper .reel-card-video').forEach(video => {
                video.muted = window.reelsGlobalAudioMuted;
            });

            document.querySelectorAll('.beauty-reels-swiper .mute-svg-muted').forEach(icon => {
                icon.classList.toggle('hidden', !window.reelsGlobalAudioMuted);
            });
            document.querySelectorAll('.beauty-reels-swiper .mute-svg-unmuted').forEach(icon => {
                icon.classList.toggle('hidden', window.reelsGlobalAudioMuted);
            });
        };

        window.preloadAllReelThumbnails = function() {
            if (window.reelsGlobalList && window.reelsGlobalList.length) {
                window.reelsGlobalList.forEach(reel => {
                    if (reel.poster_src) {
                        const img = new Image();
                        img.src = reel.poster_src;
                    }
                    if (reel.product_image) {
                        const pImg = new Image();
                        pImg.src = reel.product_image;
                    }
                });
            }
        };

        window.initBeautyReelsSwiper = function() {
            window.preloadAllReelThumbnails();
            if (typeof Swiper === 'undefined') {
                setTimeout(window.initBeautyReelsSwiper, 100);
                return;
            }

            const container = document.querySelector('.beauty-reels-swiper');
            if (!container) return;

            if (window.beautyReelsSwiperInstance) {
                try {
                    window.beautyReelsSwiperInstance.destroy(true, true);
                } catch (e) {}
            }

            window.beautyReelsSwiperInstance = new Swiper('.beauty-reels-swiper', {
                effect: 'coverflow',
                grabCursor: true,
                centeredSlides: true,
                slidesPerView: 'auto',
                spaceBetween: 0,
                initialSlide: 1,
                loop: true,
                loopedSlides: 10,
                speed: 600,
                preventClicks: false,
                preventClicksPropagation: false,
                slideToClickedSlide: true,
                autoplay: {
                    delay: 4500,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true,
                },
                coverflowEffect: {
                    rotate: 0,
                    stretch: 45,
                    depth: 180,
                    modifier: 1,
                    scale: 0.84,
                    slideShadows: false,
                },
                navigation: {
                    nextEl: '#reels-swiper-next-btn',
                    prevEl: '#reels-swiper-prev-btn',
                },
                on: {
                    init: function () {
                        const swiper = this;
                        // Single-stream video playback
                        setTimeout(() => {
                            window.playActiveReelVideo(swiper);
                        }, 250);
                    },
                    slideChangeTransitionStart: function () {
                        window.pauseAllReelVideos();
                    },
                    slideChangeTransitionEnd: function () {
                        window.playActiveReelVideo(this);
                    }
                }
            });

            // Set up IntersectionObserver so videos and autoplay pause when off-screen (saves 100% CPU/bandwidth)
            if ('IntersectionObserver' in window) {
                if (window.reelsIntersectionObserver) {
                    window.reelsIntersectionObserver.disconnect();
                }
                window.reelsIntersectionObserver = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            if (window.beautyReelsSwiperInstance) {
                                try {
                                    window.beautyReelsSwiperInstance.autoplay.start();
                                    window.playActiveReelVideo(window.beautyReelsSwiperInstance);
                                } catch (e) {}
                            }
                        } else {
                            if (window.beautyReelsSwiperInstance) {
                                try {
                                    window.beautyReelsSwiperInstance.autoplay.stop();
                                    window.pauseAllReelVideos();
                                } catch (e) {}
                            }
                        }
                    });
                }, { threshold: 0.2 });

                window.reelsIntersectionObserver.observe(container);
            }
        };

        window.pauseAllReelVideos = function() {
            document.querySelectorAll('.beauty-reels-swiper .reel-card-video').forEach(video => {
                try {
                    video.pause();
                } catch (e) {}
                const overlay = video.parentElement?.querySelector('.reel-play-overlay');
                if (overlay) overlay.style.opacity = '1';
            });
        };

        window.playActiveReelVideo = function(swiper) {
            if (!swiper) return;
            
            window.pauseAllReelVideos();

            const activeSlide = swiper.el?.querySelector('.swiper-slide-active') || (swiper.slides ? swiper.slides[swiper.activeIndex] : null);
            if (activeSlide) {
                const video = activeSlide.querySelector('.reel-card-video');
                if (video) {
                    video.muted = window.reelsGlobalAudioMuted;
                    const playPromise = video.play();
                    if (playPromise !== undefined) {
                        playPromise.then(() => {
                            const overlay = activeSlide.querySelector('.reel-play-overlay');
                            if (overlay) overlay.style.opacity = '0';
                        }).catch(() => {});
                    }
                }
            }
        };

        // Delegated click handler on document
        document.addEventListener('click', function(e) {
            const trigger = e.target.closest('[data-reel-trigger]');
            if (trigger) {
                const idx = trigger.getAttribute('data-reel-trigger');
                if (idx !== null && idx !== undefined && typeof window.triggerOpenReelModal === 'function') {
                    window.triggerOpenReelModal(parseInt(idx));
                }
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            if (typeof window.initBeautyReelsSwiper === 'function') {
                window.initBeautyReelsSwiper();
            }
        });
        document.addEventListener('livewire:navigated', function() {
            if (typeof window.initBeautyReelsSwiper === 'function') {
                window.initBeautyReelsSwiper();
            }
        });
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @php
        $metaPixelId = \App\Models\Setting::get('meta_pixel_id');
        $ga4Id = \App\Models\Setting::get('ga4_measurement_id');
    @endphp

    @if($metaPixelId)
    
    <script data-navigate-once>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '{{ $metaPixelId }}');
    
    // Fire PageView immediately on first hard load
    fbq('track', 'PageView');

    // Fire PageView on every subsequent Livewire SPA navigation
    document.addEventListener('livewire:navigated', () => {
        // Prevent double firing on the initial hard load by checking a flag
        if (!window.pixelInitialFired) {
            window.pixelInitialFired = true;
            return;
        }
        fbq('track', 'PageView');
    });
    </script>
    <noscript><img height="1" width="1" style="display:none"
    src="https://www.facebook.com/tr?id={{ $metaPixelId }}&ev=PageView&noscript=1"
    /></noscript>
    
    @endif

    @if($ga4Id)
    
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga4Id }}"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '{{ $ga4Id }}');
    </script>
    @endif
</head>
<body class="bg-[#fafafa] text-gray-900 font-sans antialiased selection:bg-brand-500 selection:text-white flex flex-col min-h-screen !w-full !max-w-[100vw] !overflow-x-hidden relative m-0 p-0 pb-16 md:pb-0">

    <div 
        @if($themeSettings['navbar_hide_on_scroll'])
        x-data="{
            isNavHidden: false,
            lastScrollY: 0,
            init() {
                this.lastScrollY = window.pageYOffset || document.documentElement.scrollTop;
            },
            handleScroll() {
                const currentScrollY = window.pageYOffset || document.documentElement.scrollTop;
                if (currentScrollY > this.lastScrollY && currentScrollY > 70) {
                    this.isNavHidden = true;
                } else if (currentScrollY < this.lastScrollY || currentScrollY <= 10) {
                    this.isNavHidden = false;
                }
                this.lastScrollY = currentScrollY <= 0 ? 0 : currentScrollY;
            }
        }"
        @scroll.window.passive="handleScroll()"
        class="fixed top-0 inset-x-0 z-[100] transition-transform duration-300 ease-out transform"
        :class="isNavHidden ? '-translate-y-full' : 'translate-y-0'"
        @else
        class="fixed top-0 inset-x-0 z-[100]"
        @endif
    >
        
        @if(filter_var(App\Models\Setting::get('announcement_bar_enabled', true), FILTER_VALIDATE_BOOLEAN))
        <div class="theme-announcement-bar bg-gradient-to-r from-gray-950 via-brand-950 to-gray-950 text-white text-xs py-1.5 px-3 sm:px-4 border-b border-brand-900/30 w-full max-w-full overflow-hidden">
            <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8 flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2 min-w-0 max-w-full">
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse shrink-0"></span>
                    <span class="font-medium text-[11px] sm:text-xs truncate">{{ App\Services\SettingService::getAnnouncementText() }}</span>
                </div>
                <div class="hidden md:flex items-center space-x-6 text-[11px] opacity-90 shrink-0">
                    <span class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        100% Authentic Guarantee
                    </span>
                    <span class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        bKash, Cards & Cash on Delivery
                    </span>
                    <a href="{{ route('order.track') }}" wire:navigate class="hover:underline font-semibold underline-offset-2">Live Order Tracker</a>
                </div>
            </div>
        </div>
        @endif

        <livewire:header />
    </div>

    <main class="flex-grow max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 pt-32 sm:pt-36 lg:pt-40 pb-6 sm:pb-8 lg:pb-10">
        {{ $slot }}
    </main>

    <footer class="theme-footer-bg bg-gray-950 text-gray-400 pt-16 pb-12 border-t border-gray-900 mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10 pb-12 border-b border-gray-800/80">
                
                <div class="lg:col-span-2 space-y-4">
                    <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-3.5 group">
                        <img src="{{ asset('images/logo.png') }}" alt="Kazi Fashion World" class="h-12 sm:h-14 w-auto object-contain brightness-0 invert opacity-95 group-hover:opacity-100 transition">
                        <div class="flex flex-col">
                            <span class="text-xl font-extrabold tracking-tight text-white leading-none">
                                KAZI <span class="text-brand-500">FASHION</span>
                            </span>
                            <span class="text-[10px] uppercase font-bold tracking-[0.25em] text-brand-400 mt-1">
                                WORLD &bull; LUXURY APPAREL
                            </span>
                        </div>
                    </a>
                    <p class="text-xs leading-relaxed text-gray-400 max-w-sm">
                        Your premier destination for 100% authentic international skincare, cosmetics, designer sharara, and luxury gowns delivered safely across Bangladesh.
                    </p>
                    <div class="flex items-center gap-3 pt-2">
                        
                        <a href="https://www.facebook.com/kajifashion" target="_blank" rel="noopener noreferrer" 
                        class="w-8 h-8 rounded-full bg-gray-900 hover:bg-brand-600 text-white flex items-center justify-center transition" 
                        title="Facebook" aria-label="Facebook">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>

                        <a href="https://instagram.com/kazi_fashionworld" target="_blank" rel="noopener noreferrer" 
                        class="w-8 h-8 rounded-full bg-gray-900 hover:bg-brand-600 text-white flex items-center justify-center transition" 
                        title="Instagram" aria-label="Instagram">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                            </svg>
                        </a>

                        <a href="https://youtube.com/@your-channel" target="_blank" rel="noopener noreferrer" 
                        class="w-8 h-8 rounded-full bg-gray-900 hover:bg-brand-600 text-white flex items-center justify-center transition" 
                        title="YouTube" aria-label="YouTube">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <div class="space-y-3">
                    <h3 class="text-xs font-bold text-white uppercase tracking-wider">Explore</h3>
                    <ul class="space-y-2 text-xs">
                        <li><a href="{{ route('home') }}" wire:navigate class="hover:text-brand-400 transition">Shop All</a></li>
                        <li><a href="{{ route('cart') }}" wire:navigate class="hover:text-brand-400 transition">Shopping Bag</a></li>
                        <li><a href="{{ route('order.track') }}" wire:navigate class="hover:text-brand-400 transition">Track Parcel</a></li>
                        @auth
                            <li><a href="{{ route('my-orders') }}" wire:navigate class="hover:text-brand-400 transition">My Orders</a></li>
                            <li><a href="{{ route('profile') }}" wire:navigate class="hover:text-brand-400 transition">My Account</a></li>
                        @else
                            <li><a href="{{ route('login') }}" wire:navigate class="hover:text-brand-400 transition">Sign In</a></li>
                            <li><a href="{{ route('register') }}" wire:navigate class="hover:text-brand-400 transition">Register</a></li>
                        @endauth
                    </ul>
                </div>

                <div class="space-y-3">
                    <h3 class="text-xs font-bold text-white uppercase tracking-wider">Customer Care & Legal</h3>
                    <ul class="space-y-2 text-xs">
                        <li><a href="{{ route('privacy') }}" wire:navigate class="hover:text-brand-400 transition">Privacy Policy</a></li>
                        <li><a href="{{ route('terms') }}" wire:navigate class="hover:text-brand-400 transition">Terms & Conditions</a></li>
                        <li><a href="{{ route('refund') }}" wire:navigate class="hover:text-brand-400 transition">Return & Refund Policy</a></li>
                    </ul>
                </div>

                <div class="space-y-3">
                    <h3 class="text-xs font-bold text-white uppercase tracking-wider">VIP Newsletter</h3>
                    <p class="text-xs text-gray-400">Subscribe for early access to exclusive drops and flash sales.</p>
                    <div class="flex">
                        <input type="email" placeholder="Your email address" class="w-full bg-gray-900 border border-gray-800 rounded-l-xl px-3 py-2 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-brand-500">
                        <button type="button" class="bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold px-3.5 py-2 rounded-r-xl transition">Join</button>
                    </div>
                </div>
            </div>

            <div class="pt-8 flex flex-col md:flex-row items-center justify-between gap-4 text-xs">
                <div class="flex flex-wrap items-center gap-4 text-gray-400">
                    <p>&copy; {{ date('Y') }} Kazi Fashion World. All rights reserved.</p>
                    <span class="text-gray-700">&bull;</span>
                    <a href="{{ route('page.show', 'terms-conditions') }}" wire:navigate class="hover:text-brand-400 transition">Terms & Conditions</a>
                    <span class="text-gray-700">&bull;</span>
                    <a href="{{ route('page.show', 'privacy-policy') }}" wire:navigate class="hover:text-brand-400 transition">Privacy Policy</a>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-[11px] text-gray-500 mr-2">Secure Payments:</span>
                    <span class="px-2.5 py-1 bg-gray-900 border border-gray-800 rounded-md text-[10px] font-bold text-pink-400">bKash</span>
                    <span class="px-2.5 py-1 bg-gray-900 border border-gray-800 rounded-md text-[10px] font-bold text-orange-400">Nagad</span>
                    <span class="px-2.5 py-1 bg-gray-900 border border-gray-800 rounded-md text-[10px] font-bold text-blue-400">Visa / Card</span>
                    <span class="px-2.5 py-1 bg-gray-900 border border-gray-800 rounded-md text-[10px] font-bold text-emerald-400">Cash on Delivery</span>
                </div>
            </div>
        </div>
    </footer>

    @if($themeSettings['social_proof_enabled'])
        <livewire:social-proof-toast />
    @endif

    @if($themeSettings['whatsapp_floating_enabled'] && !empty($themeSettings['whatsapp_number']))
        <a 
            href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $themeSettings['whatsapp_number']) }}?text={{ urlencode($themeSettings['whatsapp_greeting']) }}"
            target="_blank"
            rel="noopener noreferrer"
            class="fixed bottom-20 md:bottom-8 right-5 z-40 p-3.5 sm:p-4 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white shadow-2xl hover:scale-110 active:scale-95 transition-all duration-300 flex items-center justify-center group border-2 border-white/20"
            title="Instant WhatsApp Concierge"
        >
            <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
            </svg>
        </a>
    @endif

    @livewireScripts
</body>
</html>