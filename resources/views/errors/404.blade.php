<x-layouts.app title="Lost Your Way? - Kazi Fashion">
    <div class="bg-white min-h-[85vh] flex flex-col items-center justify-center pb-20 px-4 font-sans" style="padding-top: 100px;">
        
        <!-- Premium Vector Animation (Elegant Shopping Bag) -->
        <div class="mb-12 relative flex items-center justify-center">
            <!-- Animated SVG Vector -->
            <svg class="w-20 h-20 text-gray-800 animate-float relative z-10" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M16 11V7C16 4.79086 14.2091 3 12 3C9.79086 3 8 4.79086 8 7V11M5 9H19L20 21H4L5 9Z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M9.5 13.5C9.5 13.5 10.5 12 12 12C13.5 12 14.5 13 14.5 14C14.5 15.5 12 16.5 12 16.5M12 19.5V19.51" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <!-- Blush background glow -->
            <div class="absolute w-28 h-28 bg-pink-100 rounded-full opacity-70 animate-pulse-slow" style="filter: blur(20px);"></div>
        </div>

        <!-- Typography -->
        <div class="text-center max-w-2xl mx-auto space-y-4 mb-20 relative z-10">
            <div class="relative inline-block">
                <h1 class="text-4xl md:text-5xl font-serif font-bold text-gray-900 tracking-tight" style="font-size: 2.75rem;">
                    Lost Your Way?
                </h1>
                <div class="absolute -top-3 -right-6 w-24 h-12 bg-pink-50 rounded-full opacity-80 -z-10 transform -rotate-6" style="filter: blur(10px);"></div>
            </div>
            <p class="text-base md:text-lg text-gray-600 font-light mt-6">
                Perhaps we can help you find what you're looking for...
            </p>
        </div>

        <!-- Categories Display (Grid of Products) -->
        @php
            $categories = collect();
            try {
                // Fetch up to 4 main active categories to display
                $categories = \App\Models\Category::whereNull('parent_id')
                    ->where('is_active', 1)
                    ->take(4)
                    ->get();
            } catch(\Exception $e) {}
        @endphp

        @if($categories->count() > 0)
        <div class="flex flex-wrap justify-center items-end gap-12 md:gap-16 max-w-5xl mx-auto w-full px-4 relative z-10">
            @foreach($categories as $category)
            <a href="{{ route('category.show', ['slug' => $category->slug]) }}" class="group block text-center" style="width: 220px;">
                
                <!-- Category Image Container with Floating Effect -->
                <div class="relative w-full mb-6 bg-transparent flex items-end justify-center transform transition-transform duration-500 ease-out hover:-translate-y-3" style="aspect-ratio: 4/5;">
                    @if($category->image)
                        <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="w-full h-full object-contain transition-transform duration-700 hover:scale-105" style="filter: drop-shadow(0 20px 13px rgba(0, 0, 0, 0.15)); max-height: 100%;">
                        
                        <!-- Soft Floor Shadow under the image -->
                        <div class="absolute -bottom-4 left-1/2 -translate-x-1/2 w-4/5 h-4 bg-black opacity-10 rounded-full" style="filter: blur(6px);"></div>
                    @else
                        <!-- Fallback Gray Box -->
                        <div class="w-full h-full flex items-center justify-center bg-gray-50 rounded-2xl shadow-sm border border-gray-100">
                            <span class="text-gray-300 font-serif italic">No Image</span>
                        </div>
                    @endif
                </div>
                
                <h3 class="text-sm font-bold text-gray-800 uppercase transition-colors hover:text-brand-600" style="letter-spacing: 0.15em;">
                    {{ $category->name }}
                </h3>
            </a>
            @endforeach
        </div>
        @else
        <!-- Fallback Button if no categories found in DB -->
        <a href="/" class="px-8 py-3 bg-gray-900 text-white text-sm font-bold uppercase tracking-widest rounded-full shadow-lg hover:bg-gray-800 transition transform hover:-translate-y-1">Return to Home</a>
        @endif

        <!-- Inline Custom CSS Animations -->
        <style>
            @keyframes float {
                0%, 100% { transform: translateY(0px); }
                50% { transform: translateY(-12px); }
            }
            .animate-float {
                animation: float 4s ease-in-out infinite;
            }
            @keyframes pulse-slow {
                0%, 100% { opacity: 0.6; transform: scale(1); }
                50% { opacity: 0.8; transform: scale(1.1); }
            }
            .animate-pulse-slow {
                animation: pulse-slow 5s ease-in-out infinite;
            }
        </style>
    </div>
</x-layouts.app>