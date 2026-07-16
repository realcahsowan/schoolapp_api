<?php

namespace App\Console\Commands;

use App\Models\Tahfidz\Examination;
use App\Settings\GeneralSettings;
use Illuminate\Console\Command;

class SyncExaminationPenguji extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tahfidz:sync-examination-penguji';

    /**
     * The console command description.
     */
    protected $description = 'Synchronize examination penguji_id with the student penguji relation for the active general settings period';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $settings = app(GeneralSettings::class);
        $tahunAjaran = $settings->tahun_ajaran;
        $semester = $settings->semester;

        $this->info("Syncing examinations for {$tahunAjaran} / semester {$semester}...");

        $query = Examination::query()
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->with([
                'student.pengujis' => function ($relation) use ($tahunAjaran, $semester) {
                    $relation->wherePivot('tahun_ajaran', $tahunAjaran)
                        ->wherePivot('semester', $semester);
                },
            ]);

        $total = (clone $query)->count();

        if ($total === 0) {
            $this->info('Tidak ada examination untuk diproses.');

            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $updated = 0;
        $skipped = 0;
        $missingStudent = 0;
        $missingPenguji = 0;

        $query->chunkById(100, function ($examinations) use (
            &$updated,
            &$skipped,
            &$missingStudent,
            &$missingPenguji,
            $tahunAjaran,
            $semester,
            $bar
        ) {
            foreach ($examinations as $examination) {
                $student = $examination->student;

                if (! $student) {
                    $missingStudent++;
                    $bar->advance();
                    continue;
                }

                $targetPenguji = $student->pengujis->sortByDesc('id')->first();

                if (! $targetPenguji) {
                    $missingPenguji++;
                    $bar->advance();
                    continue;
                }

                if ((int) $examination->penguji_id !== (int) $targetPenguji->id) {
                    $examination->penguji_id = $targetPenguji->id;
                    $examination->save();
                    $updated++;
                } else {
                    $skipped++;
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info("Done. Updated: {$updated}, skipped: {$skipped}, missing student: {$missingStudent}, missing penguji: {$missingPenguji}");

        return Command::SUCCESS;
    }
}
