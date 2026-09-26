<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Live Theme Preview Alert Header -->
        <div class="p-5 rounded-2xl bg-gradient-to-r from-gray-900 via-brand-950 to-gray-900 text-white shadow-xl border border-gray-800 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-brand-500/20 text-brand-400 font-bold text-xs">✨ Theme System 2.0</span>
                    <h3 class="font-extrabold text-base text-white">Live Storefront Theme & Branding Control</h3>
                </div>
                <p class="text-xs text-gray-300">Changes made here are compiled and applied in real-time to all storefront pages, navigation bars, cards, and modal dialogs.</p>
            </div>
            <a href="/" target="_blank" class="shrink-0 inline-flex items-center gap-1.5 px-4 py-2 bg-white/10 hover:bg-white/20 text-white font-bold text-xs rounded-xl border border-white/20 transition">
                <span>Preview Storefront</span>
                <span>&rarr;</span>
            </a>
        </div>

        <form wire:submit="submit" class="space-y-6">
            {{ $this->form }}

            <div class="flex items-center justify-between gap-3 pt-4 border-t border-gray-200 dark:border-gray-800">
                <x-filament::button type="submit" size="lg" color="primary" icon="heroicon-m-check">
                    Save & Publish Theme Customizations
                </x-filament::button>

                <p class="text-xs text-gray-500">Auto-clears CSS cache and updates storefront instantly.</p>
            </div>
        </form>
    </div>
</x-filament-panels::page>
