<?php

namespace App\Jobs\Tahfidz;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RecordJournalSummary implements ShouldQueue
{
    use Queueable;

    protected $murobbiId;
    protected $kalenderId;

    /**
     * Create a new job instance.
     */
    public function __construct($murobbiId, $kalenderId)
    {
        $this->murobbiId = $murobbiId;
        $this->kalenderId = $kalenderId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $murobbi = \App\Models\Murobbi::withCount('students')->find($this->murobbiId);
        $kalender = \App\Models\Tahfidz\KalenderHafalan::find($this->kalenderId);

        if (!$murobbi || !$kalender) {
            // Optionally log or throw exception
            return;
        }

        if ($murobbi->school_id !== $kalender->school_id) {
            // Optionally log or throw exception
            return;
        }

        $tahunAjaran = $kalender->tahun_ajaran;
        $semester = $kalender->semester;

        $journals = \App\Models\Tahfidz\Journal::where('murobbi_id', $this->murobbiId)
            ->where('kalender_id', $this->kalenderId)
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->get();

        $studentsCount = $murobbi->students_count;

        $target_pagi = !empty($kalender->hp_summary) ? $studentsCount * 1 : 0;
        $target_sore = !empty($kalender->hs_summary) ? $studentsCount * 1 : 0;
        $target = $target_pagi + $target_sore;

        $summary = [
            'murobbi_id'     => $this->murobbiId,
            'kalender_id'    => $this->kalenderId,
            'tahun_ajaran'   => $tahunAjaran,
            'semester'       => $semester,
            'tanggal'        => $kalender->tanggal,
            'target'         => $target,
            'target_pagi'    => $target_pagi,
            'target_sore'    => $target_sore,
            'terisi'         => $journals->count(),
            'terisi_pagi'    => $journals->where('waktu', 'pagi')->count(),
            'terisi_sore'    => $journals->where('waktu', 'sore')->count(),
            'input_summary'  => $journals->pluck('kehadiran', 'student_id')->toArray(),
            'completed'      => ($target == $journals->count()),
            'completed_pagi' => ($target_pagi == $journals->where('waktu', 'pagi')->count()),
            'completed_sore' => ($target_sore == $journals->where('waktu', 'sore')->count()),
            'hp_only'        => $kalender->is_hp_only ?? false,
        ];

        \App\Models\Tahfidz\JournalSummary::updateOrCreate(
            [
                'murobbi_id'  => $this->murobbiId,
                'kalender_id' => $this->kalenderId,
                'tahun_ajaran' => $tahunAjaran,
                'semester'     => $semester,
            ],
            $summary
        );
    }
}
