<div>
    @if(!empty($page->custom_css))
        <style>
            {!! $page->custom_css !!}
        </style>
    @endif

    @if(!empty($page->is_full_width))
        {{-- Full-Width Custom Canvas for Bespoke Product Grids and Design --}}
        <div class="w-full space-y-6">
            @if(empty($page->hide_header_hero))
                <div class="relative rounded-3xl overflow-hidden bg-gradient-to-br from-gray-950 via-[#150712] to-gray-900 text-white p-8 sm:p-12 border border-gray-800/80 shadow-2xl">
                    <div class="absolute -top-24 -left-24 w-80 h-80 bg-brand-600/25 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="absolute -bottom-24 -right-24 w-80 h-80 bg-pink-500/20 rounded-full blur-3xl pointer-events-none"></div>

                    <div class="relative z-10 max-w-3xl space-y-4">
                        <nav class="flex items-center gap-2 text-xs text-gray-400 font-medium">
                            <a href="{{ route('home') }}" wire:navigate class="hover:text-brand-300 transition">Home</a>
                            <span>&rsaquo;</span>
                            <span class="text-white truncate">{{ $page->title }}</span>
                        </nav>

                        @if($page->hero_badge)
                            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/15 text-xs font-semibold text-brand-300 shadow-inner">
                                <span class="w-2 h-2 rounded-full bg-brand-400 animate-ping"></span>
                                <span>{{ $page->hero_badge }}</span>
                            </div>
                        @endif

                        <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-white tracking-tight leading-tight">
                            {{ $page->title }}
                        </h1>

                        @if($page->subtitle)
                            <p class="text-sm sm:text-base text-gray-300 font-normal leading-relaxed">
                                {{ $page->subtitle }}
                            </p>
                        @endif
                    </div>
                </div>
            @endif

            <!-- 100% Full-Width Custom Design & Product Grid Container -->
            <div class="w-full custom-page-canvas">
                {!! $page->content !!}
            </div>
        </div>
    @else
        {{-- Standard Editorial Layout (With Hero Header & Sidebar) --}}
        <div class="space-y-8">
            <!-- 1. Luxury Editorial Page Hero Header -->
            <div class="relative rounded-3xl overflow-hidden bg-gradient-to-br from-gray-950 via-[#150712] to-gray-900 text-white p-8 sm:p-12 md:p-16 border border-gray-800/80 shadow-2xl">
                <!-- Ambient Glow Orbs -->
                <div class="absolute -top-24 -left-24 w-80 h-80 bg-brand-600/25 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-24 -right-24 w-80 h-80 bg-pink-500/20 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10 max-w-3xl space-y-4">
                    <!-- Breadcrumbs -->
                    <nav class="flex items-center gap-2 text-xs text-gray-400 font-medium">
                        <a href="{{ route('home') }}" wire:navigate class="hover:text-brand-300 transition">Home</a>
                        <span>&rsaquo;</span>
                        <span class="text-brand-400">Customer Support & Information</span>
                        <span>&rsaquo;</span>
                        <span class="text-white truncate">{{ $page->title }}</span>
                    </nav>

                    <!-- Glowing Badge -->
                    @if($page->hero_badge)
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/15 text-xs font-semibold text-brand-300 shadow-inner">
                            <span class="w-2 h-2 rounded-full bg-brand-400 animate-ping"></span>
                            <span>{{ $page->hero_badge }}</span>
                        </div>
                    @endif

                    <!-- Main Heading -->
                    <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-white tracking-tight leading-tight">
                        {{ $page->title }}
                    </h1>

                    <!-- Subtitle -->
                    @if($page->subtitle)
                        <p class="text-sm sm:text-base text-gray-300 font-normal leading-relaxed">
                            {{ $page->subtitle }}
                        </p>
                    @endif
                </div>
            </div>

            <!-- 2. Main Grid Layout (8 Cols Content + 4 Cols Sidebar) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <!-- Left: Main Page Body Card (8 Cols) -->
                <div class="lg:col-span-8 bg-white rounded-3xl p-6 sm:p-10 md:p-12 border border-gray-100 shadow-sm space-y-6">
                    <div class="prose prose-sm sm:prose-base max-w-none text-gray-700 leading-relaxed prose-headings:text-gray-900 prose-headings:font-extrabold prose-a:text-brand-600 hover:prose-a:text-brand-700 prose-strong:text-gray-900 prose-ul:list-disc prose-li:my-1">
                        {!! $page->content !!}
                    </div>

                    <!-- Page Footer Trust Bar -->
                    <div class="pt-8 border-t border-gray-100 flex flex-wrap items-center justify-between gap-4 text-xs text-gray-500">
                        <span class="flex items-center gap-1.5 font-medium">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            Official Policy of Kazi Fashion World &bull; Updated {{ $page->updated_at->format('M Y') }}
                        </span>
                        <a href="{{ route('home') }}" wire:navigate class="text-brand-600 hover:text-brand-700 font-bold flex items-center gap-1">
                            <span>Explore Products</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>

                <!-- Right: Luxury Support & Page Directory Sidebar (4 Cols) -->
                <div class="lg:col-span-4 space-y-6 lg:sticky lg:top-28">
                    
                    <!-- VIP WhatsApp & Help Concierge Card -->
                    <div class="rounded-3xl bg-gradient-to-br from-emerald-950 via-gray-950 to-emerald-900 text-white p-6 sm:p-7 border border-emerald-800/40 shadow-xl space-y-4 relative overflow-hidden">
                        <div class="absolute -right-10 -bottom-10 w-36 h-36 bg-emerald-500/20 rounded-full blur-2xl pointer-events-none"></div>

                        <div class="relative z-10 space-y-2">
                            <span class="inline-block text-[10px] font-extrabold uppercase tracking-widest text-emerald-400 bg-emerald-500/20 border border-emerald-500/30 px-2.5 py-0.5 rounded-full">
                                24/7 VIP Concierge
                            </span>
                            <h3 class="text-lg font-extrabold text-white">Need Personalized Assistance?</h3>
                            <p class="text-xs text-gray-300 leading-relaxed">
                                Our dedicated beauty advisors and apparel stylists are on standby to answer all your inquiries.
                            </p>
                        </div>

                        <div class="relative z-10 space-y-2.5 pt-2">
                            <a 
                                href="https://wa.me/8801735940279?text={{ urlencode('Hello Kazi Fashion World, I need assistance regarding: ' . $page->title) }}" 
                                target="_blank" 
                                rel="noopener noreferrer" 
                                class="w-full py-3 px-4 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold rounded-xl shadow-lg transition flex items-center justify-center gap-2"
                            >
                                <span>💬</span>
                                <span>Chat Directly on WhatsApp</span>
                            </a>

                            <a 
                                href="{{ route('order.track') }}" 
                                wire:navigate 
                                class="w-full py-3 px-4 bg-white/10 hover:bg-white/15 text-white text-xs font-semibold rounded-xl border border-white/15 backdrop-blur-md transition flex items-center justify-center gap-2"
                            >
                                <span>📦</span>
                                <span>Track Live Order Delivery</span>
                            </a>
                        </div>
                    </div>

                    <!-- Page Directory Navigation Links -->
                    @if($otherPages->isNotEmpty())
                        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-4">
                            <h4 class="text-xs font-extrabold uppercase tracking-wider text-gray-900 pb-2 border-b border-gray-100">
                                Help & Support Directory
                            </h4>

                            <div class="space-y-1 text-xs">
                                @foreach($otherPages as $other)
                                    <a 
                                        href="{{ route('page.show', $other->slug) }}" 
                                        wire:navigate
                                        class="flex items-center justify-between p-2.5 rounded-xl hover:bg-gray-50 text-gray-700 hover:text-brand-600 font-medium transition group"
                                    >
                                        <span class="truncate">{{ $other->title }}</span>
                                        <span class="text-gray-400 group-hover:text-brand-600 transition">&rarr;</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- 3 Trust Highlights -->
                    <div class="bg-gradient-to-br from-brand-50/50 via-white to-pink-50/50 rounded-3xl p-6 border border-brand-100/60 shadow-sm space-y-3 text-xs text-gray-600">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-sm shrink-0">✓</span>
                            <div>
                                <strong class="text-gray-900 block">100% Authentic Guarantee</strong>
                                <span class="text-[11px] text-gray-500">Directly sourced verified items.</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-sm shrink-0">🚚</span>
                            <div>
                                <strong class="text-gray-900 block">Express Delivery</strong>
                                <span class="text-[11px] text-gray-500">24-48h Dhaka & Nationwide.</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-sm shrink-0">🛡️</span>
                            <div>
                                <strong class="text-gray-900 block">7-Day Easy Return</strong>
                                <span class="text-[11px] text-gray-500">Instant exchange support.</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    @endif

    @if(!empty($page->custom_js))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                {!! $page->custom_js !!}
            });
        </script>
    @endif
</div>
