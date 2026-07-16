<?php

$indexLabel = match($detailIndex) {
    'page' => 'Halaman',
    'juz' => 'Juz',
};

$scoreDetailField = match($detailType) {
    'mistakes' => 'raw_score',
    'examinations' => 'detail',
};

$aspects = ['Kelancaran', 'Fashohah', 'Tajwid'];

$jenjangs = [
    'dasar' => 'Madrasah Ibtidaiyah',
    'menengah' => 'Madrasah Tsanawiyah',
    'atas' => 'Madrasah Aliyah',
];

$komponen = [
    'Taqdim Muqorror' => 'pas_score',
    'Muwasolatul Ayat' => 'sa_score',
    'Penilaian Periodik' => 'periodic_score',
    'Rata-rata' => 'final_score',
    'Predikat' => 'predikat',
];

$program_label = Arr::get(collect($programs)->where('slug', $rapor->program)->first(), 'nama');
$semester_label = match((int) $rapor->semester) {
    1 => 'Ganjil',
    2 => 'Genap'
};

$kelas = $classroom ?? $student->classroom;
$jenjang = Arr::get($jenjangs, $student->school->jenjang);

$formatter = new IntlDateFormatter('id_ID', IntlDateFormatter::SHORT, IntlDateFormatter::SHORT);
$formatter->setPattern('d LLLL yyyy');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rapor - {{ $student->nama }}</title>
    <link rel="stylesheet" type="text/css" href="{{ public_path('css/rapor.css') }}">
</head>
<body>
    <div style="page-break-after: always;" class="max-w-7xl mx-auto relative">
        <div class="w-full h-screen absolute top-1/4 text-center">
            <img class="w-[640px] mx-auto mt-10 align-middle" src="{{ public_path('img/transparent-logo.png') }}" />
        </div>

        <div class="absolute left-0 z-100">
            <div id="heading" class="w-full mb-3">
                <div class="float-left">
                    <img class="w-20" src="{{ storage_path('app/public/' . $student->school->logo) }}">
                </div>
                <div class="float-left text-center px-5">
                    <h1 class="uppercase font-bold text-xl">Laporan Penilaian Tahfidz Al Quran</h1>
                    <h3 class="font-bold">{{ $student->school->fullname }}</h3>
                    <p class="text-sm">{{ $student->school->alamat }}</p>
                </div>
                <div class="float-left">
                    <img class="w-24" src="{{ public_path('img/tahfidz.jpeg') }}">
                </div>
            </div>

            <div id="identity" class="mt-10 border-t pt-5 clear-both">
                <table class="w-full">
                    <tr>
                        <td class="w-64">Nama</td>
                        <td>: {{ $student->nama }}</td>
                    </tr>

                    <tr>
                        <td>NISN</td>
                        <td>: {{ $student->nisn }}</td>
                    </tr>

                    <tr>
                        <td>Jenjang</td>
                        <td>: {{ $jenjang }}</td>
                    </tr>

                    <tr>
                        <td>Kelas</td>
                        <td>: {{ str_replace('idadA', 'IDAD', $kelas->nama) }}</td>
                    </tr>

                    <tr>
                        <td>Asrama / Kamar</td>
                        <td>: {{ Arr::get($dorm, 'name') }} / {{ Arr::get($dorm, 'pivot.room') }}</td>
                    </tr>

                    <tr>
                        <td>Tahun Ajaran / Semester</td>
                        <td>: {{ str_replace('-','/', $rapor->tahun_ajaran) }} / {{ $rapor->semester }} ({{ $semester_label }})</td>
                    </tr>

                    <tr>
                        <td>Program</td>
                        <td>: {{ $program_label }}</td>
                    </tr>
                </table>
            </div>

            <div id="summary" class="mt-10">
                <h2 class="text-2xl mb-5 text-center font-bold">Rangkuman Penilaian</h2>
                <table class="w-full border-collapse border">
                    <thead>
                        <tr>
                            <th class="border p-2 w-10">No</th>
                            <th class="border p-2">Jenis Penilaian</th>
                            <th class="border p-2">Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($komponen as $jenis => $field)
                            @php
                                $value = $rapor->{$field};
                            @endphp
                            <tr>
                                <td class="border p-2 text-center">{{ $loop->iteration }}</td>
                                <td class="border p-2 text-left">{{ $jenis }}</td>
                                <td class="border p-2 text-center">{{ $field !== 'predikat' ? number_format((double) $value, 2) : $value }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="3" class="text-center p-2">{{ Arr::get($descriptions, $rapor->predikat) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="signatures" class="w-11/12 mx-auto mt-10">
                <p class="text-center">{{ $rapor->lokasi }}, {{ $formatter->format($rapor->tanggal) }}</p>
                <p class="text-center">Mengetahui</p>
                <table class="w-full mt-4 font-bold">
                    <tr>
                        <td class="text-center w-5/12">
                            <p>Kepala Tahfidz Al-Quran</p>
                            <div class="h-20 relative">
                                {{--
                                @if($kepala->file_signature)
                                    <img class="h-20 w-32 mx-auto" src="{{ storage_path('app/public/' . $kepala->file_signature) }}" />
                                @endif
                                --}}
                                @php
                                    $stamp_file = public_path('img/stamp-' . $student->gender . '.png');
                                @endphp
                                <img class="h-24 mx-auto" src="{{ $stamp_file }}" />
                            </div>
                            <div class="relative">
                                <!-- <img class="absolute -top-20 left-12 w-32" src="{{ public_path('img/tahfidz-stamp.png') }}"> -->
                                <p>{{ $rapor->kepala_tahfidz_name }}</p>
                            </div>
                        </td>
                        <td class="text-center w-3/12">
                            <div class="w-16"></div>
                        </td>
                        <td class="text-center w-4/12">
                            <p>{{ Arr::get($murobbi, 'gender') == 'male' ? 'Murobbi' : 'Murobbiyah' }}</p>
                            <div class="h-20">
                                @if($murobbi->employee->file_signature)
                                    <img class="h-20 w-32 mx-auto" src="{{ storage_path('app/public/' . $murobbi->employee->file_signature) }}" />
                                @endif
                            </div>
                            <p>{{ Str::replace('MUHAMMAD', 'M.', Arr::get($murobbi, 'nama')) }}</p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div style="page-break-after: always;" class="max-w-7xl mx-auto relative">
        <div class="w-full h-screen absolute top-2/5 text-center">
            <img class="w-[640px] mx-auto mt-56 align-middle" src="{{ public_path('img/transparent-logo.png') }}" />
        </div>

        <div class="absolute left-0 z-100 w-full">
            <div id="heading" class="w-full mb-2">
                <div class="float-left">
                    <img class="w-20" src="{{ storage_path('app/public/' . $student->school->logo) }}">
                </div>
                <div class="float-left text-center px-5">
                    <h1 class="uppercase font-bold text-xl">Laporan Penilaian Tahfidz Al Quran</h1>
                    <h3 class="font-bold">{{ $student->school->fullname }}</h3>
                    <p class="text-sm">{{ $student->school->alamat }}</p>
                </div>
                <div class="float-left">
                    <img class="w-24" src="{{ public_path('img/tahfidz.jpeg') }}">
                </div>
            </div>

            <div id="identity" class="mt-10 border-t pt-5 clear-both">
                <h2 class="text-2xl mb-5 text-center font-bold">Detail Penilaian Akhir Semester</h2>
                <table class="w-full">
                    <tr>
                        <td class="w-64">Nama</td>
                        <td>: {{ $student->nama }}</td>
                    </tr>

                    <tr>
                        <td>Tahun Ajaran / Semester</td>
                        <td>: {{ str_replace('-','/', $rapor->tahun_ajaran) }} / {{ $rapor->semester }} ({{ $semester_label }})</td>
                    </tr>
                </table>
            </div>

            <div class="mt-5 pt-3 clear-both">
                <table class="w-full border-collapse border text-sm">
                    <thead class="uppercase">
                        @if($rapor->total_juz_pas === 1)
                        <tr>
                            <th colspan="5" class="border px-2 py-1 text-center">Juz {{ head($rapor->pas_juz_map) }}</th>
                        </tr>
                        @endif
                        <tr>
                            <th class="border px-2 py-1 text-center">{{ $indexLabel }}</th>
                            @foreach($aspects as $aspek)
                                <th class="border px-2 py-1 text-center">{{ $aspek }}</th>
                            @endforeach
                            <th class="border px-2 py-1 text-center">Nilai {{ $indexLabel }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(collect($detailItems)->take(23) as $item)
                            <tr>
                                <td class="border px-2 py-1 text-center">{{ $item->{$detailIndex} }}</td>
                                @foreach($aspects as $aspek)
                                    @php
                                        $subScore = Arr::get($item, $scoreDetailField . '.' . $aspek, 0);
                                    @endphp
                                    <td class="border px-2 py-1 text-center">{{ number_format($subScore, 2) }}</td>
                                @endforeach
                                <td class="border px-2 py-1 text-center">{{ number_format($item->score, 2) }}</td>
                            </tr>
                        @endforeach
                        @if(count($detailItems) <= 23)
                        <tr class="uppercase font-bold">
                            <td colspan="4" class="border p-2 text-center">Nilai {{ $detailType == 'mistakes' ? 'Juz' : 'Akhir' }}</td>
                            <td class="border p-2 text-center">{{ number_format($rapor->pas_score, 2) }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            @if(count($detailItems) <= 15)
                <div class="mt-5">
                    <table class="w-64 border-collapse border text-sm">
                        <thead class="uppercase">
                            <tr>
                                <th colspan="2" class="border px-2 py-1 text-left">Bobot Penilaian</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($aspects as $aspek)
                                <tr>
                                    <td class="border px-2 py-1 text-left">{{ $aspek }}</td>
                                    <td class="border px-2 py-1 text-right">{{ Arr::get($bobotAspekPas, $aspek) }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    @if(count($detailItems) >= 20)
    <div style="page-break-after: always;" class="max-w-7xl mx-auto relative">
        @if(count($detailItems) > 23)
            <div class="mb-5">
                <table class="w-full border-collapse border text-sm">
                    <thead class="uppercase">
                        <tr>
                            <th class="border px-2 py-1 text-center">{{ $indexLabel }}</th>
                            @foreach($aspects as $aspek)
                                <th class="border px-2 py-1 text-center">{{ $aspek }}</th>
                            @endforeach
                            <th class="border px-2 py-1 text-center">Nilai {{ $indexLabel }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(collect($detailItems)->take(-7) as $item)
                            <tr>
                                <td class="border px-2 py-1 text-center">{{ $item->{$detailIndex} }}</td>
                                @foreach($aspects as $aspek)
                                    @php
                                        $subScore = Arr::get($item, $scoreDetailField . '.' . $aspek, 0);
                                    @endphp
                                    <td class="border px-2 py-1 text-center">{{ number_format($subScore, 2) }}</td>
                                @endforeach
                                <td class="border px-2 py-1 text-center">{{ number_format($item->score, 2) }}</td>
                            </tr>
                        @endforeach
                        <tr class="uppercase font-bold">
                            <td colspan="4" class="border p-2 text-center">Nilai {{ $detailType == 'mistakes' ? 'Juz' : 'Akhir' }}</td>
                            <td class="border p-2 text-center">{{ number_format($rapor->pas_score, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif
        <table class="w-64 border-collapse border text-sm">
            <thead class="uppercase">
                <tr>
                    <th colspan="2" class="border px-2 py-1 text-left">Bobot Penilaian</th>
                </tr>
            </thead>
            <tbody>
                @foreach($aspects as $aspek)
                    <tr>
                        <td class="border px-2 py-1 text-left">{{ $aspek }}</td>
                        <td class="border px-2 py-1 text-right">{{ Arr::get($bobotAspekPas, $aspek) }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</body>
</html>
