<x-filament-panels::page>
    <form wire:submit.prevent="downloadBackup" class="space-y-6">
        
        {{ $this->form }}

        <div class="flex justify-start">
            <x-filament::button type="submit" size="lg" icon="heroicon-m-arrow-down-tray">
                Mulai Proses & Unduh ZIP
            </x-filament::button>
        </div>
        
    </form>
</x-filament-panels::page>