<x-filament-panels::page>
    <form wire:submit="submit" class="space-y-6">
        {{ $this->form }}

        <div class="flex items-center gap-3 pt-4">
            <x-filament::button type="submit" size="lg" color="primary" icon="heroicon-m-check">
                Save & Publish Hero Banner
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
