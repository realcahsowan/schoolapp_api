<?php

namespace App\Console\Commands;

use App\Models\Murobbi;
use App\Scopes\CurrentYearSemesterScope;
use App\Settings\GeneralSettings;
use Illuminate\Console\Command;

class FixRaporMurobbiEmployeeId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tahfidz:fix-rapor-murobbi-employee-id';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perbaiki field murobbi_employee_id pada rapor tahfidz berdasarkan murobbi aktif di tahun ajaran dan semester sekarang.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $settings = app(GeneralSettings::class);
        $tahunAjaran = $settings->tahun_ajaran;
        $semester = $settings->semester;

        $this->info("Menyelaraskan murobbi_employee_id untuk TA {$tahunAjaran}, semester {$semester}...");

        $murobbis = Murobbi::query()
            ->withoutGlobalScope(CurrentYearSemesterScope::class)
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->with([
                'students' => function ($query) use ($tahunAjaran, $semester) {
                    $query->wherePivot('is_active', true)
                        ->with([
                            'rapors' => function ($raporQuery) use ($tahunAjaran, $semester) {
                                $raporQuery->where('tahun_ajaran', $tahunAjaran)
                                    ->where('semester', $semester);
                            },
                        ]);
                },
            ])
            ->get();

        if ($murobbis->isEmpty()) {
            $this->warn('Tidak ada data murobbi untuk tahun ajaran dan semester aktif.');

            return Command::SUCCESS;
        }

        $totalRapors = $murobbis->sum(function ($murobbi) {
            return $murobbi->students->sum(function ($student) {
                return $student->rapors->count();
            });
        });

        if ($totalRapors === 0) {
            $this->warn('Tidak ada rapor yang terkait dengan murobbi aktif.');

            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($totalRapors);
        $bar->start();

        $updated = 0;
        $skipped = 0;

        foreach ($murobbis as $murobbi) {
            if (! filled($murobbi->employee_id)) {
                $this->warn("Murobbi ID {$murobbi->id} tidak memiliki employee_id, dilewati.");

                foreach ($murobbi->students as $student) {
                    $skipped += $student->rapors->count();
                    foreach ($student->rapors as $rapor) {
                        $bar->advance();
                    }
                }

                continue;
            }

            foreach ($murobbi->students as $student) {
                foreach ($student->rapors as $rapor) {
                    if ($rapor->tahun_ajaran !== $tahunAjaran || (int) $rapor->semester !== (int) $semester) {
                        $skipped++;
                        $bar->advance();
                        continue;
                    }

                    if ((int) $rapor->murobbi_employee_id === (int) $murobbi->employee_id) {
                        $skipped++;
                        $bar->advance();
                        continue;
                    }

                    $rapor->forceFill([
                        'murobbi_employee_id' => $murobbi->employee_id,
                    ])->save();

                    $updated++;
                    $bar->advance();
                }
            }
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Selesai. Updated: {$updated}, skipped: {$skipped}.");

        return Command::SUCCESS;
    }
}
