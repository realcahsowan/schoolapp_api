<?php

namespace App\Jobs;

use App\Models\Student;
use App\Models\Tahfidz\Journal;
use App\Models\Tahfidz\MemorizationSummary;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SummaryMemorization implements ShouldQueue
{
    use Dispatchable, Queueable;

    protected $student;

    /**
     * Create a new job instance.
     */
    public function __construct(Student $student)
    {
        $this->student = $student;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $studentId = $this->student->id;

        // Get all journals for the student
        $journals = Journal::where('student_id', $studentId)->get();

        if ($journals->isEmpty()) {
            return;
        }

        // Group journals by tahun_ajaran and semester
        $grouped = $journals->groupBy(function ($item) {
            return $item->tahun_ajaran . '-' . $item->semester;
        });

        foreach ($grouped as $groupKey => $groupJournals) {
            // Weekly summary
            $weekly = $groupJournals->groupBy(function ($item) {
                return $item->year . '-' . $item->week;
            });

            foreach ($weekly as $key => $items) {
                $this->saveSummary('weekly', $key, $items);
            }

            // Monthly summary
            $monthly = $groupJournals->groupBy(function ($item) {
                return $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
            });

            foreach ($monthly as $key => $items) {
                $this->saveSummary('monthly', $key, $items);
            }

            // Semesterly summary (per group)
            $this->saveSummary('semesterly', $groupKey, $groupJournals);
        }
    }

    /**
     * Save summary to MemorizationSummary model.
     */
    protected function saveSummary($period, $period_key, $journals)
    {
        $first = $journals->first();
        $tahunAjaran = $first->tahun_ajaran ?? null;
        $semester = $first->semester ?? null;

        // Helper: mapping surat -> jumlah ayat, surat -> halaman, halaman -> juz
        // Anda harus menyesuaikan/mengisi data berikut sesuai mushaf yang digunakan
        $quranMeta = include base_path('resources/quran/meta.php');

        $suratAyat = $quranMeta['surat_ayat'];
        $suratHalaman = $quranMeta['surat_halaman'];
        $halamanJuz = $quranMeta['halaman_juz'];

        $suratAyatTercapai = []; // [surat => [ayat, ...]]
        $halaman = [];
        $suratLengkap = [];
        $juz = [];

        // Kumpulkan ayat-ayat tercapai per surat
        foreach ($journals as $journal) {
            foreach (['detail_capaian', 'detail_extra'] as $field) {
                if (is_array($journal->$field)) {
                    foreach ($journal->$field as $item) {
                        if (($item['jenis'] ?? null) !== 'tahsin' && isset($item['surat'], $item['awal'], $item['akhir'])) {
                            $suratId = intval($item['surat']);
                            $awal = intval($item['awal']);
                            $akhir = intval($item['akhir']);
                            for ($a = $awal; $a <= $akhir; $a++) {
                                $suratAyatTercapai[$suratId][] = $a;
                            }
                        }
                    }
                }
            }
        }

        // Deteksi surat yang sudah lengkap
        foreach ($suratAyatTercapai as $suratId => $daftarAyat) {
            $daftarAyatUnik = array_unique($daftarAyat);
            sort($daftarAyatUnik);
            $jumlahAyat = $suratAyat[$suratId] ?? 0;
            if ($jumlahAyat > 0 && count($daftarAyatUnik) === $jumlahAyat) {
                $suratLengkap[] = $suratId;
                // Ambil halaman-halaman surat ini
                if (isset($suratHalaman[$suratId])) {
                    foreach ($suratHalaman[$suratId] as $h) {
                        $halaman[] = $h;
                    }
                }
            }
        }

        // Deteksi juz dari halaman
        foreach (array_unique($halaman) as $h) {
            if (isset($halamanJuz[$h])) {
                $juz[] = $halamanJuz[$h];
            }
        }

        // detail_khusus tetap seperti sebelumnya
        foreach ($journals as $journal) {
            if (is_array($journal->detail_khusus)) {
                foreach ($journal->detail_khusus as $khusus) {
                    if (($khusus['jenis'] ?? null) !== 'tahsin') {
                        $halAwal = intval($khusus['halaman_awal'] ?? 0);
                        $halAkhir = intval($khusus['halaman_akhir'] ?? 0);
                        if ($halAwal && $halAkhir) {
                            for ($h = $halAwal; $h <= $halAkhir; $h++) {
                                $halaman[] = $h;
                                if (isset($halamanJuz[$h])) {
                                    $juz[] = $halamanJuz[$h];
                                }
                            }
                        }
                    }
                }
            }
        }

        $halaman = array_filter($halaman);
        $suratLengkap = array_filter($suratLengkap);
        $juz = array_filter($juz);

        $totalHalaman = count(array_unique($halaman));
        $totalSurat = count(array_unique($suratLengkap));
        $totalJuz = count(array_unique($juz));
        $totalAyat = collect($suratAyatTercapai)->flatten()->unique()->count();

        $detailHalaman = array_values(array_unique($halaman));
        $detailSurat = array_values(array_unique($suratLengkap));

        $totalKehadiran = collect($journals)->where('kehadiran', 'hadir')->count();

        MemorizationSummary::updateOrCreate(
            [
                'student_id' => $this->student->id,
                'periode' => $period,
                'tahun_ajaran' => $tahunAjaran,
                'semester' => $semester,
                'awal_periode' => $journals->min('tanggal'),
                'akhir_periode' => $journals->max('tanggal'),
            ],
            [
                'total_halaman' => $totalHalaman,
                'total_juz' => $totalJuz,
                'total_surat' => $totalSurat,
                'total_ayat' => $totalAyat,
                'detail_halaman' => $detailHalaman,
                'detail_surat' => $detailSurat,
                'ringkasan' => [
                    'total_kehadiran' => $totalKehadiran,
                ],
            ]
        );
    }
}
