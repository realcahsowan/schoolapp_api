<x-filament-panels::page>
    @if ($batch)
        <livewire:batch-promotion-progress
            :batchId="$batch->id"
            :total="$totalCount"
            :key="$batch->id"
        />
    @endif

    {{ $this->table }}
</x-filament-panels::page>
