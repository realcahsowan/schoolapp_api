{{-- Modal untuk menampilkan banyak Journal Mutabaah dalam bentuk tabel --}}
<div>
    <h2 class="font-bold text-lg mb-2">Tanggal {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d M Y') }}</h2>
    @if($journals->isEmpty())
        <div class="text-gray-500">Tidak ada data jurnal untuk tanggal ini.</div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse border mt-2">
                <thead class="bg-gray-50">
                <tr>
                    <!--<th class="px-3 py-2 border text-left">ID</th>-->
                    <th class="px-3 py-2 border text-left">Santri</th>
                    <th class="px-3 py-2 border text-left">Waktu</th>
                    <th class="px-3 py-2 border text-left">Kehadiran</th>
                    <th class="px-3 py-2 border text-left">Status</th>
                    <!--<th class="px-3 py-2 border text-left">Catatan</th>-->
                </tr>
                </thead>
                <tbody>
                @foreach($journals as $journal)
                    <tr>
                        <!--<td class="px-3 py-1 border text-sm">{{ $journal->id }}</td>-->
                        <td class="px-3 py-1 border text-sm">{{ $journal->student?->nama ?? '-' }}</td>
                        <td class="px-3 py-1 border text-sm">{{ Str::title($journal->waktu) }}</td>
                        <td class="px-3 py-1 border text-sm">{{ Str::title($journal->kehadiran) }}</td>
                        <td class="px-3 py-1 border text-sm">
                            <span class="inline-block align-middle mr-2">
                                @if($journal->completed)
                                    <span class="text-green-600 font-bold">✓</span>
                                @else
                                    <span class="text-red-600 font-bold">✗</span>
                                @endif
                            </span>
                            {{ Str::of($journal->status)->replace('_', ' ')->title() }}
                        </td>
                        <!--<td class="px-3 py-1 border text-sm">{{ $journal->catatan ?: '-' }}</td>-->
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

