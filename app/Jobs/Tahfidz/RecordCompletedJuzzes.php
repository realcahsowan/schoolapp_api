<?php

namespace App\Jobs\Tahfidz;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Queueable;
use App\Models\Tahfidz\MemorizationSummary;

class RecordCompletedJuzzes implements ShouldQueue
{
    use Dispatchable, Queueable;

    protected $memorizationSummary;

    /**
     * Create a new job instance.
     */
    public function __construct(MemorizationSummary $memorizationSummary)
    {
        $this->memorizationSummary = $memorizationSummary;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $studentId = $this->memorizationSummary->student_id;
        // $completedJuz = $this->memorizationSummary->total_juz;
        $tahunAjaran = app(\App\Settings\GeneralSettings::class)->tahun_ajaran;
        $semester = app(\App\Settings\GeneralSettings::class)->semester;

        $mapQuran = include base_path('resources/quran/map.php');
        $hafalPages = array_unique($this->memorizationSummary->detail_halaman ?? []);

        $juzHalaman = [];
        foreach ($mapQuran as $page => $blocks) {
            $blocks = isset($blocks[0]) ? $blocks : [$blocks];
            foreach ($blocks as $block) {
                $juz = $block["juz"];
                if (!isset($juzHalaman[$juz])) {
                    $juzHalaman[$juz] = [];
                }
                $juzHalaman[$juz][] = $page;
            }
        }

        // Cek juz yang sudah lengkap
        foreach ($juzHalaman as $juz => $halamanJuz) {
            $halamanJuz = array_unique($halamanJuz);
            sort($halamanJuz);
            sort($hafalPages);
            // \Log::info([$this->studentId, $halamanJuz, $hafalPages]);
            $sudahHafal = !array_diff($halamanJuz, $hafalPages);
            if ($sudahHafal) {
                \App\Models\Tahfidz\CompletedJuz::updateOrCreate(
                    [
                        "student_id" => $studentId,
                        "tahun_ajaran" => $tahunAjaran,
                        "semester" => $semester,
                        "juz_number" => $juz,
                    ],
                    [
                        "completed_at" => now(),
                    ],
                );
            }
        }
    }
}
