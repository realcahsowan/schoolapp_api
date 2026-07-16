<?php

namespace App\Console\Commands;

use App\Jobs\Tahfidz\GenerateRaporPdfJob;
use App\Models\Employee;
use App\Models\Tahfidz\Configuration;
use App\Models\Tahfidz\Rapor;
use App\Settings\GeneralSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class UpdateRaporData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tahfidz:update-rapor-data {school_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update lokasi, tanggal, dan kepala tahfidz pada rapor tahfidz untuk sekolah tertentu.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $schoolId = (int) $this->argument('school_id');
        $settings = app(GeneralSettings::class);
        $tahunAjaran = $settings->tahun_ajaran;
        $semester = $settings->semester;

        $this->info("Updating rapor data for school_id {$schoolId}, TA {$tahunAjaran}, semester {$semester}...");

        $configurations = Configuration::query()
            ->where('school_id', $schoolId)
            ->whereIn('name', ['lokasiRapor', 'tanggalRapor'])
            ->get()
            ->keyBy('name');

        $lokasiRapor = $this->resolvePayloadValue($configurations->get('lokasiRapor')?->payload);
        $tanggalRapor = $this->resolvePayloadValue($configurations->get('tanggalRapor')?->payload);

        if (! filled($lokasiRapor)) {
            $this->warn("Configuration lokasiRapor not found for school_id {$schoolId}.");
        }

        if (! filled($tanggalRapor)) {
            $this->warn("Configuration tanggalRapor not found for school_id {$schoolId}.");
        }

        $kepalaTahfidzPutra = Employee::query()
            ->whereHas('positions', function ($query) use ($schoolId) {
                $query->where('active', true)
                    ->where('school_id', $schoolId)
                    ->where('nama', 'kepala-tahfidz-putra');
            })
            ->orderBy('id')
            ->first();

        $kepalaTahfidzPutri = Employee::query()
            ->whereHas('positions', function ($query) use ($schoolId) {
                $query->where('active', true)
                    ->where('school_id', $schoolId)
                    ->where('nama', 'kepala-tahfidz-putri');
            })
            ->orderBy('id')
            ->first();

        if (! $kepalaTahfidzPutra) {
            $this->warn("No active kepala tahfidz putra found for school_id {$schoolId}.");
        }

        if (! $kepalaTahfidzPutri) {
            $this->warn("No active kepala tahfidz putri found for school_id {$schoolId}.");
        }

        $query = Rapor::query()
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->whereHas('student.classroom', function ($query) use ($schoolId) {
                $query->where('school_id', $schoolId);
            })
            ->with(['student.classroom']);

        $total = (clone $query)->count();

        if ($total === 0) {
            $this->warn('No rapor records found for the given school and current tahun_ajaran/semester.');

            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $updated = 0;
        $skipped = 0;
        $queued = 0;

        $query->chunkById(100, function ($rapors) use (
            $lokasiRapor,
            $tanggalRapor,
            $kepalaTahfidzPutra,
            $kepalaTahfidzPutri,
            &$updated,
            &$skipped,
            &$queued,
            $bar
        ) {
            foreach ($rapors as $rapor) {
                $changes = [];
                $gender = strtolower((string) ($rapor->student?->gender ?? ''));
                $kepalaTahfidz = match ($gender) {
                    'male' => $kepalaTahfidzPutra,
                    'female' => $kepalaTahfidzPutri,
                    default => null,
                };

                if (filled($lokasiRapor)) {
                    $changes['lokasi'] = $lokasiRapor;
                }

                if (filled($tanggalRapor)) {
                    $changes['tanggal'] = $tanggalRapor;
                }

                if ($kepalaTahfidz) {
                    $changes['kepala_tahfidz_employee_id'] = $kepalaTahfidz->id;
                    $changes['kepala_tahfidz_name'] = $kepalaTahfidz->nama;
                } else {
                    $this->warn("Skipping kepala tahfidz update for rapor ID {$rapor->id}: unsupported or missing student gender '{$gender}'.");
                }

                if (! empty($changes)) {
                    $rapor->forceFill($changes)->save();
                    $updated++;
                } else {
                    $skipped++;
                }

                GenerateRaporPdfJob::dispatch($rapor->id)/*->onQueue('rapor-pdf')*/;
                $queued++;
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->info("Done. Updated: {$updated}, skipped: {$skipped}, queued PDF jobs: {$queued}.");

        return Command::SUCCESS;
    }

    /**
     * Normalize configuration payloads to a scalar value when the stored payload is an array.
     *
     * @param mixed $payload
     */
    protected function resolvePayloadValue(mixed $payload): mixed
    {
        if (is_array($payload)) {
            return Arr::first($payload);
        }

        return $payload;
    }
}
