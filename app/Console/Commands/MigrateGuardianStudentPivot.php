<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateGuardianStudentPivot extends Command
{
    protected $signature = 'pivot:migrate-guardian-student';
    protected $description = 'Migrasi data pivot ortu_student dari db madrasah ke db utama (guardian_student)';

    public function handle()
    {
        // Koneksi ke database madrasah
        $madrasahPivot = DB::connection('madrasah')->table('ortu_student')->get();

        if ($madrasahPivot->isEmpty()) {
            $this->info('Tidak ada data di ortu_student (db madrasah).');
            return 0;
        }

        $guardianIds = DB::table('guardians')->pluck('id')->toArray();
        $studentIds = DB::table('students')->pluck('id')->toArray();

        $insertData = [];
        foreach ($madrasahPivot as $row) {
            if (in_array($row->ortu_id, $guardianIds) && in_array($row->student_id, $studentIds)) {
                $insertData[] = [
                    'guardian_id' => $row->ortu_id,
                    'student_id'  => $row->student_id,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }
        }

        if (empty($insertData)) {
            $this->info('Tidak ada data guardian yang valid untuk dipindahkan.');
            return 0;
        }

        DB::table('guardian_student')->insert($insertData);

        $this->info('Migrasi selesai. Total data dipindahkan: ' . count($insertData));
        return 0;
    }
}