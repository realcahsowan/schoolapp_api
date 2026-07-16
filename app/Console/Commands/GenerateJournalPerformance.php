<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Murobbi;
use App\Services\GenerateJournalPerformanceService; // added

class GenerateJournalPerformance extends Command
{
    protected $signature = 'tahfidz:generate-performance
                            {--murobbi_id= : ID murobbi}
                            {--periode= : Periode type (weekly, monthly, semesterly)}
                            {--number= : nomor untuk periode (week number atau month number)}';

    protected $description = 'Generate or update JournalPerformance for a murobbi for a given periode (weekly, monthly, semesterly).';

    public function handle(GenerateJournalPerformanceService $service): int
    {
        $murobbiId = $this->option('murobbi_id') ?: $this->ask('Murobbi ID (numeric)');
        $murobbiId = is_numeric($murobbiId) ? (int) $murobbiId : null;
        if (! $murobbiId) {
            $this->error('Invalid murobbi id.');
            return 1;
        }

        if ($this->option('periode')) {
            $periode = strtolower($this->option('periode'));
        } else {
            $periode = $this->choice('Periode type', ['weekly', 'monthly', 'semesterly'], 0);
        }

        $number = $this->option('number');

        // validate existence early (service will also fail if missing)
        $murobbi = Murobbi::find($murobbiId);
        if (! $murobbi) {
            $this->error("Murobbi with id {$murobbiId} not found.");
            return 1;
        }

        // periode-specific prompts/validation (keep UX same as before)
        if ($periode === 'weekly') {
            if (! $number) {
                $number = $this->ask('Week number (ISO week number or calendar week)');
            }
            if (! is_numeric($number)) {
                $this->error('Invalid week number.');
                return 1;
            }
            $number = (int) $number;
        } elseif ($periode === 'monthly') {
            if (! $number) {
                $number = $this->ask('Month number (1-12)');
            }
            if (! is_numeric($number) || (int)$number < 1 || (int)$number > 12) {
                $this->error('Invalid month number.');
                return 1;
            }
            $number = (int) $number;
        } else {
            // semesterly: number ignored
            $number = null;
        }

        // delegate heavy logic to service (synchronous)
        try {
            $result = $service->execute($murobbiId, $periode, $number);
        } catch (\Throwable $e) {
            $this->error('Error generating performance: ' . $e->getMessage());
            return 1;
        }

        if (isset($result['status']) && $result['status'] === 'empty') {
            $this->warn($result['message'] ?? 'No KalenderHafalan entries found for the provided periode/scope.');
            return 0;
        }

        if (isset($result['status']) && $result['status'] === 'ok') {
            $perfId = $result['performance_id'] ?? 'unknown';
            $this->info("JournalPerformance saved (id: {$perfId}).");
            if (isset($result['data']) && is_array($result['data'])) {
                $d = $result['data'];
                $this->line("jenis_periode: {$d['jenis_periode']} | angka_periode: " . ($d['angka_periode'] ?? 'null') . " | awal: {$d['awal']} | akhir: {$d['akhir']} | total_hari: {$d['total_hari']} | total_hp_only: {$d['total_hp_only']}");
                $this->line("target: {$d['target']} | realisasi: {$d['realisasi']}");
            }
            return 0;
        }

        $this->error('Unexpected result from service.');
        return 1;
    }
}