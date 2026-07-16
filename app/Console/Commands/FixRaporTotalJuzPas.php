<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FixRaporTotalJuzPas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:rapor-total-juz-pas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memperbaiki field total_juz_pas dan pas_juz_map pada rapor berdasarkan examinations tahun & semester aktif.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Memulai perbaikan total_juz_pas dan pas_juz_map pada rapor...');

        $rapors = \App\Models\Tahfidz\Rapor::whereHas('student', function ($q) {
            $q->whereHas('examinations', function ($sq) {
                $sq->currentYearSemester();
            });
        })
            ->with(['student.examinations' => function ($q) {
                $q->currentYearSemester();
            }])
            ->get();

        $count = 0;
        foreach ($rapors as $rapor) {
            $student = $rapor->student;
            if (!$student) {
                continue;
            }
            $examinations = $student->examinations;
            if ($examinations->isEmpty()) {
                continue;
            }

            // Hitung jumlah juz unik dari examinations (asumsi ada field 'juz' di examinations)
            $juzMap = $examinations->pluck('juz')->filter()->unique()->values();
            $totalJuz = $juzMap->count();

            $rapor->total_juz_pas = $totalJuz;
            $rapor->pas_juz_map = $juzMap->all();
            $rapor->save();
            $count++;
            $this->line("Rapor ID {$rapor->id} diupdate: total_juz_pas={$totalJuz}, pas_juz_map=" . json_encode($juzMap->all()));
        }

        $this->info("Selesai. Total rapor yang diupdate: {$count}");
        return 0;
    }
}
