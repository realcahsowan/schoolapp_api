<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Tahfidz\Configuration;
use App\Models\Murobbi;
use App\Models\Tahfidz\KalenderHafalan;

class MigrasiTahfidz extends Command
{
    protected $signature = 'migrasi:tahfidz';
    protected $description = 'Migrasi data tahfidz: configuration, murobbi, kalender hafalan';

    public function handle()
    {
        DB::transaction(function () {
            // Set Murobbi model incrementing to false and keyType to int for migration
            $murobbiModel = new \App\Models\Murobbi;
            $murobbiModel->incrementing = false;
            $murobbiModel->keyType = 'int';
            // Migrasi Configuration
            $this->info('Migrasi Configuration...');
            $oldConfigs = DB::connection('tahfidz')->table('configurations')->get();
            foreach ($oldConfigs as $config) {
                Configuration::updateOrCreate([
                    'name' => $config->name,
                    'school_id' => $config->school_id,
                ], [
                    'payload' => json_decode($config->payload, true),
                    'locked' => $config->locked,
                ]);
            }
            $this->info('Migrasi Configuration selesai.');

            // Migrasi Murobbi
            $this->info('Migrasi Murobbi...');
            $oldMurobbis = DB::connection('tahfidz')->table('murobbis')->get();
            $murobbiInserts = [];
            foreach ($oldMurobbis as $murobbi) {
                $murobbiInserts[] = [
                    'id' => $murobbi->id,
                    'employee_id' => $murobbi->employee_id,
                    'school_id' => $murobbi->school_id,
                    'nama' => $murobbi->nama,
                    'nama_pendek' => $murobbi->nama_pendek,
                    'gender' => $murobbi->gender,
                    'tahun_ajaran' => $murobbi->tahun_ajaran,
                    'semester' => $murobbi->semester,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if ($murobbiInserts) {
                DB::table('tahfidz__murobbis')->insert($murobbiInserts);
                $this->info('Migrasi Murobbi selesai. Total: ' . count($murobbiInserts));
            } else {
                $this->info('Tidak ada data murobbi yang valid untuk dimigrasi.');
            }

            // Migrasi pivot murobbi_student ke tahfidz__student_murobbi
            $this->info('Migrasi pivot murobbi_student...');
            $oldPivots = DB::connection('tahfidz')->table('murobbi_student')->get();
            $pivotInserts = [];
            $validStudentIds = \App\Models\Student::pluck('id')->toArray();
            $validMurobbiIds = \App\Models\Murobbi::pluck('id')->toArray();
            // Prefetch all achievements and map by student_id only
            $achievements = DB::connection('tahfidz')->table('achievements')->get();
            $achievementMap = [];
            foreach ($achievements as $a) {
                $achievementMap[$a->student_id] = $a;
            }
            foreach ($oldPivots as $pivot) {
                if (in_array($pivot->student_id, $validStudentIds) && in_array($pivot->murobbi_id, $validMurobbiIds)) {
                    $achievement = $achievementMap[$pivot->student_id] ?? null;
                    $pivotInserts[] = [
                        'student_id' => $pivot->student_id,
                        'murobbi_id' => $pivot->murobbi_id,
                        'category' => $achievement?->category ?? null,
                        'program' => $achievement?->program ?? null,
                        'is_active' => $pivot->active ?? false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            if ($pivotInserts) {
                DB::table('tahfidz__student_murobbi')->insert($pivotInserts);
                $this->info('Migrasi pivot murobbi_student selesai. Total: ' . count($pivotInserts));
            } else {
                $this->info('Tidak ada data pivot murobbi_student yang valid untuk dimigrasi.');
            }
            $this->info('Memulai migrasi data tahfidz...');

            // Migrasi Kalender Hafalan
            $this->info('Migrasi Kalender Hafalan...');
            $oldKalenders = DB::connection('tahfidz')->table('kalender_hafalans')->get();
            $kalenderInserts = [];
            foreach ($oldKalenders as $kalender) {
                $kalenderInserts[] = [
                    'id' => $kalender->id,
                    'school_id' => $kalender->school_id,
                    'tahun_ajaran' => $kalender->tahun_ajaran,
                    'semester' => $kalender->semester,
                    'year' => $kalender->year,
                    'month' => $kalender->month,
                    'week' => $kalender->week,
                    'day' => $kalender->day,
                    'tanggal' => $kalender->tanggal,
                    'hp_summary' => $kalender->hp_summary,
                    'hs_summary' => $kalender->hs_summary,
                    'is_hp_only' => $kalender->hp_only,
                    'is_weekly_examination' => $kalender->weekly_examination,
                    'is_disabled' => $kalender->disabled,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if ($kalenderInserts) {
                DB::table('tahfidz__kalender_hafalans')->insert($kalenderInserts);
                $this->info('Migrasi Kalender Hafalan selesai. Total: ' . count($kalenderInserts));
            } else {
                $this->info('Tidak ada data kalender hafalan yang valid untuk dimigrasi.');
            }

            $this->info('Migrasi data tahfidz selesai!');
        });
    }
}
