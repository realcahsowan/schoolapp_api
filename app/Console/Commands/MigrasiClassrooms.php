<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrasiClassrooms extends Command
{
    protected $signature = 'migrasi:classrooms';
    protected $description = 'Migrasi data classrooms dari table grades';

    public function handle()
    {
        DB::transaction(function () {
            $this->info('Memulai migrasi data classrooms dari table grades...');
            $grades = DB::connection('madrasah')->table('grades')->get();
            $classroomInserts = [];
            foreach ($grades as $grade) {
                $classroomInserts[] = [
                    'id' => $grade->id, // gunakan id asli dari grades
                    'alias' => $grade->alias ?? null,
                    'tahun_ajaran' => $grade->tahun_ajaran ?? null,
                    'nama' => $grade->nama ?? '',
                    'level' => $grade->level ?? null,
                    'rombel' => $grade->rombel ?? null,
                    'jurusan_id' => $grade->jurusan_id ?? null,
                    'tingkat_id' => $grade->tingkat_id ?? null,
                    'kurikulum_id' => $grade->kurikulum_id ?? null,
                    'history' => $grade->history ?? null,
                    'is_promoted' => $grade->is_promoted ?? false,
                    'employee_id' => $grade->employee_id ?? null,
                    'school_id' => $grade->school_id ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('classrooms')->insert($classroomInserts);
            $this->info("Migrasi selesai. Total: " . count($classroomInserts) . " classrooms.");

            // Migrasi data pivot classroom_student dari grade_student

            $this->info('Memulai migrasi data pivot classroom_student dari grade_student...');
            $gradeStudents = DB::connection('madrasah')->table('grade_student')->get();
            $pivotInserts = [];
            $validStudentIds = DB::table('students')->pluck('id')->toArray();
            $validClassroomIds = DB::table('classrooms')->pluck('id')->toArray();
            $bar = $this->output->createProgressBar(count($gradeStudents));
            $bar->start();
            foreach ($gradeStudents as $pivot) {
                if (in_array($pivot->student_id, $validStudentIds) && in_array($pivot->grade_id, $validClassroomIds)) {
                    $isActive = $pivot->active ?? true;
                    $pivotInserts[] = [
                        'classroom_id' => $pivot->grade_id,
                        'student_id' => $pivot->student_id,
                        'is_active' => $isActive,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    // Jika pivot is_active, update student.classroom_id
                    if ($isActive) {
                        DB::table('students')->where('id', $pivot->student_id)->update(['classroom_id' => $pivot->grade_id]);
                    }
                }
                $bar->advance();
            }
            $bar->finish();
            $this->line('');
            if ($pivotInserts) {
                DB::table('classroom_student')->insert($pivotInserts);
                $this->info('Migrasi pivot classroom_student selesai. Total: ' . count($pivotInserts));
            } else {
                $this->info('Tidak ada data pivot classroom_student yang valid untuk dimigrasi.');
            }
        });
    }
}
