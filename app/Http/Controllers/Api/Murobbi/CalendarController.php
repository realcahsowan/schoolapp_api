<?php

namespace App\Http\Controllers\Api\Murobbi;

use App\Http\Controllers\Controller;
use App\Models\Tahfidz\KalenderHafalan;
use App\Models\Tahfidz\Journal;
use App\Settings\GeneralSettings;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    private function getMurobbiData(): array
    {
        $employee = auth()->user()->employee;
        $settings = app(GeneralSettings::class);

        $murobbis = $employee->murobbis()
            ->where('tahun_ajaran', $settings->tahun_ajaran)
            ->where('semester', $settings->semester)
            ->get();

        return [
            'murobbis' => $murobbis,
            'murobbi_ids' => $murobbis->pluck('id')->toArray(),
            'school_ids' => $murobbis->pluck('school_id')->unique()->values()->toArray(),
        ];
    }

    public function index(Request $request)
    {
        $data = $this->getMurobbiData();
        $murobbis = $data['murobbis'];
        $murobbiIds = $data['murobbi_ids'];
        $schoolIds = $data['school_ids'];
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $kalenders = KalenderHafalan::whereIn('school_id', $schoolIds)
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->orderBy('tanggal')
            ->get()
            ->map(function ($k) use ($murobbiIds, $murobbis) {
                $journals = Journal::where('kalender_id', $k->id)
                    ->whereIn('murobbi_id', $murobbiIds)
                    ->with('student')
                    ->get();

                $murobbisForSchool = $murobbis->where('school_id', $k->school_id);

                return [
                    'id' => $k->id,
                    'tanggal' => $k->tanggal->format('Y-m-d'),
                    'school_id' => $k->school_id,
                    'is_hp_only' => $k->is_hp_only,
                    'is_weekly_examination' => $k->is_weekly_examination,
                    'is_disabled' => $k->is_disabled,
                    'hp_summary' => $k->hp_summary,
                    'hs_summary' => $k->hs_summary,
                    'journals' => $journals->map(fn($j) => [
                        'id' => $j->id,
                        'student_id' => $j->student_id,
                        'student_nama' => $j->student->nama,
                        'waktu' => $j->waktu,
                        'status' => $j->status,
                        'kehadiran' => $j->kehadiran,
                    ]),
                ];
            });

        return response()->json([
            'month' => $month,
            'year' => $year,
            'kalenders' => $kalenders,
        ]);
    }

    public function show($tanggal)
    {
        $data = $this->getMurobbiData();
        $murobbis = $data['murobbis'];
        $murobbiIds = $data['murobbi_ids'];
        $schoolIds = $data['school_ids'];

        $kalenders = KalenderHafalan::where('tanggal', $tanggal)
            ->whereIn('school_id', $schoolIds)
            ->with('school')
            ->get();

        if ($kalenders->isEmpty()) {
            return response()->json(['message' => 'Tidak ada data untuk tanggal ini'], 404);
        }

        $result = $kalenders->map(function ($kalender) use ($murobbiIds) {
            $journals = Journal::where('kalender_id', $kalender->id)
                ->whereIn('murobbi_id', $murobbiIds)
                ->with('student')
                ->get()
                ->groupBy('waktu');

            return [
                'kalender' => [
                    'id' => $kalender->id,
                    'tanggal' => $kalender->tanggal->format('Y-m-d'),
                    'is_hp_only' => $kalender->is_hp_only,
                    'is_weekly_examination' => $kalender->is_weekly_examination,
                    'is_disabled' => $kalender->is_disabled,
                    'hp_summary' => $kalender->hp_summary,
                    'hs_summary' => $kalender->hs_summary,
                    'school' => $kalender->school ? ['id' => $kalender->school->id, 'nama' => $kalender->school->nama] : null,
                ],
                'pagi' => isset($journals['pagi']) ? $journals['pagi']->map(fn($j) => [
                    'id' => $j->id,
                    'student_id' => $j->student_id,
                    'student_nama' => $j->student->nama,
                    'kehadiran' => $j->kehadiran,
                    'status' => $j->status,
                ]) : [],
                'sore' => isset($journals['sore']) ? $journals['sore']->map(fn($j) => [
                    'id' => $j->id,
                    'student_id' => $j->student_id,
                    'student_nama' => $j->student->nama,
                    'kehadiran' => $j->kehadiran,
                    'status' => $j->status,
                ]) : [],
            ];
        });

        return response()->json([
            'tanggal' => $tanggal,
            'data' => $result,
        ]);
    }
}
