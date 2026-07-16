<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixInactiveMurobbiStudents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-inactive-murobbi-students';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $pivotTable = 'tahfidz__student_murobbi';
        $updatedCount = 0;

        // Ambil student_id yang TIDAK ADA relasi murobbis dengan is_active=1, dan ADA relasi dengan is_active=0
        $students = DB::table($pivotTable)
            ->select('student_id')
            ->groupBy('student_id')
            ->havingRaw('SUM(is_active = 1) = 0 AND SUM(is_active = 0) > 0')
            ->pluck('student_id');
        $this->output->progressStart($students->count());

        foreach ($students as $studentId) {
            // Update hanya satu relasi ke true (atau semua jika diinginkan)
            $pivot = DB::table($pivotTable)
                ->where('student_id', $studentId)
                ->where('is_active', false)
                ->orderBy('id')
                ->first();
            if ($pivot) {
                DB::table($pivotTable)
                  ->where('id', $pivot->id)
                  ->update(['is_active' => true]);
                $updatedCount++;
            }
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();

        $this->info("--- Selesai: $updatedCount data diperbaiki ---");
    }
}
