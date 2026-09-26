<header 
    class="bg-white/95 backdrop-blur-xl border-b border-gray-100/80 shadow-[0_10px_30px_-10px_rgba(0,0,0,0.08),0_4px_6px_-2px_rgba(0,0,0,0.02)] w-full max-w-full relative z-[100] transition-all duration-300"
    x-data="{ 
        isSearchFocused: false, 
        mobileSearchOpen: false,
        mobileDrawerOpen: false,
        activeCategoryDropdown: null,
        mobileAccordion: null
    }"
    @keydown.escape="isSearchFocused = false; mobileSearchOpen = false; mobileDrawerOpen = false; activeCategoryDropdown = null; $wire.closeSearch()"
    @keydown.window.ctrl.k.prevent="$refs.desktopSearchInput?.focus(); isSearchFocused = true; $wire.openSearch()"
    @keydown.window.cmd.k.prevent="$refs.desktopSearchInput?.focus(); isSearchFocused = true; $wire.openSearch()"
>
    <!-- ======================================================== -->
    <!-- 1. MAIN HEADER NAVBAR -->
    <!-- ======================================================== -->
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 w-full">
        <div class="flex items-center justify-between h-16 sm:h-20 gap-2 sm:gap-4 md:gap-6">
            
            <!-- Left: Mobile Menu Toggle & Brand Logo -->
            <div class="flex items-center gap-2 sm:gap-4 shrink min-w-0">
                <!-- Mobile Hamburger Button -->
                <button 
                    type="button"
                    @click="mobileDrawerOpen = true; document.body.style.overflow = 'hidden'"
                    class="lg:hidden p-2 rounded-2xl bg-gray-50 hover:bg-brand-50 text-gray-800 hover:text-brand-600 transition border border-gray-200/70 focus:outline-none cursor-pointer"
                    aria-label="Open menu"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <!-- Brand Logo & Monogram -->
                @php
                    $theme = App\Services\ThemeService::getThemeSettings();
                    $logoSrc = !empty($theme['logo_image']) ? asset('storage/' . $theme['logo_image']) : asset('images/logo.png');
                @endphp
                <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2.5 sm:gap-3.5 group shrink-0 select-none">
                    <div class="relative">
                        <img 
                            src="{{ $logoSrc }}" 
                            alt="{{ $theme['store_name'] }}" 
                            class="h-9 sm:h-12 md:h-13 w-auto object-contain shrink-0 group-hover:scale-105 transition-transform duration-300 drop-shadow-sm"
                        >
                    </div>
                    <div class="flex flex-col min-w-0">
                        <div class="flex items-center gap-1.5">
                            <span class="text-sm sm:text-lg md:text-xl font-extrabold tracking-tight text-gray-950 leading-none group-hover:text-brand-600 transition-colors truncate">
                                {{ $theme['store_name'] }}
                            </span>
                            <span class="hidden sm:inline-block text-[9px] font-black uppercase tracking-wider bg-brand-50 text-brand-700 px-1.5 py-0.5 rounded border border-brand-100">
                                LUXURY
                            </span>
                        </div>
                        <span class="text-[7px] sm:text-[9px] uppercase font-extrabold tracking-[0.2em] text-gray-400 mt-0.5 sm:mt-1 truncate">
                            {{ $theme['store_tagline'] }}
                        </span>
                    </div>
                </a>
            </div>

            <!-- Center: Intelligent Omnibox Search Bar (Desktop & Tablet) -->
            <div class="flex-1 max-w-xl hidden md:block relative" @click.outside="isSearchFocused = false; $wire.closeSearch()">
                <form wire:submit.prevent="performSearch" class="relative">
                    <input 
                        x-ref="desktopSearchInput"
                        type="text" 
                        wire:model.live.debounce.300ms="search"
                        @focus="isSearchFocused = true; $wire.openSearch()"
                        @click="isSearchFocused = true"
                        @input="isSearchFocused = true"
                        placeholder="Search luxury skincare, sharara, gowns, cosmetics..." 
                        class="w-full bg-gray-50/90 hover:bg-gray-100/70 focus:bg-white px-4 py-2.5 pl-11 pr-20 border border-gray-200/90 rounded-full focus:ring-2 focus:ring-brand-500/50 focus:border-brand-500 outline-none text-xs transition duration-200 text-gray-900 placeholder-gray-400 shadow-inner"
                    >
                    
                    <!-- Search Icon / Live Loading Spinner (Left) -->
                    <div class="absolute left-4 top-3 text-gray-400 pointer-events-none flex items-center justify-center">
                        <svg wire:loading.remove wire:target="search" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <svg wire:loading wire:target="search" class="animate-spin w-4 h-4 text-brand-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                    </div>

                    <!-- Right Controls: Keyboard shortcut / Clear button -->
                    <div class="absolute right-3.5 top-2 flex items-center gap-1.5">
                        @if(!empty($search))
                            <button 
                                type="button" 
                                wire:click="clearSearch"
                                @click="isSearchFocused = false"
                                class="w-5 h-5 rounded-full bg-gray-200 hover:bg-gray-300 text-gray-600 flex items-center justify-center text-xs font-bold transition cursor-pointer"
                                title="Clear search"
                            >
                                &times;
                            </button>
                        @else
                            <kbd class="hidden lg:inline-flex items-center px-2 py-0.5 text-[9px] font-mono font-bold text-gray-400 bg-white border border-gray-200 rounded-md shadow-xs pointer-events-none">
                                âŒ˜K
                            </kbd>
                        @endif
                    </div>
                </form>

                <!-- Intelligent Live Autocomplete Dropdown (Desktop) -->
                <div 
                    x-show="isSearchFocused"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-2 scale-98"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                    x-transition:leave-end="opacity-0 translate-y-2 scale-98"
                    class="absolute left-0 right-0 top-full mt-2.5 bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden z-[110] text-xs divide-y divide-gray-100"
                    style="display: none;"
                >
                    <!-- State 1: Empty search input -> Show Trending Keywords -->
                    @if(empty(trim($search)))
                        <div class="p-5 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="flex items-center gap-1.5 text-gray-400 font-bold text-[10px] uppercase tracking-widest">
                                    <span>ðŸ”¥</span>
                                    <span>Popular & Trending Searches</span>
                                </span>
                                <span class="text-[10px] text-brand-600 font-bold">Recommended</span>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @foreach($trendingSearches as $trending)
                                    <button 
                                        type="button"
                                        wire:click="selectTrending('{{ $trending }}')"
                                        class="px-3 py-1.5 bg-gray-50 hover:bg-brand-50 hover:text-brand-600 hover:border-brand-200 border border-gray-200/80 rounded-full text-xs font-semibold text-gray-700 transition flex items-center gap-1.5 cursor-pointer"
                                    >
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        <span>{{ $trending }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                    <!-- State 2: Searching with >= 2 characters -->
                    @elseif(strlen(trim($search)) >= 2)
                        <!-- Matched Categories Chips (if any) -->
                        @if($matchedCategories->isNotEmpty())
                            <div class="p-3.5 bg-gray-50/80 space-y-2">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block px-1">
                                    Matched Categories ({{ $matchedCategories->count() }})
                                </span>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($matchedCategories as $cat)
                                        <a 
                                            href="{{ route('category.show', $cat->slug) }}" 
                                            wire:navigate
                                            @click="isSearchFocused = false; $wire.closeSearch()"
                                            class="px-3 py-1 bg-white hover:bg-brand-50 hover:text-brand-600 border border-gray-200 rounded-lg text-xs font-semibold text-gray-800 transition flex items-center gap-1 shadow-xs"
                                        >
                                            <span>ðŸ“</span>
                                            <span>{{ $cat->hierarchy_name }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Matched Products List -->
                        @if($searchResults->isNotEmpty())
                            <div class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
                                @foreach($searchResults as $product)
                                    <a 
                                        href="{{ route('product.detail', $product->slug) }}" 
                                        wire:navigate
                                        @click="isSearchFocused = false; $wire.closeSearch()"
                                        class="p-3.5 flex items-center justify-between gap-3 hover:bg-brand-50/40 transition group"
                                    >
                                        <div class="flex items-center gap-3">
                                            <!-- Thumbnail Image (4:5 Ratio) -->
                                            <div class="w-10 h-12.5 aspect-[4/5] rounded-xl bg-gray-100 border border-gray-200/70 overflow-hidden shrink-0 flex items-center justify-center">
                                                @if(!empty($product->thumbnail_image) || !empty($product->image))
                                                    <img src="{{ asset('storage/' . ($product->thumbnail_image ?? $product->image)) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                                @else
                                                    <span class="text-gray-300 text-[10px]">No Img</span>
                                                @endif
                                            </div>

                                            <!-- Title & Category Info -->
                                            <div class="space-y-0.5">
                                                <h4 class="font-bold text-gray-900 group-hover:text-brand-600 transition line-clamp-1">
                                                    {{ $product->name }}
                                                </h4>
                                                <p class="text-[11px] text-gray-400">
                                                    {{ $product->category ? $product->category->hierarchy_name : 'Luxury Collection' }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Price & Stock Status -->
                                        <div class="text-right shrink-0">
                                            @if($product->is_on_sale)
                                                <div class="flex items-center gap-1.5 justify-end">
                                                    <span class="font-black text-rose-600 text-xs">BDT {{ number_format($product->sale_price) }}</span>
                                                    <span class="text-[10px] text-gray-400 line-through">BDT {{ number_format($product->price) }}</span>
                                                </div>
                                            @else
                                                <span class="font-black text-gray-900 text-xs">BDT {{ number_format($product->price) }}</span>
                                            @endif

                                            @if($product->stock_quantity > 0)
                                                <span class="text-[9px] text-emerald-600 font-bold bg-emerald-50 px-1.5 py-0.5 rounded block mt-0.5">In Stock</span>
                                            @else
                                                <span class="text-[9px] text-red-500 font-bold bg-red-50 px-1.5 py-0.5 rounded block mt-0.5">Out of Stock</span>
                                            @endif
                                        </div>
                                    </a>
                                @endforeach
                            </div>

                            <!-- View All Results Footer -->
                            <div class="p-3 bg-gray-50/80 text-center">
                                <button 
                                    type="button" 
                                    wire:click="performSearch"
                                    class="w-full py-2.5 bg-gray-900 hover:bg-brand-600 text-white font-bold text-xs rounded-xl shadow transition cursor-pointer"
                                >
                                    View all {{ $totalResultsCount }} results for "{{ $search }}" &rarr;
                                </button>
                            </div>
                        @else
                            <!-- No Products Found State -->
                            <div class="p-8 text-center space-y-2">
                                <span class="text-2xl">ðŸ”</span>
                                <h4 class="font-bold text-gray-900">No products matching "{{ $search }}"</h4>
                                <p class="text-gray-400 text-xs max-w-xs mx-auto">Try searching for terms like "serum", "gown", "sharara", or "face wash".</p>
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Right: Utility Actions & Commerce Controls -->
            <div class="flex items-center space-x-1.5 sm:space-x-2 md:space-x-3 shrink-0">
                
                <!-- Mobile Search Trigger Button -->
                <button 
                    @click="mobileSearchOpen = !mobileSearchOpen; $nextTick(() => { if (mobileSearchOpen) { $refs.mobileSearchInput?.focus(); $wire.openSearch(); } })"
                    type="button" 
                    class="md:hidden p-2 rounded-2xl bg-gray-50 hover:bg-brand-50 text-gray-800 hover:text-brand-600 transition border border-gray-200/70"
                    title="Search products"
                >
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>

                <!-- Track Order CTA Pill -->
                <a 
                    href="{{ route('order.track') }}" 
                    wire:navigate 
                    class="hidden sm:inline-flex items-center gap-1.5 text-xs font-bold text-gray-700 hover:text-brand-600 py-2 px-3.5 rounded-full hover:bg-brand-50/80 border border-gray-200/80 hover:border-brand-200 transition"
                >
                    <svg class="w-3.5 h-3.5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="hidden md:inline">Track Order</span>
                </a>

                <!-- User Account Dropdown / Auth Buttons -->
                @auth
                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                        <button 
                            @click="open = !open" 
                            type="button" 
                            class="flex items-center gap-1.5 py-1 px-2 sm:py-1.5 sm:px-3 rounded-full bg-gray-50 hover:bg-brand-50 border border-gray-200 hover:border-brand-200 transition text-xs font-bold text-gray-800 cursor-pointer"
                        >
                            <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-gradient-to-tr from-gray-900 to-brand-700 text-white font-bold text-[10px] sm:text-xs flex items-center justify-center shadow-xs">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <span class="hidden lg:inline-block max-w-[90px] truncate">{{ auth()->user()->name }}</span>
                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        <!-- Account Dropdown Menu -->
                        <div 
                            x-show="open" 
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                            x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                            class="absolute right-0 mt-2.5 w-56 bg-white rounded-3xl shadow-2xl border border-gray-100 py-2.5 z-[110] text-xs divide-y divide-gray-100"
                            style="display: none;"
                        >
                            <div class="px-4 py-2.5">
                                <p class="font-bold text-gray-900 truncate">{{ auth()->user()->name }}</p>
                                <p class="text-[11px] text-gray-400 truncate">{{ auth()->user()->email }}</p>
                            </div>

                            <div class="py-1">
                                <a href="{{ route('profile') }}" wire:navigate class="flex items-center gap-2.5 px-4 py-2 text-gray-700 hover:bg-brand-50 hover:text-brand-600 transition font-medium">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <span>My Profile</span>
                                </a>

                                <a href="{{ route('my-orders') }}" wire:navigate class="flex items-center gap-2.5 px-4 py-2 text-gray-700 hover:bg-brand-50 hover:text-brand-600 transition font-medium">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 11h14l1 12H4L5 11z"/></svg>
                                    <span>My Orders & History</span>
                                </a>
                            </div>

                            @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                                <div class="py-1">
                                    <a href="/admin" target="_blank" class="flex items-center gap-2.5 px-4 py-2 text-brand-600 hover:bg-brand-50 font-bold transition">
                                        <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <span>Filament Admin</span>
                                    </a>
                                </div>
                            @endif

                            <form action="{{ route('logout') }}" method="POST" class="pt-1">
                                @csrf
                                <button type="submit" class="w-full text-left flex items-center gap-2.5 px-4 py-2 text-red-600 hover:bg-red-50 font-bold transition cursor-pointer">
                                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    <span>Sign Out</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a 
                        href="{{ route('login') }}"
                        wire:navigate
                        class="flex items-center justify-center w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-gray-50 hover:bg-brand-50 border border-gray-200 hover:border-brand-200 transition text-gray-800 cursor-pointer shadow-xs shrink-0"
                        title="Sign In / Register"
                    >
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </a>
                @endauth

                <!-- Shopping Bag Action Button -->
                <a 
                    href="{{ route('cart') }}" 
                    wire:navigate 
                    class="flex relative p-2 sm:p-2.5 rounded-2xl bg-gray-950 text-white hover:bg-brand-600 transition flex items-center gap-1.5 sm:gap-2 shadow-md shadow-gray-950/10 group shrink-0"
                    title="View Shopping Bag"
                >
                    <div class="relative">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 transform group-hover:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 11h14l1 12H4L5 11z"></path>
                        </svg>
                        @if($cartCount > 0)
                            <span class="absolute -top-2.5 -right-2.5 bg-gradient-to-r from-rose-500 to-pink-500 text-white text-[9px] sm:text-[10px] font-black w-4 h-4 sm:w-5 sm:h-5 rounded-full flex items-center justify-center shadow-lg border-2 border-white animate-pulse">
                                {{ $cartCount }}
                            </span>
                        @endif
                    </div>
                    <span class="text-xs font-bold hidden lg:inline-block pr-1">Bag</span>
                </a>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- 2. DYNAMIC CATEGORY NAVIGATION RIBBON & MEGA MENUS -->
        <!-- ======================================================== -->
        <nav class="hidden lg:flex items-center justify-between border-t border-gray-100 py-2.5 text-xs font-bold text-gray-700">
            <!-- Left: Dynamic Parent Categories with Dropdowns -->
            <div class="flex items-center space-x-1">
                <!-- All Categories Dropdown Trigger -->
                <div class="relative" @mouseenter="activeCategoryDropdown = 'all'" @mouseleave="activeCategoryDropdown = null">
                    <a 
                        href="{{ route('home') }}" 
                        wire:navigate
                        class="px-3.5 py-1.5 rounded-full hover:bg-gray-100 text-gray-900 flex items-center gap-1.5 transition font-extrabold"
                    >
                        <span>âœ¨</span>
                        <span>All Collections</span>
                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </a>

                    <!-- Mega Dropdown View -->
                    <div 
                        x-show="activeCategoryDropdown === 'all'"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-1"
                        class="absolute left-0 top-full mt-1 w-[720px] bg-white rounded-3xl shadow-2xl border border-gray-100 p-6 z-[110] grid grid-cols-3 gap-6"
                        style="display: none;"
                    >
                        @foreach($navCategories as $navCat)
                            <div class="space-y-2">
                                <a 
                                    href="{{ route('category.show', $navCat->slug) }}" 
                                    wire:navigate
                                    class="font-black text-gray-900 hover:text-brand-600 transition flex items-center justify-between text-xs pb-1 border-b border-gray-100 group"
                                >
                                    <span>{{ $navCat->name }}</span>
                                    <span class="text-[10px] text-gray-400 group-hover:text-brand-600 font-normal">&rarr;</span>
                                </a>
                                @if($navCat->children->isNotEmpty())
                                    <ul class="space-y-1 text-[11px] font-medium text-gray-600">
                                        @foreach($navCat->children->take(4) as $child)
                                            <li>
                                                <a 
                                                    href="{{ route('category.show', $child->slug) }}" 
                                                    wire:navigate
                                                    class="hover:text-brand-600 hover:translate-x-0.5 transition block py-0.5"
                                                >
                                                    {{ $child->name }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Individual Parent Category Links -->
                @foreach($navCategories->take(6) as $navCat)
                    <div class="relative" @mouseenter="activeCategoryDropdown = {{ $navCat->id }}" @mouseleave="activeCategoryDropdown = null">
                        <a 
                            href="{{ route('category.show', $navCat->slug) }}" 
                            wire:navigate
                            class="px-3 py-1.5 rounded-full hover:bg-gray-100 text-gray-700 hover:text-brand-600 transition inline-flex items-center gap-1"
                        >
                            <span>{{ $navCat->name }}</span>
                            @if($navCat->children->isNotEmpty())
                                <svg class="w-2.5 h-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            @endif
                        </a>

                        <!-- Flyout Menu for Subcategories -->
                        @if($navCat->children->isNotEmpty())
                            <div 
                                x-show="activeCategoryDropdown === {{ $navCat->id }}"
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 translate-y-1"
                                class="absolute left-0 top-full mt-1 w-52 bg-white rounded-2xl shadow-xl border border-gray-100 p-2 z-[110] space-y-0.5"
                                style="display: none;"
                            >
                                <a 
                                    href="{{ route('category.show', $navCat->slug) }}" 
                                    wire:navigate
                                    class="block px-3 py-2 rounded-xl text-xs font-bold text-gray-900 hover:bg-brand-50 hover:text-brand-600 transition"
                                >
                                    All {{ $navCat->name }}
                                </a>
                                @foreach($navCat->children as $sub)
                                    <a 
                                        href="{{ route('category.show', $sub->slug) }}" 
                                        wire:navigate
                                        class="block px-3 py-1.5 rounded-xl text-xs font-medium text-gray-600 hover:bg-gray-50 hover:text-brand-600 transition flex items-center justify-between"
                                    >
                                        <span>{{ $sub->name }}</span>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Right: Special Highlights Ribbon -->
            <div class="flex items-center space-x-3 text-xs">
                <a 
                    href="/authenticity-guarantee" 
                    wire:navigate
                    class="text-emerald-700 hover:text-emerald-800 font-bold flex items-center gap-1 transition"
                >
                    <span>ðŸ›¡ï¸</span>
                    <span>100% Genuine</span>
                </a>

                
@if($hasActiveFlashSale)
<a 
                    href="{{ route('home') }}#flash-sale" 
                    class="bg-gradient-to-r from-rose-600 to-pink-600 text-white font-extrabold px-3 py-1 rounded-full shadow-xs flex items-center gap-1 hover:brightness-110 transition animate-pulse"
                >
                    <span>ðŸ”¥</span>
                    <span>Mega Deals</span>
                </a>
@endif
            </div>
        </nav>

        <!-- B) Mobile / Tablet Horizontal Category Scroll Strip (Pinned at Top) -->
        <nav class="lg:hidden border-t border-gray-100/90 py-2 w-full overflow-hidden">
            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar scroll-smooth px-1" style="scrollbar-width: none; -ms-overflow-style: none; -webkit-overflow-scrolling: touch;">
                <!-- All Link -->
                <a 
                    href="{{ route('home') }}" 
                    wire:navigate
                    class="px-3 py-1.5 rounded-full bg-gray-900 text-white font-black text-xs shrink-0 flex items-center gap-1 shadow-xs"
                >
                    <span>âœ¨</span>
                    <span>All</span>
                </a>

                <!-- Mega Deals Pill -->
                
@if($hasActiveFlashSale)
<a 
                    href="{{ route('home') }}#flash-sale" 
                    class="px-3 py-1.5 rounded-full bg-gradient-to-r from-rose-600 to-pink-600 text-white font-extrabold text-xs shrink-0 flex items-center gap-1 shadow-xs"
                >
                    <span>ðŸ”¥</span>
                    <span>Deals</span>
                </a>
@endif

                <!-- Dynamic Parent Categories -->
                @foreach($navCategories as $navCat)
                    <a 
                        href="{{ route('category.show', $navCat->slug) }}" 
                        wire:navigate
                        class="px-3 py-1.5 rounded-full bg-gray-50 hover:bg-brand-50 hover:text-brand-600 border border-gray-200/80 text-gray-800 font-bold text-xs shrink-0 transition"
                    >
                        {{ $navCat->name }}
                    </a>
                @endforeach

                <!-- 100% Genuine Guarantee -->
                <a 
                    href="/authenticity-guarantee" 
                    wire:navigate
                    class="px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 font-bold text-xs shrink-0 flex items-center gap-1"
                >
                    <span>ðŸ›¡ï¸</span>
                    <span>Genuine</span>
                </a>
            </div>
        </nav>

        <!-- ======================================================== -->
        <!-- 3. MOBILE SEARCH DRAWER (RESPONSIVE) -->
        <!-- ======================================================== -->
        <div 
            x-show="mobileSearchOpen" 
            @click.outside="mobileSearchOpen = false; $wire.closeSearch()"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="md:hidden pb-4 pt-1 border-t border-gray-100 mt-2 space-y-3"
            style="display: none;"
        >
            <!-- Mobile Search Input Box -->
            <form wire:submit.prevent="performSearch" class="relative flex items-center gap-2">
                <div class="relative flex-1">
                    <input 
                        type="text" 
                        x-ref="mobileSearchInput"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search skincare, sharara, gowns..." 
                        class="w-full bg-gray-50 focus:bg-white px-4 py-3 pl-10 pr-10 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none text-xs text-gray-900 shadow-sm"
                    >
                    <!-- Search Icon / Loading Spinner -->
                    <div class="absolute left-3.5 top-3.5 text-gray-400 pointer-events-none">
                        <svg wire:loading.remove wire:target="search" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <svg wire:loading wire:target="search" class="animate-spin w-4 h-4 text-brand-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                    </div>

                    <!-- Clear Search Button -->
                    @if(!empty($search))
                        <button 
                            type="button" 
                            wire:click="clearSearch"
                            class="absolute right-3 top-3 w-5 h-5 rounded-full bg-gray-200 text-gray-600 flex items-center justify-center text-xs font-bold"
                        >
                            &times;
                        </button>
                    @endif
                </div>

                <!-- Close Button -->
                <button 
                    type="button"
                    @click="mobileSearchOpen = false; $wire.closeSearch()"
                    class="p-2.5 text-xs font-bold text-gray-500 hover:text-gray-900 bg-gray-100 rounded-xl"
                >
                    Cancel
                </button>
            </form>

            <!-- Mobile Suggestions & Live Results Container -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-xl overflow-hidden divide-y divide-gray-100">
                <!-- State 1: Empty Search -> Show Trending Searches -->
                @if(empty(trim($search)))
                    <div class="p-4 space-y-2.5">
                        <div class="flex items-center gap-1.5 text-gray-400 font-bold text-[10px] uppercase tracking-wider">
                            <span>ðŸ”¥</span>
                            <span>Popular & Trending</span>
                        </div>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($trendingSearches as $trending)
                                <button 
                                    type="button"
                                    wire:click="selectTrending('{{ $trending }}')"
                                    @click="mobileSearchOpen = false"
                                    class="px-3 py-1.5 bg-gray-50 hover:bg-brand-50 hover:text-brand-600 border border-gray-200 rounded-full text-xs font-medium text-gray-700 transition"
                                >
                                    {{ $trending }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                <!-- State 2: Searching with >= 2 characters -->
                @elseif(strlen(trim($search)) >= 2)
                    <!-- Mobile Matched Categories Chips -->
                    @if($matchedCategories->isNotEmpty())
                        <div class="p-3 bg-gray-50/80 space-y-1.5">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block">
                                Categories
                            </span>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($matchedCategories as $cat)
                                    <a 
                                        href="{{ route('category.show', $cat->slug) }}" 
                                        wire:navigate
                                        @click="mobileSearchOpen = false; $wire.closeSearch()"
                                        class="px-2.5 py-1 bg-white hover:bg-brand-50 text-gray-800 border border-gray-200 rounded-lg text-xs font-semibold shadow-xs flex items-center gap-1"
                                    >
                                        <span>ðŸ“</span>
                                        <span>{{ $cat->name }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Mobile Matched Products List -->
                    @if($searchResults->isNotEmpty())
                        <div class="divide-y divide-gray-100 max-h-72 overflow-y-auto">
                            @foreach($searchResults as $product)
                                <a 
                                    href="{{ route('product.detail', $product->slug) }}" 
                                    wire:navigate
                                    @click="mobileSearchOpen = false; $wire.closeSearch()"
                                    class="p-3 flex items-center justify-between gap-3 hover:bg-gray-50 transition"
                                >
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="w-9 h-11.5 aspect-[4/5] rounded-lg bg-gray-100 border border-gray-200 overflow-hidden shrink-0 flex items-center justify-center">
                                            @if(!empty($product->thumbnail_image) || !empty($product->image))
                                                <img src="{{ asset('storage/' . ($product->thumbnail_image ?? $product->image)) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                            @else
                                                <span class="text-gray-300 text-[9px]">No Img</span>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <h4 class="font-bold text-gray-900 text-xs truncate">
                                                {{ $product->name }}
                                            </h4>
                                            <p class="text-[10px] text-gray-400 truncate">
                                                {{ $product->category ? $product->category->name : 'Luxury' }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="text-right shrink-0">
                                        <span class="font-black text-rose-600 text-xs block whitespace-nowrap">
                                            BDT {{ number_format($product->sale_price ?: $product->price) }}
                                        </span>
                                        @if($product->stock_quantity > 0)
                                            <span class="text-[8px] text-emerald-600 font-bold bg-emerald-50 px-1 py-0.2 rounded inline-block">In Stock</span>
                                        @else
                                            <span class="text-[8px] text-red-500 font-bold bg-red-50 px-1 py-0.2 rounded inline-block">Out</span>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>

                        <!-- View all button on mobile -->
                        <div class="p-2.5 bg-gray-50 text-center">
                            <button 
                                type="button" 
                                wire:click="performSearch"
                                @click="mobileSearchOpen = false"
                                class="w-full py-2 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow"
                            >
                                View all {{ $totalResultsCount }} results &rarr;
                            </button>
                        </div>
                    @else
                        <!-- No Match on Mobile -->
                        <div class="p-6 text-center space-y-1">
                            <span class="text-xl">ðŸ”</span>
                            <h4 class="font-bold text-gray-900 text-xs">No products found</h4>
                            <p class="text-gray-400 text-[11px]">Try "sharara", "serum", or "gown".</p>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- 4. LUXURY OFF-CANVAS MOBILE NAVIGATION DRAWER (TELEPORTED TO BODY) -->
    <!-- ======================================================== -->
    <template x-teleport="body">
        <div 
            x-show="mobileDrawerOpen"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[99999] bg-black/80 backdrop-blur-md flex"
            style="display: none;"
            @click.self="mobileDrawerOpen = false; document.body.style.overflow = ''"
        >
            <div 
                x-show="mobileDrawerOpen"
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="-translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="-translate-x-full"
                class="bg-white w-5/6 max-w-sm h-full max-h-screen shadow-2xl flex flex-col justify-between overflow-hidden relative z-10"
                @click.stop
            >
                <!-- Drawer Header -->
                <div class="p-4 sm:p-5 border-b border-gray-100 flex items-center justify-between shrink-0 bg-white">
                    <a href="{{ route('home') }}" wire:navigate @click="mobileDrawerOpen = false; document.body.style.overflow = ''" class="flex items-center gap-2">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-8 w-auto">
                        <div class="flex flex-col">
                            <span class="font-black text-gray-900 text-sm leading-none">KAZI FASHION</span>
                            <span class="text-[8px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">Luxury World</span>
                        </div>
                    </a>
                    <button 
                        type="button" 
                        @click="mobileDrawerOpen = false; document.body.style.overflow = ''"
                        class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-700 hover:text-gray-900 flex items-center justify-center font-bold text-lg cursor-pointer transition"
                        title="Close menu"
                    >
                        &times;
                    </button>
                </div>

                <!-- Drawer Body: Categories Accordion & Links -->
                <div class="p-4 sm:p-5 space-y-5 flex-1 overflow-y-auto">
                    <!-- User Account Status -->
                    @auth
                        <div class="p-3.5 rounded-2xl bg-gray-50 border border-gray-100 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-brand-600 text-white font-bold flex items-center justify-center shrink-0">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="font-bold text-gray-900 text-xs truncate">{{ auth()->user()->name }}</p>
                                <a href="{{ route('profile') }}" wire:navigate @click="mobileDrawerOpen = false; document.body.style.overflow = ''" class="text-[10px] text-brand-600 font-semibold">View Account &rarr;</a>
                            </div>
                        </div>
                    @else
                        <div class="p-3.5 rounded-2xl bg-gray-50 border border-gray-100 flex items-center justify-between gap-3">
                            <div>
                                <p class="font-bold text-gray-900 text-xs">Welcome, Guest</p>
                                <p class="text-[10px] text-gray-500">Sign in to track your orders</p>
                            </div>
                            <a href="{{ route('login') }}" wire:navigate @click="mobileDrawerOpen = false; document.body.style.overflow = ''" class="shrink-0 text-center py-2 px-4 rounded-xl bg-gray-900 text-white text-[11px] font-bold hover:bg-brand-600 shadow-sm transition">
                                Sign In
                            </a>
                        </div>
                    @endauth

                    <!-- Category Accordion List -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between px-1 mb-1">
                            <span class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">
                                Luxury Collections
                            </span>
                            <a href="{{ route('home') }}" wire:navigate @click="mobileDrawerOpen = false; document.body.style.overflow = ''" class="text-[10px] text-brand-600 font-bold">View All</a>
                        </div>

                        <!-- Mega Deals Highlight Pill inside drawer -->
                        
@if($hasActiveFlashSale)
<a 
                            href="{{ route('home') }}#flash-sale" 
                            @click="mobileDrawerOpen = false; document.body.style.overflow = ''"
                            class="flex items-center justify-between p-3 rounded-2xl bg-gradient-to-r from-rose-50 to-pink-50 border border-rose-100 text-rose-700 font-black text-xs group"
                        >
                            <span class="flex items-center gap-2">
                                <span>ðŸ”¥</span>
                                <span>Mega Deals & Flash Sales</span>
                            </span>
                            <span class="text-[10px] bg-rose-600 text-white px-2 py-0.5 rounded-full font-bold">Save Big</span>
                        </a>
@endif

                        @foreach($navCategories as $navCat)
                            <div class="rounded-2xl border border-gray-100 overflow-hidden bg-gray-50/50">
                                <div class="flex items-center justify-between p-3">
                                    <a 
                                        href="{{ route('category.show', $navCat->slug) }}" 
                                        wire:navigate
                                        @click="mobileDrawerOpen = false; document.body.style.overflow = ''"
                                        class="font-bold text-xs text-gray-900 hover:text-brand-600 transition flex-1"
                                    >
                                        {{ $navCat->name }}
                                    </a>

                                    @if($navCat->children->isNotEmpty())
                                        <button 
                                            type="button" 
                                            @click="mobileAccordion = (mobileAccordion === {{ $navCat->id }} ? null : {{ $navCat->id }})"
                                            class="p-1 text-gray-400 hover:text-gray-900 cursor-pointer"
                                        >
                                            <svg class="w-4 h-4 transform transition-transform duration-200" :class="mobileAccordion === {{ $navCat->id }} ? 'rotate-180 text-brand-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>
                                    @endif
                                </div>

                                @if($navCat->children->isNotEmpty())
                                    <div x-show="mobileAccordion === {{ $navCat->id }}" x-collapse class="px-4 pb-3 space-y-1.5 border-t border-gray-100 pt-2 bg-white">
                                        <a 
                                            href="{{ route('category.show', $navCat->slug) }}" 
                                            wire:navigate 
                                            @click="mobileDrawerOpen = false; document.body.style.overflow = ''"
                                            class="text-xs font-bold text-brand-600 block py-1"
                                        >
                                            All {{ $navCat->name }}
                                        </a>
                                        @foreach($navCat->children as $sub)
                                            <a 
                                                href="{{ route('category.show', $sub->slug) }}" 
                                                wire:navigate 
                                                @click="mobileDrawerOpen = false; document.body.style.overflow = ''"
                                                class="text-xs text-gray-600 hover:text-brand-600 flex items-center justify-between py-1"
                                            >
                                                <span>{{ $sub->name }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <!-- Brand CMS Pages Links -->
                    <div class="space-y-2 pt-2 border-t border-gray-100">
                        <span class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400 block px-1">
                            Help & Support
                        </span>
                        <a href="{{ route('order.track') }}" wire:navigate @click="mobileDrawerOpen = false; document.body.style.overflow = ''" class="flex items-center gap-2.5 px-2 py-1.5 text-xs font-bold text-gray-700 hover:text-brand-600">
                            <span>ðŸ“¦</span>
                            <span>Track Your Order</span>
                        </a>
                        <a href="/authenticity-guarantee" wire:navigate @click="mobileDrawerOpen = false; document.body.style.overflow = ''" class="flex items-center gap-2.5 px-2 py-1.5 text-xs font-bold text-gray-700 hover:text-brand-600">
                            <span>âœ¨</span>
                            <span>Authenticity Guarantee</span>
                        </a>
                        <a href="/about-us" wire:navigate @click="mobileDrawerOpen = false; document.body.style.overflow = ''" class="flex items-center gap-2.5 px-2 py-1.5 text-xs font-bold text-gray-700 hover:text-brand-600">
                            <span>ðŸ›ï¸</span>
                            <span>About Our House</span>
                        </a>
                        <a href="/contact-us" wire:navigate @click="mobileDrawerOpen = false; document.body.style.overflow = ''" class="flex items-center gap-2.5 px-2 py-1.5 text-xs font-bold text-gray-700 hover:text-brand-600">
                            <span>ðŸ’¬</span>
                            <span>Contact Customer Care</span>
                        </a>
                    </div>
                </div>

                <!-- Drawer Footer: WhatsApp Helpline Direct -->
                <div class="p-4 sm:p-5 border-t border-gray-100 bg-gray-50 space-y-2 shrink-0">
                    <a 
                        href="https://wa.me/8801735940279" 
                        target="_blank" 
                        rel="noopener noreferrer" 
                        class="w-full py-2.5 px-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl flex items-center justify-center gap-1.5 shadow-md shadow-emerald-600/20"
                    >
                        <span>ðŸ’¬</span>
                        <span>Chat on WhatsApp</span>
                    </a>
                    <p class="text-[10px] text-center text-gray-400">&copy; {{ date('Y') }} Kazi Fashion World. All rights reserved.</p>
                </div>
            </div>
        </div>
    </template>
</header>



