<?php

namespace App\Services;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Murobbi;
use App\Models\Tahfidz\KalenderHafalan;
use App\Models\Tahfidz\JournalSummary;
use App\Models\Tahfidz\JournalPerformance;
use App\Settings\GeneralSettings;

class GenerateJournalPerformanceService
{
    public function execute(int $murobbiId, string $periode, ?int $number = null): array
    {
        $murobbi = Murobbi::withCount('students')->findOrFail($murobbiId);

        $tahunAjaran = $murobbi->tahun_ajaran ?? app(GeneralSettings::class)?->tahun_ajaran;
        $semester = $murobbi->semester ?? app(GeneralSettings::class)?->semester;

        $kalQuery = KalenderHafalan::query()
            ->where(function ($q) {
                $q->whereNull('is_weekly_examination')->orWhere('is_weekly_examination', '!=', 1);
            })
            ->when($murobbi->school_id, fn($q) => $q->where('school_id', $murobbi->school_id))
            ->when($tahunAjaran, fn($q) => $q->where('tahun_ajaran', $tahunAjaran))
            ->when($semester, fn($q) => $q->where('semester', $semester));

        if ($periode === 'weekly') {
            if (Schema::hasColumn((new KalenderHafalan())->getTable(), 'week')) {
                $kalQuery->where('week', $number);
            } else {
                $kalQuery->whereRaw('WEEK(`tanggal`, 1) = ?', [$number]);
            }
        } elseif ($periode === 'monthly') {
            if (Schema::hasColumn((new KalenderHafalan())->getTable(), 'month')) {
                $kalQuery->where('month', $number);
            } else {
                $kalQuery->whereMonth('tanggal', $number);
            }
        }

        $kalenders = $kalQuery->orderBy('tanggal')->get();

        // \Log::info($kalQuery->toSql());
        \Log::info($kalenders->count());
        if ($kalenders->isEmpty()) {
            return ['status' => 'empty', 'message' => 'No KalenderHafalan entries found.'];
        }

        $awal = $kalenders->first()->tanggal ? Carbon::parse($kalenders->first()->tanggal)->toDateString() : null;
        $akhir = $kalenders->last()->tanggal ? Carbon::parse($kalenders->last()->tanggal)->toDateString() : null;
        $totalHari = $kalenders->count();
        $totalHpOnly = $kalenders->where('is_hp_only', true)->count();
        $kalenderIds = $kalenders->pluck('id')->toArray();

        $aggregate = JournalSummary::query()
            ->where('murobbi_id', $murobbiId)
            ->whereIn('kalender_id', $kalenderIds)
            ->select([
                DB::raw('COALESCE(SUM(COALESCE(target,0)),0) as sum_target'),
                DB::raw('COALESCE(SUM(COALESCE(terisi,0)),0) as sum_terisi'),
            ])->first();

        // Calculate the number of students (assumes students is a relationship)
        // $jumlahSiswa = $murobbi->students->count();
        $jumlahSiswa = $murobbi->students_count;

        // Adjust target calculation as requested
        $target = ($totalHari * $jumlahSiswa * 2) - ($totalHpOnly * $jumlahSiswa);
        $realisasi = (int) ($aggregate->sum_terisi ?? 0);

        $data = [
            'murobbi_id' => $murobbiId,
            'tahun_ajaran' => $tahunAjaran,
            'semester' => $semester,
            'jenis_periode' => $periode,
            'angka_periode' => is_numeric($number) ? (int) $number : null,
            'awal' => $awal,
            'akhir' => $akhir,
            'total_hari' => $totalHari,
            'total_hp_only' => $totalHpOnly,
            'target' => $target,
            'realisasi' => $realisasi,
        ];

        $performance = JournalPerformance::updateOrCreate(
            [
                'murobbi_id' => $murobbiId,
                'tahun_ajaran' => $tahunAjaran,
                'semester' => $semester,
                'jenis_periode' => $periode,
                'angka_periode' => is_numeric($number) ? (int) $number : null,
            ],
            $data
        );

        \Log::info($performance);

        return [
            'status' => 'ok',
            'performance_id' => $performance->id,
            'data' => $data,
        ];
    }
}
