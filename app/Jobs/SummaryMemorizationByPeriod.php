<?php

namespace App\Jobs;

use App\Models\Student;
use App\Models\Tahfidz\Journal;
use App\Models\Tahfidz\MemorizationSummary;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SummaryMemorizationByPeriod implements ShouldQueue
{
    use Dispatchable;
    use Queueable;

    protected $student;
    protected $tahunAjaran;
    protected $semester;
    protected $periodeType; // 'weekly', 'monthly', 'semesterly'
    protected $periodNumber; // week number, month number, or semester number

    /**
     * Create a new job instance.
     */
    public function __construct(Student $student, $tahunAjaran, $semester, $periodeType, $periodNumber)
    {
        $this->student = $student;
        $this->tahunAjaran = $tahunAjaran;
        $this->semester = $semester;
        $this->periodeType = $periodeType;
        $this->periodNumber = $periodNumber;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $query = Journal::where('student_id', $this->student->id)
            ->where('tahun_ajaran', $this->tahunAjaran)
            ->where('semester', $this->semester);

        // Filter by period
        if ($this->periodeType === 'weekly') {
            $query->where('week', $this->periodNumber);
        } elseif ($this->periodeType === 'monthly') {
            $query->where('month', $this->periodNumber);
        } elseif ($this->periodeType === 'semesterly') {
            // No additional filter needed, already filtered by tahun_ajaran & semester
        }

        $journals = $query->get();

        if ($journals->isEmpty()) {
            return;
        }

        $this->saveSummary(
            $this->periodeType,
            $this->periodNumber,
            $journals,
            $this->tahunAjaran,
            $this->semester
        );
    }

    /**
     * Save summary to MemorizationSummary model.
     */
    protected function saveSummary($period, $period_key, $journals, $tahunAjaran, $semester)
    {
        $quranMeta = include base_path('resources/quran/meta.php');
        $suratAyat = $quranMeta['surat_ayat'];
        $suratHalaman = $quranMeta['surat_halaman'];
        $halamanJuz = $quranMeta['halaman_juz'];

        $suratAyatTercapai = [];
        $suratAyatHafalanBaru = []; // Untuk jenis 'hb'
        $suratAyatHafalanMurojaah = []; // Untuk jenis 'hm'
        $suratAyatTahsin = []; // Untuk jenis 'tahsin'
        $pages = [];
        $suratLengkap = [];
        $juz = [];
        $pagesTahsin = [];

        // Kumpulkan ayat-ayat tercapai per surat
        foreach ($journals as $journal) {
            foreach (['detail_capaian', 'detail_extra'] as $field) {
                if (is_array($journal->$field)) {
                    foreach ($journal->$field as $item) {
                        if (isset($item['surat'], $item['awal'], $item['akhir'])) {
                            $suratId = intval($item['surat']);
                            $awal = intval($item['awal']);
                            $akhir = intval($item['akhir']);
                            // Semua ayat tercapai (kecuali tahsin)
                            if (($item['jenis'] ?? null) !== 'tahsin') {
                                for ($a = $awal; $a <= $akhir; $a++) {
                                    $suratAyatTercapai[$suratId][] = $a;
                                    if (($item['jenis'] ?? null) === 'hb') {
                                        $suratAyatHafalanBaru[$suratId][] = $a;
                                    }
                                    if (($item['jenis'] ?? null) === 'hm') {
                                        $suratAyatHafalanMurojaah[$suratId][] = $a;
                                    }
                                }
                            }
                            // Ayat tahsin
                            if (($item['jenis'] ?? null) === 'tahsin') {
                                for ($a = $awal; $a <= $akhir; $a++) {
                                    $suratAyatTahsin[$suratId][] = $a;
                                }
                            }
                        }
                    }
                }
            }
        }

        // detail_khusus tetap seperti sebelumnya
        foreach ($journals as $journal) {
            if (is_array($journal->detail_khusus) && !empty($journal->detail_khusus)) {
                $halAwal = intval($journal->detail_khusus['halaman_awal'] ?? 0);
                $halAkhir = intval($journal->detail_khusus['halaman_akhir'] ?? 0);

                if (($journal->detail_khusus['jenis'] ?? null) !== 'tahsin_tilawah') {
                    if ($halAwal && $halAkhir) {
                        for ($h = $halAwal; $h <= $halAkhir; $h++) {
                            $pages[] = $h;
                        }
                    }
                }
                if (($journal->detail_khusus['jenis'] ?? null) === 'tahsin_tilawah') {
                    if ($halAwal && $halAkhir) {
                        for ($h = $halAwal; $h <= $halAkhir; $h++) {
                            $pagesTahsin[] = $h;
                        }
                    }
                }
            }
        }

        $mapQuran = include base_path('resources/quran/map.php');
        foreach ($suratAyatTercapai as $surat => $daftarAyat) {
            $pages = array_merge($pages, getHalamanBySuratAyat($surat, $daftarAyat, $mapQuran));
        }
        $pages = array_values(array_unique($pages));

        // Buat array halaman untuk hafalan baru (hb) dan murojaah (hm)
        $hafalanBaruPages = [];
        $hafalanMurojaahPages = [];

        foreach ($suratAyatHafalanBaru as $surat => $daftarAyat) {
            $hafalanBaruPages = array_merge(
                $hafalanBaruPages,
                getHalamanBySuratAyat($surat, $daftarAyat, $mapQuran)
            );
        }
        $hafalanBaruPages = array_values(array_unique($hafalanBaruPages));

        // Ensure all elements in all arrays are unique and sorted within $suratAyatHafalanBaru
        foreach ($suratAyatHafalanBaru as $surat => &$ayat) {
            $ayat = array_values(array_unique($ayat));
            sort($ayat);
        }
        unset($ayat);

        // Ensure all elements in all arrays are unique and sorted within $suratAyatHafalanMurojaah
        foreach ($suratAyatHafalanMurojaah as $surat => &$ayat) {
            $ayat = array_values(array_unique($ayat));
            sort($ayat);
        }
        unset($ayat);

        foreach ($suratAyatHafalanMurojaah as $surat => $daftarAyat) {
            $hafalanMurojaahPages = array_merge(
                $hafalanMurojaahPages,
                getHalamanBySuratAyat($surat, $daftarAyat, $mapQuran)
            );
        }
        $hafalanMurojaahPages = array_values(array_unique($hafalanMurojaahPages));

        // buat logic untuk surat lengkap
        // Ensure all elements in all arrays are unique and sorted within $suratAyatTercapai
        foreach ($suratAyatTercapai as $surat => &$daftarAyat) {
            $daftarAyat = array_values(array_unique($daftarAyat));
            sort($daftarAyat);
        }
        unset($daftarAyat);

        $suratLengkap = array_filter($suratLengkap);
        $juz = array_filter($juz);

        $totalHalaman = count(array_unique($pages));
        $totalSurat = count(array_unique($suratLengkap));
        $totalAyat = collect($suratAyatTercapai)->flatten()->unique()->count();

        $detailHalaman = array_values(array_unique($pages));
        // $detailSurat = array_values(array_unique($suratLengkap));

        $totalKehadiran = collect($journals)->where('kehadiran', 'hadir')->count();

        // Ambil tanggal awal dan akhir periode
        $awalPeriode = $journals->min('tanggal');
        $akhirPeriode = $journals->max('tanggal');

        // Query KalenderHafalan sesuai periode
        $kalenderQuery = \App\Models\Tahfidz\KalenderHafalan::where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester);
        // ->whereDate('tanggal', '>=', $awalPeriode)
        // ->whereDate('tanggal', '<=', $akhirPeriode);

        if ($period === 'weekly') {
            $kalenderQuery->where('week', $period_key);
        } elseif ($period === 'monthly') {
            $kalenderQuery->where('month', $period_key);
        }
        // Jika semesterly, tidak perlu filter week/month

        $kalenders = $kalenderQuery->get();

        // Ambil tanggal awal dan akhir periode dari Kalender Hafalan
        $awalPeriode = $kalenders->min('tanggal');
        $akhirPeriode = $kalenders->max('tanggal');

        // Mapping data kalender untuk mengambil hp_summary dan hs_summary sesuai key (level kelas)
        $levelKey = $this->student->classroom->level ?? null;
        $hpSummaryByJenis = [];
        $hsSummaryByJenis = [];

        foreach ($kalenders as $kal) {
            // Mapping hp_summary
            if (is_array($kal->hp_summary) && $levelKey && isset($kal->hp_summary[$levelKey])) {
                foreach ($kal->hp_summary[$levelKey] as $item) {
                    if (isset($item['surat'], $item['awal'], $item['akhir'], $item['jenis'])) {
                        $jenis = $item['jenis'];
                        $surat = (string) $item['surat'];
                        $awal = intval($item['awal']);
                        $akhir = intval($item['akhir']);
                        if (!isset($hpSummaryByJenis[$jenis])) {
                            $hpSummaryByJenis[$jenis] = [];
                        }
                        if (!isset($hpSummaryByJenis[$jenis][$surat])) {
                            $hpSummaryByJenis[$jenis][$surat] = [];
                        }
                        $hpSummaryByJenis[$jenis][$surat] = array_merge(
                            $hpSummaryByJenis[$jenis][$surat],
                            range($awal, $akhir)
                        );
                    }
                }
            }
            // Mapping hs_summary
            if (is_array($kal->hs_summary) && $levelKey && isset($kal->hs_summary[$levelKey])) {
                foreach ($kal->hs_summary[$levelKey] as $item) {
                    if (isset($item['surat'], $item['awal'], $item['akhir'], $item['jenis'])) {
                        $jenis = $item['jenis'];
                        $surat = (string) $item['surat'];
                        $awal = intval($item['awal']);
                        $akhir = intval($item['akhir']);
                        if (!isset($hsSummaryByJenis[$jenis])) {
                            $hsSummaryByJenis[$jenis] = [];
                        }
                        if (!isset($hsSummaryByJenis[$jenis][$surat])) {
                            $hsSummaryByJenis[$jenis][$surat] = [];
                        }
                        $hsSummaryByJenis[$jenis][$surat] = array_merge(
                            $hsSummaryByJenis[$jenis][$surat],
                            range($awal, $akhir)
                        );
                    }
                }
            }
        }

        $mergeBySurat = function ($a, $b) {
            $result = $a;
            foreach ($b as $surat => $arr) {
                if (isset($result[$surat])) {
                    $result[$surat] = array_merge($result[$surat], $arr);
                } else {
                    $result[$surat] = $arr;
                }
            }
            return $result;
        };

        $kurikulum = [
            'periode' => [
                'awal' => $awalPeriode,
                'akhir' => $akhirPeriode,
            ],
            'hb' => array_map(
                fn($arr) => array_values(array_unique($arr)),
                $mergeBySurat($hpSummaryByJenis['hb'] ?? [], $hsSummaryByJenis['hb'] ?? [])
            ),
            'hm' => array_map(
                fn($arr) => array_values(array_unique($arr)),
                $mergeBySurat($hpSummaryByJenis['hm'] ?? [], $hsSummaryByJenis['hm'] ?? [])
            ),
        ];

        // Hitung juz yang sudah lengkap dihafal semua halamannya
        $juzLengkap = [];
        foreach ($halamanJuz as $halaman => $juzKe) {
            // Ambil semua halaman untuk juz ini
            $halamanJuzKe = array_keys(array_filter($halamanJuz, fn($j) => $j == $juzKe));
            // Jika semua halaman juz ini sudah ada di $halaman (detail_halaman)
            if (!array_diff($halamanJuzKe, $pages)) {
                $juzLengkap[] = $juzKe;
            }
        }
        $totalJuz = count(array_unique($juzLengkap));

        // Hitung detail kehadiran
        $kehadiranDetail = [
            'hadir' => collect($journals)->where('kehadiran', 'hadir')->count(),
            'izin' => collect($journals)->where('kehadiran', 'izin')->count(),
            'sakit' => collect($journals)->where('kehadiran', 'sakit')->count(),
            'alpa' => collect($journals)->where('kehadiran', 'alpa')->count(),
        ];

        // Hapus MemorizationSummary jika period adalah 'semesterly'
        // if ($period === 'semesterly') {
        MemorizationSummary::where('student_id', $this->student->id)
            ->where('periode', $period)
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->where('awal_periode', $awalPeriode)
            // ->where('akhir_periode', $akhirPeriode)
            ->delete();
        // }

        MemorizationSummary::updateOrCreate(
            [
                'student_id' => $this->student->id,
                'periode' => $period,
                'tahun_ajaran' => $tahunAjaran,
                'semester' => $semester,
                'awal_periode' => $awalPeriode,
                'akhir_periode' => $akhirPeriode,
            ],
            [
                'total_halaman' => $totalHalaman,
                'total_juz' => $totalJuz,
                'total_surat' => $totalSurat,
                'total_ayat' => $totalAyat,
                'detail_halaman' => $detailHalaman,
                'detail_surat' => $suratAyatTercapai,
                'ringkasan' => [
                    'total_kehadiran' => $totalKehadiran,
                    'kehadiran_detail' => $kehadiranDetail, // <-- Tambahkan ini
                    'hafalan_baru' => [
                        'total_halaman' => count($hafalanBaruPages),
                        'halaman' => array_values($hafalanBaruPages),
                        'detail_surat' => $suratAyatHafalanBaru,
                    ],
                    'hafalan_murojaah' => [
                        'total_halaman' => count($hafalanMurojaahPages),
                        'halaman' => array_values($hafalanMurojaahPages),
                        'detail_surat' => $suratAyatHafalanMurojaah,
                    ],
                    'tahsin' => [
                        'total_halaman' => count(array_unique($pagesTahsin)),
                        'halaman' => array_values(array_unique($pagesTahsin)),
                    ],
                ],
                'kurikulum' => $kurikulum,
            ]
        );
    }
}

function getHalamanBySuratAyat($surat, $daftarAyat, $mapQuran)
{
    $halamanTercapai = [];
    foreach ($mapQuran as $halaman => $entries) {
        foreach ($entries as $entry) {
            if ($entry['surah_index'] == $surat) {
                // Cek irisan ayat
                $rangeAyat = range($entry['ayah_start'], $entry['ayah_end']);
                if (count(array_intersect($daftarAyat, $rangeAyat)) > 0) {
                    $halamanTercapai[] = (int) $halaman;
                    break; // satu entry cukup, lanjut halaman berikutnya
                }
            }
        }
    }
    return array_unique($halamanTercapai);
}
