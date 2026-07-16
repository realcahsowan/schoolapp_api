<?php

namespace App\Console\Commands;

use App\Models\Tahfidz\Configuration;
use Illuminate\Console\Command;
use App\Models\Tahfidz\Rapor;
use App\Models\Tahfidz\Examination;

class SyncRaporWithExaminations extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tahfidz:sync-rapor-examinations';

    /**
     * The console command description.
     */
    protected $description = 'Synchronize Rapor data with locked Examinations';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Implementation will be filled next
        $this->info('Sync started...');

        $count = 0;
        $tahunAjaran = app(\App\Settings\GeneralSettings::class)->tahun_ajaran;
        $semester = app(\App\Settings\GeneralSettings::class)->semester;

        $periodikTarget = Configuration::where('name', 'jumlahPelaksanaanPeriodik')->pluck('payload', 'school_id');
        $rapors = Rapor::where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->with('student.classroom')
            ->get();
        $bar = $this->output->createProgressBar($rapors->count());
        $bar->start();

        foreach ($rapors as $rapor) {
            // Ambil examinations dengan student_id yang sama dan locked
            $examinations = Examination::where('student_id', $rapor->student_id)
                ->where('is_locked', true)
                ->get();
            if ($examinations->isEmpty()) {
                // Tetap update sa_score meskipun tidak ada PAS; jangan lanjut
                $examinations = collect();
            }
            // Build pas_juz_scores: [juz => score]
            $pasJuzScores = [];
            foreach ($examinations as $exam) {
                if (!in_array($exam->juz, $rapor->pas_juz_map)) {
                    continue;
                }
                if ($exam->juz !== null && $exam->score !== null && in_array($exam->juz, $rapor->pas_juz_map)) {
                    $pasJuzScores[$exam->juz] = $exam->score;
                }
            }

            if (empty($pasJuzScores)) {
                $rapor->pas_score = 0;
                $rapor->pas_succeed = false;
            } else {
                $rapor->pas_juz_scores = $pasJuzScores;
                $rapor->pas_completed_juz = array_keys($pasJuzScores);
                $totalJuzPas = (int)($rapor->total_juz_pas ?? 0);
                $sumScore = array_sum($pasJuzScores);
                $rapor->pas_score = $totalJuzPas > 0 ? $sumScore / $totalJuzPas : 0;
                $rapor->pas_succeed = $totalJuzPas > 0 && count($rapor->pas_completed_juz) == $totalJuzPas;
            }

            // Update sa_score dari MemberMuwashalatAyat dengan student_id, tahun_ajaran, semester
            $memberSa = \App\Models\Tahfidz\MemberMuwashalatAyat::where('student_id', $rapor->student_id)
                ->where('tahun_ajaran', $rapor->tahun_ajaran)
                ->where('semester', $rapor->semester)
                ->first();
            $rapor->sa_score = $memberSa?->score ?? null;

            // Update periodic_score dengan rata-rata score PenilaianPeriodik
            $periodikScores = \App\Models\Tahfidz\PenilaianPeriodik::where('student_id', $rapor->student_id)
                ->where('tahun_ajaran', $rapor->tahun_ajaran)
                ->where('semester', $rapor->semester)
                ->pluck('score')
                ->filter(fn ($score) => $score !== null);
            // $rapor->periodic_score = $periodikScores->count() > 0 ? $periodikScores->avg() : null;
            if ($rapor->student && $rapor->student->classroom && isset($periodikTarget[$rapor->student->classroom->school_id])) {
                $rapor->periodic_score = $periodikScores->count() > 0 ? $periodikScores->sum() / $periodikTarget[$rapor->student->classroom->school_id] : null;
            } else {
                $rapor->periodic_score = null;
                $this->warn('Rapor ID ' . $rapor->id . ': Student or classroom missing, periodic_score set to null.');
            }

            $rapor->save();
            $count++;
            $bar->advance();
        }
        $bar->finish();
        $this->info("\nSynced {$count} rapor(s).\nSync finished.");
        return Command::SUCCESS;
    }
}
