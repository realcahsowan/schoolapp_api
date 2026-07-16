<?php

namespace App\Console\Commands;

use App\Models\Tahfidz\KalenderHafalan;
use App\Models\Tahfidz\KurikulumHafalan;
use App\Settings\GeneralSettings;
use Illuminate\Console\Command;

class GenerateKurikulumHafalan extends Command
{
    protected $signature = 'tahfidz:generate-kurikulum-hafalan {--tahun_ajaran=} {--semester=}';

    protected $description = 'Generate KurikulumHafalan from KalenderHafalan hp_summary for current tahun_ajaran and semester';

    public function handle(): int
    {
        $settings = app(GeneralSettings::class);
        $tahunAjaran = $this->option('tahun_ajaran') ?: $settings->tahun_ajaran;
        $semester = (int) ($this->option('semester') ?: $settings->semester);

        $this->info("Generating KurikulumHafalan for TA {$tahunAjaran}, Semester {$semester}...");

        // Load Quran map (page -> blocks with surah_index, ayah_start, ayah_end, juz)
        $quranMap = include base_path('resources/quran/map.php');

        // Index data per school and grade
        $aggregates = [];

        $kalenders = KalenderHafalan::query()
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->where(function ($q) {
                $q->whereNotNull('hp_summary')->orWhereNotNull('hs_summary');
            })
            ->get();

        if ($kalenders->isEmpty()) {
            $this->warn('Tidak ada data KalenderHafalan untuk tahun ajaran dan semester ini.');

            return self::SUCCESS;
        }

        foreach ($kalenders as $kalender) {
            $schoolId = $kalender->school_id;
            $hpSummary = $kalender->hp_summary ?? [];
            $hsSummary = $kalender->hs_summary ?? [];

            // Gabungkan hp_summary dan hs_summary (format item sama: {jenis: hb|hm, surat, awal, akhir})
            $allSummaries = [];
            foreach ([$hpSummary, $hsSummary] as $summary) {
                foreach ($summary as $grade => $items) {
                    foreach ($items as $it) {
                        $allSummaries[$grade][] = $it;
                    }
                }
            }

            foreach ($allSummaries as $grade => $items) {
                if (! isset($aggregates[$schoolId][$grade])) {
                    $aggregates[$schoolId][$grade] = [
                        'hb' => [
                            'detail' => [], // surat => [[awal, akhir], ...] merged ranges
                            'juz' => [],    // set of juz numbers covered
                        ],
                        'hm' => [
                            'detail' => [],
                            'juz' => [],
                        ],
                    ];
                }

                foreach ($items as $item) {
                    $jenis = $item['jenis'] ?? null; // 'hb' | 'hm'
                    $surat = (int) ($item['surat'] ?? 0);
                    $awal = (int) ($item['awal'] ?? 0);
                    $akhir = (int) ($item['akhir'] ?? 0);

                    if (! in_array($jenis, ['hb', 'hm'], true) || $surat <= 0 || $awal <= 0 || $akhir <= 0 || $akhir < $awal) {
                        continue;
                    }

                    // Merge range into detail for this jenis & surat
                    $this->mergeRange($aggregates[$schoolId][$grade][$jenis]['detail'], $surat, [$awal, $akhir]);

                    // Compute juz coverage for this item using quran map
                    $juzCovered = $this->getJuzCoveredBySuratRange($quranMap, $surat, $awal, $akhir);
                    $aggregates[$schoolId][$grade][$jenis]['juz'] = array_values(array_unique(array_merge(
                        $aggregates[$schoolId][$grade][$jenis]['juz'],
                        $juzCovered
                    )));
                }
            }
        }

        // Persist aggregates into KurikulumHafalan
        $createdOrUpdated = 0;
        foreach ($aggregates as $schoolId => $byGrade) {
            foreach ($byGrade as $grade => $data) {
                // HB totals
                [$detailHb, $totalAyatHb, $totalSuratHb] = $this->finalizeDetailAndTotals($data['hb']['detail']);
                $totalJuzHb = count(array_unique($data['hb']['juz']));

                // HM totals
                [$detailHm, $totalAyatHm, $totalSuratHm] = $this->finalizeDetailAndTotals($data['hm']['detail']);
                $totalJuzHm = count(array_unique($data['hm']['juz']));

                KurikulumHafalan::updateOrCreate(
                    [
                        'school_id' => $schoolId,
                        'tahun_ajaran' => $tahunAjaran,
                        'semester' => $semester,
                        'grade' => (string) $grade,
                        'program' => null, // not specified in sumber data
                    ],
                    [
                        'detail_hafalan_baru' => $detailHb,
                        'total_ayat_hafalan_baru' => $totalAyatHb,
                        'total_surat_hafalan_baru' => $totalSuratHb,
                        'total_juz_hafalan_baru' => $totalJuzHb,
                        'detail_hafalan_murojaah' => $detailHm,
                        'total_ayat_hafalan_murojaah' => $totalAyatHm,
                        'total_surat_hafalan_murojaah' => $totalSuratHm,
                        'total_juz_hafalan_murojaah' => $totalJuzHm,
                    ]
                );

                $createdOrUpdated++;
                $this->line("Saved KurikulumHafalan: school {$schoolId}, grade {$grade}");
            }
        }

        $this->info("Selesai. Records processed: {$createdOrUpdated}");

        return self::SUCCESS;
    }

    /**
     * Merge a new [awal, akhir] range into the detail array for a given surat.
     * $detail format reference (by reference): [ surat_number => [[awal, akhir], ...] ]
     */
    protected function mergeRange(array &$detail, int $surat, array $range): void
    {
        [$awal, $akhir] = $range;
        if (! isset($detail[$surat])) {
            $detail[$surat] = [[$awal, $akhir]];

            return;
        }

        $ranges = $detail[$surat];
        $ranges[] = [$awal, $akhir];

        // Sort by start, then merge overlapping/adjacent
        usort($ranges, function ($a, $b) {
            if ($a[0] === $b[0]) {
                return $a[1] <=> $b[1];
            }

            return $a[0] <=> $b[0];
        });

        $merged = [];
        foreach ($ranges as $r) {
            if (empty($merged)) {
                $merged[] = $r;
            } else {
                $last = &$merged[count($merged) - 1];
                if ($r[0] <= $last[1] + 1) {
                    // overlap or adjacent, extend
                    $last[1] = max($last[1], $r[1]);
                } else {
                    $merged[] = $r;
                }
                unset($last);
            }
        }

        $detail[$surat] = $merged;
    }

    /**
     * From a finalized detail (surat => list of merged ranges), compute:
     *  - detail (unchanged)
     *  - total ayat across all surat and ranges
     *  - total surat (count keys)
     */
    protected function finalizeDetailAndTotals(array $detail): array
    {
        $totalAyat = 0;
        foreach ($detail as $surat => $ranges) {
            foreach ($ranges as $r) {
                $totalAyat += ($r[1] - $r[0] + 1);
            }
        }
        $totalSurat = count($detail);

        return [$detail, $totalAyat, $totalSurat];
    }

    /**
     * Determine Juz numbers covered by a given surat and ayat range using quran map.
     * Returns a list of unique juz numbers.
     */
    protected function getJuzCoveredBySuratRange(array $quranMap, int $surat, int $awal, int $akhir): array
    {
        $juzSet = [];
        foreach ($quranMap as $page => $blocks) {
            $blocks = isset($blocks[0]) ? $blocks : [$blocks];
            foreach ($blocks as $block) {
                if ((int) $block['surah_index'] !== $surat) {
                    continue;
                }
                $bAwal = (int) $block['ayah_start'];
                $bAkhir = (int) $block['ayah_end'];
                // Check intersection
                if ($bAwal <= $akhir && $bAkhir >= $awal) {
                    $juzSet[$block['juz']] = true;
                }
            }
        }

        return array_map('intval', array_keys($juzSet));
    }
}
