<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigratePeriodicsToPenilaianPeriodik extends Command
{
    protected $signature = 'tahfidz:migrate-periodics';
    protected $description = 'Memindahkan data dari db tahfidz.table periodics ke db utama table tahfidz__penilaian_periodik';

    public function handle()
    {
        // Kosongkan tabel tujuan sebelum migrasi
        DB::table('tahfidz__penilaian_periodik')->truncate();
        // Koneksi ke database tahfidz
        $tahfidzData = DB::connection('tahfidz')->table('periodics')->get();
        // Ambil semua student_id yang valid dari tabel students
        $validStudentIds = DB::table('students')->pluck('id')->toArray();
        // Ambil semua murobbi_id yang valid dari tabel tahfidz__murobbis
        $validMurobbiIds = DB::table('tahfidz__murobbis')->pluck('id')->toArray();

        $this->info('Memulai migrasi data...');
        $count = 0;
        $batchSize = 500;
        $insertData = [];
        foreach ($tahfidzData as $row) {
            // Skip jika student_id atau murobbi_id tidak valid
            if (!in_array($row->student_id, $validStudentIds)) {
                continue;
            }
            if (!in_array($row->murobbi_id, $validMurobbiIds)) {
                continue;
            }
            // Map data sesuai kebutuhan
            $insertData[] = [
                'student_id' => $row->student_id,
                'murobbi_id' => $row->murobbi_id,
                'tahun_ajaran' => $row->tahun_ajaran ?? null,
                'semester' => $row->semester ?? null,
                'tanggal' => $row->tanggal ?? null,
                'kehadiran' => 'hadir',
                'jenis_izin' => $row->jenis_izin ?? null,
                'keterangan_izin_lainnya' => $row->keterangan_izin_lainnya ?? null,
                'target' => $row->target ?? null,
                'juz_map' => $row->juz_map ?? null,
                'pages_map' => $row->pages_map ?? null,
                'detail' => $row->detail ?? null,
                'score' => $row->score ?? null,
                'created_at' => $row->created_at ?? now(),
                'updated_at' => $row->updated_at ?? now(),
            ];
            $count++;
            // If batch size reached, insert and reset
            if (count($insertData) === $batchSize) {
                DB::beginTransaction();
                try {
                    DB::table('tahfidz__penilaian_periodik')->insert($insertData);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->error('Migrasi gagal pada batch: ' . $e->getMessage());
                    return;
                }
                $insertData = [];
            }
        }
        // Insert remaining data
        if (count($insertData) > 0) {
            DB::beginTransaction();
            try {
                DB::table('tahfidz__penilaian_periodik')->insert($insertData);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error('Migrasi gagal pada batch akhir: ' . $e->getMessage());
                return;
            }
        }
        $this->info("Migrasi selesai. Total data dipindahkan: $count");
    }
}
