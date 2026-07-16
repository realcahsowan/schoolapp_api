<x-filament-panels::page>
    <x-filament::section>
        <div style="margin-bottom: 20px;">{{ $this->form }}</div>
        <x-filament::button wire:click="sendWhatsapp">
            Kirim
        </x-filament::button>
    </x-filament::section>
</x-filament-panels::page>
