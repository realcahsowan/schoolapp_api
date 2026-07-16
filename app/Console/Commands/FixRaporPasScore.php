<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FixRaporPasScore extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tahfidz:fix-pas-score';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memperbaiki data Rapor: menghitung ulang pas_score yang bernilai 0 untuk seluruh rapor siswa.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $raporClass = \App\Models\Tahfidz\Rapor::class;
        $studentClass = \App\Models\Student::class;

        // Ambil semua rapor dengan pas_score = 0 beserta relasi student.examinations
        $rapors = $raporClass::with(['student.examinations'])
            ->where('pas_score', 0)
            ->get();

        $updated = 0;
        foreach ($rapors as $rapor) {
            $student = $rapor->student;
            if (! $student) {
                // Lewatkan jika tidak ada student terkait
                continue;
            }
            $examinations = $student->examinations;
            $totalScore = $examinations->sum('score');
            $juzMap = (array) $rapor->pas_juz_map;
            $juzCount = count($juzMap);
            if ($juzCount > 0) {
                $rapor->pas_score = $totalScore / $juzCount;
                $rapor->save();
                $updated++;
            }
        }

        $this->info("Update selesai. Total rapor diupdate: {$updated}");
    }
}
