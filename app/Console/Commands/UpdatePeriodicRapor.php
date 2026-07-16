<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdatePeriodicRapor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tahfidz:update-periodic-rapor {schoolId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update periodic_score hanya untuk rapor pada sekolah tertentu (schoolId), dengan sum PenilaianPeriodik->score dibagi jumlahPelaksanaanPeriodik (Configuration).';

    public function handle(): int
    {
        $this->info('Update periodic_score started...');

        $tahunAjaran = app(\App\Settings\GeneralSettings::class)->tahun_ajaran;
        $semester = app(\App\Settings\GeneralSettings::class)->semester;
        // Ambil semua config jumlahPelaksanaanPeriodik dalam bentuk school_id => payload
        $schoolId = $this->argument('schoolId');
        $periodikTarget = \App\Models\Tahfidz\Configuration::where('name', 'jumlahPelaksanaanPeriodik')->where('school_id', $schoolId)->first();
        // Ambil semua rapor tahun ajaran & semester aktif dan hanya untuk sekolah tertentu
        $rapors = \App\Models\Tahfidz\Rapor::where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->whereHas('student.classroom', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })
            ->with([
                'student.penilaianPeriodik' => function ($q) use ($tahunAjaran, $semester) {
                    $q->where('tahun_ajaran', $tahunAjaran)
                      ->where('semester', $semester);
                },
            ])
            ->get();
        $bar = $this->output->createProgressBar($rapors->count());
        $bar->start();
        $count = 0;
        foreach ($rapors as $rapor) {
            // Ambil semua score PenilaianPeriodik dari eager load
            $periodikScores = $rapor->student && $rapor->student->penilaianPeriodik
                ? $rapor->student->penilaianPeriodik->pluck('score')->filter(fn($score) => $score !== null)
                : collect();
            // Set periodic_score sesuai aturan
            if ($rapor->student &&  $periodikTarget && isset($periodikTarget->payload) && is_numeric($periodikTarget->payload)) {
                $jumlah = $periodikTarget->payload;
                $rapor->periodic_score = $periodikScores->count() > 0 ? $periodikScores->sum() / $jumlah : null;
            } else {
                $rapor->periodic_score = null;
                $this->warn('Rapor ID ' . $rapor->id . ' : config missing or payload not found/numeric. periodic_score set to null.');
            }
            $rapor->save();
            $count++;
            $bar->advance();
        }
        $bar->finish();
        $this->info("\nUpdated periodic_score for {$count} rapor(s).\nProcess finished.");
        return Command::SUCCESS;
    }
}
