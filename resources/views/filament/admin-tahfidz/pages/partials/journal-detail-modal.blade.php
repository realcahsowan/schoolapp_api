<div class="space-y-2">
    <div>
        <span class="font-semibold">Tanggal:</span>
        <span>{{ $journal->tanggal ?? '-' }}</span>
    </div>
    <div>
        <span class="font-semibold">Waktu:</span>
        <span>{{ $journal->waktu ?? '-' }}</span>
    </div>
    <div>
        <span class="font-semibold">Kehadiran:</span>
        <span>{{ $journal->kehadiran ?? '-' }}</span>
    </div>
    <div>
        <span class="font-semibold">Status:</span>
        <span>{{ $journal->status ?? '-' }}</span>
    </div>
    <div>
        <span class="font-semibold">Detail Capaian:</span>
        @php
            $capaian = is_string($journal->detail_capaian ?? null)
                ? json_decode($journal->detail_capaian, true)
                : ($journal->detail_capaian ?? []);
        @endphp
        @if (!empty($capaian) && is_array($capaian))
            <ul class="list-disc ml-6">
            @foreach ($capaian as $d)
                <li>
                    <span class="font-semibold">Jenis:</span>
                    {{ $d['jenis'] === 'hb' ? 'Hafalan Baru' : ($d['jenis'] === 'hm' ? 'Hafalan Murojaah' : $d['jenis']) }},
                    <span class="font-semibold">Surat:</span> {{ $d['surat'] ?? '-' }},
                    <span class="font-semibold">Awal:</span> {{ $d['awal'] ?? '-' }},
                    <span class="font-semibold">Akhir:</span> {{ $d['akhir'] ?? '-' }}
                </li>
            @endforeach
            </ul>
        @else
            <span>-</span>
        @endif
    </div>
</div>

