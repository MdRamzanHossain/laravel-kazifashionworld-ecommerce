<x-filament-panels::page>
    <div class="space-y-6">
        <!-- SMS Gateway Info Banner -->
        <div class="p-5 rounded-2xl bg-gradient-to-r from-gray-900 via-indigo-950 to-gray-900 text-white shadow-xl border border-gray-800 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-indigo-500/20 text-indigo-400 font-bold text-xs">📱 SMS Gateway 2.0</span>
                    <h3 class="font-extrabold text-base text-white">Automated SMS & Notifications Engine</h3>
                </div>
                <p class="text-xs text-gray-300">Supported Providers: Greenweb BD, BulkSMSBD, MiM SMS, ElitBuzz, Twilio, and Any Custom HTTP API.</p>
            </div>
            <a href="{{ route('filament.admin.resources.sms-logs.index') }}" class="shrink-0 inline-flex items-center gap-1.5 px-4 py-2 bg-white/10 hover:bg-white/20 text-white font-bold text-xs rounded-xl border border-white/20 transition">
                <span>View Live SMS Logs</span>
                <span>&rarr;</span>
            </a>
        </div>

        <!-- Live Instant Test SMS Tool -->
        <div class="p-6 rounded-2xl bg-white dark:bg-gray-900 shadow-sm border border-gray-200 dark:border-gray-800 space-y-4">
            <div class="flex items-center justify-between">
                <div class="space-y-0.5">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span>⚡ Send Instant Live Test SMS</span>
                    </h3>
                    <p class="text-xs text-gray-500">Verify your active gateway API credentials by sending a real-time message.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Recipient Mobile Number</label>
                    <input 
                        type="text" 
                        wire:model="testRecipient" 
                        placeholder="e.g. 01735940279" 
                        class="w-full px-3.5 py-2 text-xs rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none"
                    >
                </div>

                <div class="md:col-span-2 flex flex-col sm:flex-row items-stretch sm:items-end gap-3">
                    <div class="flex-1">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Test Message Body</label>
                        <input 
                            type="text" 
                            wire:model="testMessage" 
                            placeholder="Type test message..." 
                            class="w-full px-3.5 py-2 text-xs rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none"
                        >
                    </div>

                    <button 
                        type="button" 
                        wire:click="sendTestSms" 
                        wire:loading.attr="disabled"
                        class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center justify-center gap-2 shrink-0 disabled:opacity-50"
                    >
                        <span wire:loading.remove wire:target="sendTestSms">Send Live SMS &rarr;</span>
                        <span wire:loading wire:target="sendTestSms">Sending...</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Settings Form -->
        <form wire:submit="submit" class="space-y-6">
            {{ $this->form }}

            <div class="flex items-center justify-between gap-3 pt-4 border-t border-gray-200 dark:border-gray-800">
                <x-filament::button type="submit" size="lg" color="primary" icon="heroicon-m-check">
                    Save SMS Gateway & Trigger Rules
                </x-filament::button>

                <p class="text-xs text-gray-500">Automated SMS will immediately use these templates upon order updates.</p>
            </div>
        </form>
    </div>
</x-filament-panels::page>