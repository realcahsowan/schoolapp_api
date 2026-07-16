<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AddDormitoryStudentFromMurobbi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:add-dormitory-student-from-murobbi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tambah record dormitory_student berdasarkan relasi murobbi aktif dan dormitory aktif karyawan.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $studentClass = \App\Models\Student::class;
        $general = app(\App\Settings\GeneralSettings::class);
        $tahunAjaran = $general->tahun_ajaran;
        $semester = $general->semester;

        $students = $studentClass::with(['murobbis.employee.dormitories'])->get();
        $updated = 0;
        $created = 0;
        $skipped = 0;
        $this->output->progressStart($students->count());

        foreach ($students as $student) {
            $activeMurobbi = $student->murobbis->firstWhere('pivot.is_active', true);
            if (! $activeMurobbi) {
                $skipped++;
                $this->output->progressAdvance();

                continue;
            }
            $employee = $activeMurobbi->employee;
            if (! $employee) {
                $skipped++;
                $this->output->progressAdvance();

                continue;
            }
            $activeDorm = $employee->active_dormitory;
            if (! $activeDorm) {
                $skipped++;
                $this->output->progressAdvance();

                continue;
            }
            // Cek apakah sudah ada di pivot untuk tahun ajaran/semester sekarang
            $exists = $student->dormitories()->wherePivot('tahun_ajaran', $tahunAjaran)->wherePivot('semester', $semester)->wherePivot('dormitory_id', $activeDorm->id)->exists();
            if ($exists) {
                $updated++;
                $this->output->progressAdvance();

                continue;
            }
            // Tambahkan to pivot dengan value room dari pivot employee
            $room = $activeDorm->pivot->room ?? null;
            $student->dormitories()->attach($activeDorm->id, [
                'room' => $room,
                'is_active' => true,
                'tahun_ajaran' => $tahunAjaran,
                'semester' => $semester,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $created++;
            $this->output->progressAdvance();
        }
        $this->output->progressFinish();
        $this->info("--- Selesai: {$created} dimasukkan, {$updated} sudah ada, {$skipped} dilewati ---");
    }
}
