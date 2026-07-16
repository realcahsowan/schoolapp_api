<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\Tahfidz\Rapor;
use App\Settings\GeneralSettings;

class GenerateRaporForAllStudents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-rapor-for-all-students';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Rapor for all students with current year, semester, category, and program.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $settings = app(GeneralSettings::class);
        $tahunAjaran = $settings->tahun_ajaran;
        $semester = $settings->semester;

        $students = Student::with(['murobbis' => function($q) {
            $q->wherePivot('is_active', true);
        }])
        ->whereNotNull('classroom_id')
        ->get();

        $count = 0;
        $total = $students->count();
        $this->output->progressStart($total);
        foreach ($students as $student) {
            $murobbiPivot = $student->murobbis->first()?->pivot;
            $category = $murobbiPivot->category ?? null;
            $program = $murobbiPivot->program ?? null;

            // Hindari duplikasi rapor untuk tahun ajaran & semester yang sama
            $exists = Rapor::where('student_id', $student->id)
                ->where('tahun_ajaran', $tahunAjaran)
                ->where('semester', $semester)
                ->where('category', $category)
                ->where('program', $program)
                ->exists();
            if ($exists) {
                $this->output->progressAdvance();
                continue;
            }

            Rapor::create([
                'student_id' => $student->id,
                'tahun_ajaran' => $tahunAjaran,
                'semester' => $semester,
                'category' => $category,
                'program' => $program,
            ]);
            $count++;
            $this->output->progressAdvance();
        }
        $this->output->progressFinish();
        $this->info("Generated $count rapor records.");
    }
}
