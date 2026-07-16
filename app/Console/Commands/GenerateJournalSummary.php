<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tahfidz\JournalSummary;
use App\Models\Tahfidz\Journal;
use App\Models\Murobbi;
use App\Models\Tahfidz\KalenderHafalan;

class GenerateJournalSummary extends Command
{
    protected $signature = 'journal:summary {--tahun_ajaran=} {--semester=}';
    protected $description = 'Generate JournalSummary records grouped by murobbi and kalender';

    public function handle()
    {
        $tahunAjaran = $this->option('tahun_ajaran');
        $semester = $this->option('semester');

        if (!$tahunAjaran) {
            $tahunAjaran = $this->ask('Masukkan tahun ajaran');
        }
        if (!$semester) {
            $semester = $this->ask('Masukkan semester');
        }

        $journals = Journal::where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->get();

        if ($journals->isEmpty()) {
            $this->info('No journals found for tahun_ajaran and semester.');
            return 0;
        }

        $murobbiIds = $journals->pluck('murobbi_id')->unique();
        $kalenderIds = $journals->pluck('kalender_id')->unique();

        $murobbis = Murobbi::whereIn('id', $murobbiIds)
            ->withCount('students')
            ->get()
            ->keyBy('id');
        $kalenders = KalenderHafalan::whereIn('id', $kalenderIds)->get()->keyBy('id');

        $grouped = $journals->groupBy(function ($journal) {
            return $journal->murobbi_id . '-' . $journal->kalender_id;
        });

        foreach ($grouped as $key => $group) {
            [$murobbiId, $kalenderId] = explode('-', $key);

            $murobbi = $murobbis->get($murobbiId);
            $kalender = $kalenders->get($kalenderId);

            if (!$murobbi || !$kalender) {
                $this->warn("Murobbi/Kalender not found for group: $key");
                continue;
            }

            $studentsCount = $murobbi->students_count;

            $target_pagi = !empty($kalender->hp_summary) ? $studentsCount * 1 : 0;
            $target_sore = !empty($kalender->hs_summary) ? $studentsCount * 1 : 0;
            $target = ($kalender->is_hp_only ?? false) ? $target_pagi : ($target_pagi + $target_sore);

            $summary = [
                'murobbi_id'     => $murobbiId,
                'kalender_id'    => $kalenderId,
                'tahun_ajaran'   => $tahunAjaran,
                'semester'       => $semester,
                'tanggal'        => $kalender->tanggal,
                'target'         => $target,
                'target_pagi'    => $target_pagi,
                'target_sore'    => $target_sore,
                'terisi'         => $group->count(),
                'terisi_pagi'    => $group->where('waktu', 'pagi')->count(),
                'terisi_sore'    => $group->where('waktu', 'sore')->count(),
                'input_summary'  => $group->pluck('kehadiran', 'student_id')->toArray(),
                'completed'      => ($target == $group->count()),
                'completed_pagi' => ($target_pagi == $group->where('waktu', 'pagi')->count()),
                'completed_sore' => ($target_sore == $group->where('waktu', 'sore')->count()),
                'hp_only'        => $kalender->is_hp_only ?? false,
            ];

            $journalSummary = JournalSummary::updateOrCreate(
                [
                    'murobbi_id'  => $murobbiId,
                    'kalender_id' => $kalenderId,
                    'tahun_ajaran' => $tahunAjaran,
                    'semester'     => $semester,
                ],
                $summary
            );

            $this->info('JournalSummary generated: ID ' . $journalSummary->id . " (Murobbi: $murobbiId, Kalender: $kalenderId)");
        }

        return 0;
    }
}
