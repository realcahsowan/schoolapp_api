<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Tahfidz\Journal;

class MigrasiMutabaahToJournal extends Command
{
    protected $signature = 'migrasi:mutabaah-to-journal';
    protected $description = 'Migrasi data dari mutabaah_harians ke tahfidz__journals';

    public function handle()
    {
        $mutabaahData = DB::connection('tahfidz')->table('mutabaah_harians')->get();

        // Ambil semua ID valid di awal
        $validMurobbiIds = DB::table('tahfidz__murobbis')->pluck('id')->toArray();
        $validStudentIds = DB::table('students')->pluck('id')->toArray();
        $validKalenderIds = DB::table('tahfidz__kalender_hafalans')->pluck('id')->toArray();

        DB::beginTransaction();
        try {
            $bar = $this->output->createProgressBar(count($mutabaahData));
            $bar->start();

            foreach ($mutabaahData as $row) {
                $validMurobbi = in_array($row->murobbi_id, $validMurobbiIds);
                $validStudent = in_array($row->student_id, $validStudentIds);
                $validKalender = true;
                if (!empty($row->kalender_id)) {
                    $validKalender = in_array($row->kalender_id, $validKalenderIds);
                }

                if (!$validMurobbi || !$validStudent || !$validKalender) {
                    $this->warn("Lewati data ID {$row->id}: murobbi_id, student_id, atau kalender_id tidak valid.");
                    $bar->advance();
                    continue;
                }

                Journal::create([
                    'murobbi_id' => $row->murobbi_id,
                    'student_id' => $row->student_id,
                    'kalender_id' => $row->kalender_id ?? null,
                    'tahun_ajaran' => $row->tahun_ajaran ?? '',
                    'semester' => $row->semester ?? '',
                    'year' => $row->year ?? null,
                    'month' => $row->month ?? null,
                    'week' => $row->week ?? null,
                    'tanggal' => $row->tanggal ?? null,
                    'kehadiran' => $row->kehadiran ?? '',
                    'is_terlambat' => $row->is_terlambat ?? false,
                    'jenis_izin' => $row->jenis_izin ?? null,
                    'keterangan_izin_lainnya' => $row->keterangan_izin_lainnya ?? null,
                    'keterangan_sakit' => $row->keterangan_sakit ?? null,
                    'status' => $row->status ?? '',
                    'detail_capaian' => $row->detail_capaian ? json_decode($row->detail_capaian, true) : null,
                    'detail_extra' => $row->detail_extra ? json_decode($row->detail_extra, true) : null,
                    'detail_khusus' => $row->detail_khusus ? json_decode($row->detail_khusus, true) : null,
                    'pelanggaran' => $row->pelanggaran ? json_decode($row->pelanggaran, true) : null,
                    'score_detail' => $row->score_detail ? json_decode($row->score_detail, true) : null,
                    'is_melanggar' => $row->is_melanggar ?? false,
                    'is_hp_only' => $row->hp_only ?? false,
                    'score' => $row->score ?? null,
                    'waktu' => $row->waktu ?? null,
                    'catatan' => $row->catatan ?? null,
                ]);
                $bar->advance();
            }

            $bar->finish();
            DB::commit();
            $this->info('\nMigrasi selesai!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Migrasi gagal: ' . $e->getMessage());
        }
    }
}
