@props(['journals'])
<div>
    <h2 class="font-bold text-lg mb-2">Tanggal {{ $tanggal }}</h2>

    @php
        $journalsPagi = $journals->where('waktu', 'pagi');
        $journalsSore = $journals->where('waktu', 'sore');
    @endphp

    {{-- Tabel Jurnal Pagi --}}
    <h3 class="font-semibold text-md mt-4 mb-2">Waktu Pagi</h3>
    @if($journalsPagi->isEmpty())
        <div class="text-gray-500 mb-4">Tidak ada data jurnal pagi.</div>
    @else
        <table class="table-auto border w-full mb-6">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-2 py-1">Santri</th>
                    <th class="px-2 py-1">Kehadiran</th>
                    <th class="px-2 py-1">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($journalsPagi as $journal)
                    <tr>
                        <td class="border px-2 py-1">{{ $journal->student?->nama }}</td>
                        <td class="border px-2 py-1">{{ Str::title($journal->kehadiran) }}</td>
                        <td class="border px-2 py-1">{{ Str::of($journal->status)->replace('_', ' ')->title() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Tabel Jurnal Sore --}}
    <h3 class="font-semibold text-md mt-10 mb-2">Waktu Sore</h3>
    @if($journalsSore->isEmpty())
        <div class="text-gray-500">Tidak ada data jurnal sore.</div>
    @else
        <table class="table-auto border w-full">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-2 py-1">Santri</th>
                    <th class="px-2 py-1">Kehadiran</th>
                    <th class="px-2 py-1">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($journalsSore as $journal)
                    <tr>
                        <td class="border px-2 py-1">{{ $journal->student?->nama }}</td>
                        <td class="border px-2 py-1">{{ Str::title($journal->kehadiran) }}</td>
                        <td class="border px-2 py-1">{{ Str::of($journal->status)->replace('_', ' ')->title() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

