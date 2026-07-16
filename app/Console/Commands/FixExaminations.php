<?php

namespace App\Console\Commands;

use App\Models\Student;
use App\Models\Tahfidz\Examination;
use Illuminate\Console\Command;

class FixExaminations extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tahfidz:fix-examinations';

    /**
     * The console command description.
     */
    protected $description = 'Fix duplicate examinations, only keep those in pas_juz_map (prefer locked if duplicate juz)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Fixing examinations...');

        $students = Student::whereHas('rapor')->whereHas('examinations')->with(['rapor', 'examinations'])->get();
        $bar = $this->output->createProgressBar($students->count());
        $bar->start();
        $fixCount = 0;
        foreach ($students as $student) {
            $rapor = $student->rapor;
            $examinations = $student->examinations->keyBy('id');

            if (! $rapor || $examinations->isEmpty()) {
                $bar->advance();

                continue;
            }
            $pasJuzMap = $rapor->pas_juz_map ?? [];

            // Ambil semua examinations yang juz-nya tidak ada dalam pas_juz_map
            $examsGroupByJuz = $examinations->groupBy('juz');
            $idsToKeep = collect();
            // Untuk setiap juz dalam pas_juz_map, pilih satu examination sesuai aturan
            foreach ($pasJuzMap as $juz) {
                if (! isset($examsGroupByJuz[$juz])) {
                    continue;
                }
                $examsJuzGroup = $examsGroupByJuz[$juz];
                // Prioritaskan yang locked
                $locked = $examsJuzGroup->where('is_locked', true);
                $chosen = $locked->first() ?: $examsJuzGroup->first();
                if ($chosen) {
                    $idsToKeep->push($chosen->id);
                }
            }
            // Examinations yang id-nya tidak ada di idsToKeep -> hapus
            $idsToDelete = $examinations->keys()->diff($idsToKeep);
            if ($idsToDelete->count() > 0) {
                Examination::whereIn('id', $idsToDelete)->delete();
                $fixCount++;
            }
            $bar->advance();
        }
        $bar->finish();
        $this->info("\nFinished. Students affected: {$fixCount}");

        return Command::SUCCESS;
    }
}
