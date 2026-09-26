<x-filament-panels::page>
    <form wire:submit="submit" class="space-y-6">
        {{ $this->form }}

        <div class="flex items-center gap-3">
            <x-filament::button type="submit" size="lg">
                Save Analytics Settings
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>