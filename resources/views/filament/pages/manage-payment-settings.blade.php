<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Security Notice Banner -->
        <div class="p-5 rounded-2xl bg-gradient-to-r from-gray-900 via-gray-950 to-gray-900 text-white shadow-xl border border-gray-800 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-emerald-500/20 text-emerald-400 font-bold text-xs">🔒 256-Bit SSL Encrypted</span>
                    <h3 class="font-extrabold text-base text-white">Merchant Payment Gateway Setup</h3>
                </div>
                <p class="text-xs text-gray-300">Credentials stored here are securely encrypted and used directly during customer checkout and instant webhook validation.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-pink-500/20 text-pink-300 text-xs font-bold border border-pink-500/30">bKash</span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-orange-500/20 text-orange-300 text-xs font-bold border border-orange-500/30">Nagad</span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-500/20 text-blue-300 text-xs font-bold border border-blue-500/30">SSLCommerz</span>
            </div>
        </div>

        <form wire:submit="submit" class="space-y-6">
            {{ $this->form }}

            <div class="flex items-center justify-between gap-3 pt-4 border-t border-gray-200 dark:border-gray-800">
                <x-filament::button type="submit" size="lg" color="primary" icon="heroicon-m-check">
                    Save & Update Payment Gateways
                </x-filament::button>

                <p class="text-xs text-gray-500">Changes apply immediately to checkout.</p>
            </div>
        </form>
    </div>
</x-filament-panels::page>