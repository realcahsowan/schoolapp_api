<div x-data="{ copied: false }" class="space-y-3">
    <div class="flex items-center justify-between gap-3">
        <div class="text-sm text-gray-500">
            Teks peringatan untuk tanggal {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d M Y') }}
        </div>

        <button
            type="button"
            class="inline-flex items-center rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-gray-700"
            @click="navigator.clipboard.writeText($refs.message.value).then(() => { copied = true; setTimeout(() => copied = false, 1500) })"
        >
            <span x-text="copied ? 'Tersalin' : 'Salin'"></span>
        </button>
    </div>

    <textarea
        x-ref="message"
        readonly
        rows="20"
        class="w-full rounded-lg border border-gray-300 bg-gray-50 p-3 font-mono text-sm leading-5 text-gray-900 focus:outline-none focus:ring-0"
    >{{ $message }}</textarea>
</div>
