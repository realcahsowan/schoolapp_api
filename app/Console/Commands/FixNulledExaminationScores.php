<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tahfidz\Examination;

class FixNulledExaminationScores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-nulled-examination-scores';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memindahkan score ke old_score pada examinations yang is_nulled = true dan score > 0.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $query = Examination::where('is_nulled', true)->where('score', '>', 0);
        $total = $query->count();
        if ($total === 0) {
            $this->info('Tidak ada data yang perlu diperbaiki.');
            return;
        }

        $this->output->progressStart($total);

        $query->chunkById(100, function ($examinations) {
            foreach ($examinations as $exam) {
                $exam->old_score = $exam->score;
                $exam->score = 0;
                $exam->save();
                $this->output->progressAdvance();
            }
        });

        $this->output->progressFinish();
        $this->info("--- Selesai: $total data diperbaiki ---");
    }
}

