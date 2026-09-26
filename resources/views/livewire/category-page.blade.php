<div 
    class="space-y-8"
    x-data="{


        
        
        
        
        
    }"
    @keydown.escape.window="closeQuickView()"
>
    <!-- Success Feedback Flash Toast -->
    @if (session()->has('cart_success'))
        <div 
            x-data="{ show: true }" 
            x-show="show" 
            x-init="setTimeout(() => show = false, 4000)"
            class="fixed top-20 right-4 z-50 rounded-2xl bg-emerald-600 text-white px-5 py-3 shadow-2xl flex items-center gap-3 text-xs font-bold transition transform"
        >
            <span>🛍️</span>
            <span>{{ session('cart_success') }}</span>
        </div>
    @endif

    <!-- 1. Luxury Editorial Category Hero Banner -->
    <div class="relative rounded-3xl overflow-hidden bg-gradient-to-br from-gray-950 via-[#160713] to-gray-900 text-white p-6 sm:p-10 md:p-14 border border-gray-800/80 shadow-2xl">
        <!-- Ambient Luxury Glow Orbs -->
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-brand-600/25 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-pink-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 max-w-3xl space-y-4">
            <!-- Breadcrumbs -->
            <nav class="flex items-center gap-2 text-xs text-gray-400 font-medium">
                <a href="{{ route('home') }}" wire:navigate class="hover:text-brand-300 transition">Home</a>
                <span>&rsaquo;</span>
                <span class="text-brand-400">Categories</span>
                @if($category->isSubcategory() && $category->parent)
                    <span>&rsaquo;</span>
                    <a href="{{ route('category.show', $category->parent->slug) }}" wire:navigate class="hover:text-brand-300 transition">{{ $category->parent->name }}</a>
                @endif
                <span>&rsaquo;</span>
                <span class="text-white truncate">{{ $category->name }}</span>
            </nav>

            <!-- Glowing Badge & Item Count -->
            <div class="flex flex-wrap items-center gap-2.5">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/15 text-xs font-semibold text-brand-300 shadow-inner">
                    <span class="w-2 h-2 rounded-full bg-brand-400 animate-ping"></span>
                    <span>{{ $category->hero_badge ?: '✨ 100% Authentic Luxury Collection' }}</span>
                </div>

                <span class="px-3 py-1 rounded-full bg-pink-500/20 text-pink-300 border border-pink-500/30 text-xs font-bold">
                    {{ $totalCategoryProductsCount }} Products Available
                </span>
            </div>

            <!-- Main Luxury Heading -->
            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold text-white tracking-tight leading-tight">
                {{ $category->name }}
            </h1>

            <!-- Subtitle / Editorial Description -->
            <p class="text-xs sm:text-sm md:text-base text-gray-300 font-normal leading-relaxed max-w-2xl">
                {{ $category->description ?: "Discover our premier selection of authentic international skincare, cosmetics, and designer handcrafted couture apparel in {$category->name}." }}
            </p>
        </div>
    </div>

    <!-- 2. Subcategories Graphical Showcase Cards (If Applicable) -->
    @if($subcategories->isNotEmpty())
        <section class="space-y-4 pt-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-lg">✨</span>
                    <h2 class="text-base sm:text-lg font-extrabold text-gray-900 tracking-tight">
                        Explore {{ $parentCategory->name }} Subcategories
                    </h2>
                </div>
                <span class="text-xs text-gray-500 font-medium">Click to filter collection</span>
            </div>

            <!-- Subcategory Cards Grid / Carousel -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 sm:gap-4">
                <!-- 'All Items' Filter Card -->
                <button 
                    type="button"
                    wire:click="selectSubcategory('all')"
                    class="p-3 sm:p-4 rounded-2xl border transition-all text-left flex flex-col justify-between group cursor-pointer {{ $selectedSubcategory === 'all' && $category->isParent() ? 'bg-gray-900 text-white border-gray-900 shadow-md ring-2 ring-brand-500/50' : 'bg-white hover:bg-gray-50 text-gray-800 border-gray-200' }}"
                >
                    <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center font-black text-sm mb-3 group-hover:scale-105 transition">
                        🛍️
                    </div>
                    <div>
                        <h4 class="font-bold text-xs truncate leading-snug">All {{ $parentCategory->name }}</h4>
                        <span class="text-[10px] {{ $selectedSubcategory === 'all' && $category->isParent() ? 'text-gray-300' : 'text-gray-400' }} block mt-0.5">
                            {{ $totalCategoryProductsCount }} Items
                        </span>
                    </div>
                </button>

                <!-- Subcategory Cards -->
                @foreach($subcategories as $sub)
                    <button 
                        type="button"
                        wire:click="selectSubcategory({{ $sub->id }})"
                        class="p-3 sm:p-4 rounded-2xl border transition-all text-left flex flex-col justify-between group cursor-pointer {{ (string)$selectedSubcategory === (string)$sub->id || $category->id === $sub->id ? 'bg-gradient-to-br from-brand-900 to-pink-900 text-white border-brand-700 shadow-lg ring-2 ring-brand-400/50' : 'bg-white hover:bg-gray-50 text-gray-800 border-gray-200' }}"
                    >
                        <div class="w-10 h-10 rounded-xl overflow-hidden bg-brand-50 flex items-center justify-center mb-3 group-hover:scale-105 transition border border-gray-100 shrink-0">
                            @if($sub->image)
                                <img src="{{ asset('storage/' . $sub->image) }}" alt="{{ $sub->name }}" class="w-full h-full object-cover">
                            @else
                                <span class="text-base text-brand-600 font-bold">🌸</span>
                            @endif
                        </div>
                        <div>
                            <h4 class="font-bold text-xs truncate leading-snug">{{ $sub->name }}</h4>
                            <span class="text-[10px] {{ (string)$selectedSubcategory === (string)$sub->id || $category->id === $sub->id ? 'text-pink-200' : 'text-gray-400' }} block mt-0.5">
                                {{ $sub->products_count }} Items
                            </span>
                        </div>
                    </button>
                @endforeach
            </div>
        </section>
    @endif

    <!-- 3. Interactive Filter & Sorting Control Bar -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-gray-100 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Left: Search & Count -->
        <div class="flex flex-wrap items-center gap-3">
            <span class="text-xs font-extrabold text-gray-900">
                Showing <span class="text-brand-600">{{ $products->total() }}</span> Products
            </span>

            <!-- Filter Search Bar -->
            <div class="relative min-w-[220px]">
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Search in {{ $category->name }}..." 
                    class="w-full pl-8 pr-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-brand-500 outline-none transition"
                >
                <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>

        <!-- Right: In-Stock Toggle & Sorting Selector -->
        <div class="flex flex-wrap items-center gap-3 text-xs">
            <!-- In-Stock Filter -->
            <label class="inline-flex items-center gap-2 cursor-pointer bg-gray-50 hover:bg-gray-100 px-3 py-2 rounded-xl border border-gray-200 transition select-none">
                <input type="checkbox" wire:model.live="inStockOnly" class="rounded text-brand-600 focus:ring-brand-500 w-3.5 h-3.5">
                <span class="font-semibold text-gray-700">In-Stock Only</span>
            </label>

            <!-- Sorting Dropdown -->
            <div class="flex items-center gap-2">
                <span class="text-gray-500 font-medium">Sort By:</span>
                <select wire:model.live="sortBy" class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-800 focus:bg-white focus:ring-2 focus:ring-brand-500 outline-none transition">
                    <option value="latest">Latest Arrivals</option>
                    <option value="price_asc">Price: Low to High</option>
                    <option value="price_desc">Price: High to Low</option>
                    <option value="sale">Deals & Discounts</option>
                </select>
            </div>

            @if($selectedSubcategory !== 'all' || !empty($search) || $inStockOnly || $sortBy !== 'latest')
                <button 
                    type="button" 
                    wire:click="clearFilters"
                    class="text-brand-600 hover:text-brand-700 font-bold underline transition"
                >
                    Reset
                </button>
            @endif
        </div>
    </div>

    <!-- 4. Luxury Product Cards Grid -->
    @if($products->isNotEmpty())
        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
            @foreach($products as $product)
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
                        'category_name'     => $product->category ? $product->category->name : $category->name,
                        'brand_name'        => $product->brand ? $product->brand->name : null,
                        'short_description' => $product->short_description ?: '100% genuine verified formulation and couture apparel tailored for authentic luxury.',
                        'stock_quantity'    => $product->stock_quantity,
                          'average_rating'    => $product->average_rating,
                          'reviews_count'     => $product->approved_reviews_count,
                        'has_variations'    => $product->has_variations,
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
                    class="bg-white rounded-3xl p-3.5 sm:p-4 border border-gray-100 shadow-sm hover:shadow-2xl hover:border-brand-200 transition-all duration-500 flex flex-col justify-between group cursor-pointer relative transform hover:-translate-y-1"
                >
                    <div>
                        <!-- Thumbnail Image & Badges (4:5 Ratio) -->
                        <div class="relative mb-3.5 overflow-hidden rounded-2xl bg-gradient-to-b from-gray-50 to-pink-50/20 aspect-[4/5] flex items-center justify-center">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-108 transition-transform duration-700 ease-out">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-300 text-xs font-medium">No Image</div>
                            @endif

                            <!-- Floating Badges -->
                            <div class="absolute top-2.5 left-2.5 flex flex-col gap-1 z-10 pointer-events-none">
                                @if($flashDeal)
                                    <span class="bg-rose-600 text-white text-[9px] sm:text-[10px] font-black px-2.5 py-0.5 rounded-lg shadow-md">
                                        🔥 Flash Deal
                                    </span>
                                @elseif($hasDiscount)
                                    <span class="bg-gradient-to-r from-rose-600 to-pink-600 text-white text-[9px] sm:text-[10px] font-black px-2.5 py-0.5 rounded-lg shadow-md">
                                        -{{ $discountPct }}% OFF
                                    </span>
                                @endif

                                @if($product->is_featured)
                                    <span class="bg-gray-900/90 backdrop-blur-md text-white text-[8px] sm:text-[9px] font-bold px-2 py-0.5 rounded-md shadow-sm border border-white/10">
                                        ✨ Luxury
                                    </span>
                                @endif
                            </div>

                            <!-- Hover Quick View Pill Overlay -->
                            <div class="absolute inset-x-3 bottom-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none hidden sm:block">
                                <span class="w-full block text-center py-2 bg-gray-950/80 backdrop-blur-md text-white text-[10px] font-extrabold uppercase tracking-wider rounded-xl shadow-lg border border-white/10">
                                    Quick View Details
                                </span>
                            </div>
                        </div>

                        <!-- Category Name & Brand -->
                        <div class="mb-1 flex items-center justify-between gap-1 text-[10px] font-extrabold uppercase tracking-[0.16em] text-brand-600">
                            <span class="truncate">{{ $product->category ? $product->category->name : $category->name }}</span>
                            @if($product->brand)
                                <span class="text-gray-400 truncate">{{ $product->brand->name }}</span>
                            @endif
                        </div>

                        <!-- Product Name -->
                        <h3 class="font-sans font-bold text-xs sm:text-sm text-gray-900 group-hover:text-brand-600 transition line-clamp-2 leading-snug tracking-tight">
                            {{ $product->name }}
                        </h3>

                        <!-- Rating Stars & Stock Indicator -->
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

                    <!-- Price & Sleek Luxury Details Link -->
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

                        <!-- Action Affordance -->
                        <div class="flex items-center justify-between text-xs font-bold text-gray-800 group-hover:text-brand-600 transition pt-1">
                            <span class="text-[11px]">Explore Details</span>
                            <span>&rarr;</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="pt-6">
            {{ $products->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="bg-white rounded-3xl p-12 sm:p-16 text-center border border-gray-100 shadow-sm max-w-lg mx-auto space-y-4">
            <div class="w-16 h-16 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center mx-auto text-2xl shadow-sm">
                🌸
            </div>
            <h3 class="text-lg font-bold text-gray-900">No products found</h3>
            <p class="text-xs text-gray-500 max-w-xs mx-auto">
                No items currently match your selected filters in <strong>{{ $category->name }}</strong>.
            </p>
            <div class="pt-2">
                <button 
                    type="button" 
                    wire:click="clearFilters"
                    class="px-6 py-3 bg-gradient-to-r from-primary to-accent hover:from-primary-hover hover:to-primary text-white text-xs font-bold rounded-xl transition shadow-md"
                >
                    Reset Filters & View All
                </button>
            </div>
        </div>
    @endif

    <!-- ======================================================== -->
    <x-quick-view-modal />
</div>






