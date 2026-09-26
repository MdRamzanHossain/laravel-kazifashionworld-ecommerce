<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-between pb-4">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Quick Access</h2>
            <span class="text-xs text-gray-500">Fast Navigation</span>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
            
            <!-- Products -->
            <a href="{{ url('/admin/products') }}" class="flex flex-col items-center justify-center p-4 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 hover:bg-primary-50 dark:hover:bg-primary-500/10 hover:border-primary-200 dark:hover:border-primary-500/30 transition shadow-sm group">
                <div class="w-12 h-12 bg-primary-100 dark:bg-primary-500/20 text-primary-600 dark:text-primary-400 rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                    <x-heroicon-o-shopping-bag class="w-6 h-6" />
                </div>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Products</span>
            </a>

            <!-- Orders -->
            <a href="{{ url('/admin/orders') }}" class="flex flex-col items-center justify-center p-4 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 hover:border-emerald-200 dark:hover:border-emerald-500/30 transition shadow-sm group">
                <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                    <x-heroicon-o-shopping-cart class="w-6 h-6" />
                </div>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Orders</span>
            </a>

            <!-- Customers -->
            <a href="{{ url('/admin/users') }}" class="flex flex-col items-center justify-center p-4 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 hover:bg-blue-50 dark:hover:bg-blue-500/10 hover:border-blue-200 dark:hover:border-blue-500/30 transition shadow-sm group">
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                    <x-heroicon-o-users class="w-6 h-6" />
                </div>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Customers</span>
            </a>

            <!-- Categories -->
            <a href="{{ url('/admin/categories') }}" class="flex flex-col items-center justify-center p-4 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 hover:bg-purple-50 dark:hover:bg-purple-500/10 hover:border-purple-200 dark:hover:border-purple-500/30 transition shadow-sm group">
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-500/20 text-purple-600 dark:text-purple-400 rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                    <x-heroicon-o-tag class="w-6 h-6" />
                </div>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Categories</span>
            </a>

            <!-- Store Settings -->
            <a href="{{ url('/admin/manage-store-settings') }}" class="flex flex-col items-center justify-center p-4 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 hover:bg-orange-50 dark:hover:bg-orange-500/10 hover:border-orange-200 dark:hover:border-orange-500/30 transition shadow-sm group">
                <div class="w-12 h-12 bg-orange-100 dark:bg-orange-500/20 text-orange-600 dark:text-orange-400 rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                    <x-heroicon-o-cog-6-tooth class="w-6 h-6" />
                </div>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Settings</span>
            </a>

        </div>
    </x-filament::section>
</x-filament-widgets::widget>
