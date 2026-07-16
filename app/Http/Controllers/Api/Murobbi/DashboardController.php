<?php

namespace App\Http\Controllers\Api\Murobbi;

use App\Http\Controllers\Controller;
use App\Models\StudentMurobbi;
use App\Models\Tahfidz\KalenderHafalan;
use App\Models\Tahfidz\Journal;
use App\Models\Tahfidz\PenilaianPeriodik;
use App\Settings\GeneralSettings;
use Illuminate\Http\Request;

class DashboardController extends Controller
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

    public function index()
    {
        $employee = auth()->user()->employee;

        if (!$employee) {
            return response()->json(['stats' => null]);
        }

        $data = $this->getMurobbiData();
        $murobbis = $data['murobbis'];
        $murobbiIds = $data['murobbi_ids'];
        $schoolIds = $data['school_ids'];

        if ($murobbis->isEmpty()) {
            return response()->json(['stats' => null]);
        }

        $today = now()->format('Y-m-d');

        $todayKalenders = KalenderHafalan::where('tanggal', $today)
            ->whereIn('school_id', $schoolIds)
            ->get();

        $todayStats = [
            'required' => 0,
            'filled' => 0,
            'percentage' => 0,
            'is_active' => false,
            'label' => now()->translatedFormat('l, d F Y'),
        ];

        foreach ($todayKalenders as $kalender) {
            if (!$kalender->is_disabled && !$kalender->is_weekly_examination) {
                $todayStats['is_active'] = true;
                $multiplier = $kalender->is_hp_only ? 1 : 2;
                $murobbiIdsForSchool = $murobbis->where('school_id', $kalender->school_id)->pluck('id');
                $studentCount = StudentMurobbi::whereIn('murobbi_id', $murobbiIdsForSchool)->distinct('student_id')->count();
                $todayStats['required'] += $studentCount * $multiplier;
                $todayStats['filled'] += Journal::whereIn('murobbi_id', $murobbiIdsForSchool)
                    ->where('kalender_id', $kalender->id)
                    ->count();
            }
        }

        $todayStats['percentage'] = $todayStats['required'] > 0
            ? round(($todayStats['filled'] / $todayStats['required']) * 100)
            : 0;

        $recentDates = KalenderHafalan::whereIn('school_id', $schoolIds)
            ->where('is_weekly_examination', false)
            ->where('is_disabled', false)
            ->where('tanggal', '<=', $today)
            ->select('tanggal')
            ->distinct()
            ->orderBy('tanggal', 'desc')
            ->take(5)
            ->pluck('tanggal');

        $recentKalenders = KalenderHafalan::whereIn('school_id', $schoolIds)
            ->whereIn('tanggal', $recentDates)
            ->where('is_weekly_examination', false)
            ->where('is_disabled', false)
            ->get()
            ->groupBy('tanggal');

        $recentStats = $recentKalenders->map(function ($kalenders, $tanggal) use ($murobbis) {
            $required = 0;
            $filled = 0;
            foreach ($kalenders as $kalender) {
                $multiplier = $kalender->is_hp_only ? 1 : 2;
                $murobbiIdsForSchool = $murobbis->where('school_id', $kalender->school_id)->pluck('id');
                $studentCount = StudentMurobbi::whereIn('murobbi_id', $murobbiIdsForSchool)->distinct('student_id')->count();
                $required += $studentCount * $multiplier;
                $filled += Journal::whereIn('murobbi_id', $murobbiIdsForSchool)
                    ->where('kalender_id', $kalender->id)
                    ->count();
            }
            $first = $kalenders->first();

            return [
                'tanggal' => $tanggal,
                'label' => $first->tanggal->translatedFormat('d M Y'),
                'is_hp_only' => $kalenders->every(fn($k) => $k->is_hp_only),
                'required' => $required,
                'filled' => $filled,
                'percentage' => $required > 0 ? round(($filled / $required) * 100) : 0,
            ];
        })->values();

        $stats = [
            'student_count' => StudentMurobbi::whereIn('murobbi_id', $murobbiIds)->distinct('student_id')->count(),
            'total_journals' => Journal::whereIn('murobbi_id', $murobbiIds)->count(),
            'today_journals' => Journal::whereIn('murobbi_id', $murobbiIds)->whereDate('tanggal', now())->count(),
            'average_score' => round(PenilaianPeriodik::whereIn('murobbi_id', $murobbiIds)->avg('score') ?? 0, 1),
            'upcoming_exams' => KalenderHafalan::whereIn('school_id', $schoolIds)
                ->where('is_weekly_examination', true)
                ->where('tanggal', '>=', now())
                ->orderBy('tanggal', 'asc')
                ->first()?->tanggal?->translatedFormat('d F Y'),
            'today_progress' => $todayStats,
            'recent_journal_stats' => $recentStats,
        ];

        return response()->json([
            'stats' => $stats,
        ]);
    }

    public function getJournalDetail($tanggal)
    {
        $data = $this->getMurobbiData();
        $murobbis = $data['murobbis'];
        $schoolIds = $data['school_ids'];

        $kalenders = KalenderHafalan::where('tanggal', $tanggal)
            ->whereIn('school_id', $schoolIds)
            ->get()
            ->keyBy('school_id');

        $students = collect();
        foreach ($murobbis as $murobbi) {
            $kalender = $kalenders->get($murobbi->school_id);
            $murobbiStudents = $murobbi->students()->get()->map(function ($student) use ($kalender, $murobbi) {
                $journals = collect();
                if ($kalender) {
                    $journals = Journal::where('student_id', $student->id)
                        ->where('kalender_id', $kalender->id)
                        ->where('murobbi_id', $murobbi->id)
                        ->get();
                }

                $has_pagi = $journals->contains('waktu', 'pagi');
                $has_sore = $journals->contains('waktu', 'sore');
                $is_complete = $kalender?->is_hp_only ? $has_pagi : ($has_pagi && $has_sore);

                return [
                    'id' => $student->id,
                    'nama' => $student->nama,
                    'has_pagi' => $has_pagi,
                    'has_sore' => $has_sore,
                    'is_complete' => $is_complete,
                ];
            });
            $students = $students->concat($murobbiStudents);
        }

        return response()->json([
            'tanggal' => $tanggal,
            'is_hp_only' => $kalenders->every(fn($k) => $k->is_hp_only),
            'students' => $students->values(),
        ]);
    }
}
