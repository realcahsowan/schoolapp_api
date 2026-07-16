<?php

namespace App\Console\Commands;

use App\Models\Tahfidz\Examination;
use Illuminate\Console\Command;

class FillSchoolIdInExaminations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fill-school-id-in-examinations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fill missing school_id values in tahfidz__examinations from the related student classroom';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Updating missing school_id values for examinations...');

        $query = Examination::query()
            ->whereNull('school_id')
            ->with(['student.classroom']);

        $total = (clone $query)->count();
        $this->output->progressStart($total);

        $updated = 0;
        $skipped = 0;

        $query->chunkById(100, function ($examinations) use (&$updated, &$skipped) {
            foreach ($examinations as $examination) {
                $schoolId = $examination->student?->classroom?->school_id;

                if ($schoolId) {
                    $examination->update(['school_id' => $schoolId]);
                    $updated++;
                } else {
                    $skipped++;
                }

                $this->output->progressAdvance();
            }
        });

        $this->output->progressFinish();

        $this->info("Done. Updated: {$updated}, skipped: {$skipped}");

        return Command::SUCCESS;
    }
}
