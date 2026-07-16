<div>
    <h2 class="mb-3 text-lg font-semibold">
        Tanggal {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d M Y') }}
    </h2>

    @if ($journals->isEmpty())
        <div class="text-gray-500">Tidak ada jurnal untuk hari ini.</div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse border">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="border px-3 py-2 text-left">Santri</th>
                        <th class="border px-3 py-2 text-left">Waktu</th>
                        <th class="border px-3 py-2 text-left">Kehadiran</th>
                        <th class="border px-3 py-2 text-left">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($journals as $journal)
                        <tr>
                            <td class="border px-3 py-2 text-sm">{{ $journal->student?->nama ?? '-' }}</td>
                            <td class="border px-3 py-2 text-sm">{{ \Illuminate\Support\Str::title($journal->waktu ?? '-') }}</td>
                            <td class="border px-3 py-2 text-sm">{{ \Illuminate\Support\Str::title($journal->kehadiran ?? '-') }}</td>
                            <td class="border px-3 py-2 text-sm">{{ \Illuminate\Support\Str::of($journal->status ?? '-')->replace('_', ' ')->title() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
