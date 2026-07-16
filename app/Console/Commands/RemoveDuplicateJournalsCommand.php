<?php

namespace App\Console\Commands;

use App\Models\Tahfidz\Journal;
use App\Settings\GeneralSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RemoveDuplicateJournalsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'journal:remove-duplicates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove duplicated journals by student, tanggal, waktu for current tahun_ajaran and semester';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Ambil tahun_ajaran dan semester aktif
        $settings = app(GeneralSettings::class);
        $tahunAjaran = $settings->tahun_ajaran;
        $semester = $settings->semester;

        $this->info("Memproses journal duplikat untuk tahun_ajaran $tahunAjaran semester $semester...");

        // Cari semua kombinasi duplikat: student_id, tanggal, waktu
        $duplicates = Journal::select('student_id', 'tanggal', 'waktu', DB::raw('COUNT(*) as jumlah'))
            // ->where('tahun_ajaran', $tahunAjaran)
            // ->where('semester', $semester)
            ->groupBy('student_id', 'tanggal', 'waktu')
            ->having('jumlah', '>', 1)
            ->get();

        // dd($duplicates->count());

        if ($duplicates->isEmpty()) {
            $this->info('Tidak ada data duplikat.');

            return 0;
        }

        $totalDeleted = 0;
        foreach ($duplicates as $dup) {
            $journals = Journal::where('student_id', $dup->student_id)
                ->where('tanggal', $dup->tanggal)
                ->where('waktu', $dup->waktu)
                // ->where('tahun_ajaran', $tahunAjaran)
                // ->where('semester', $semester)
                ->orderBy('id')
                ->get();

            // Keep first, delete the rest
            $toDelete = $journals->pluck('id')->slice(1);
            if ($toDelete->count()) {
                $deleted = Journal::whereIn('id', $toDelete)->delete();
                $totalDeleted += $deleted;
                $this->info("Student {$dup->student_id} tanggal {$dup->tanggal} waktu {$dup->waktu}: $deleted data dihapus.");
            }
        }

        $this->info("Total data duplikat dihapus: $totalDeleted");

        return 0;
    }
}
