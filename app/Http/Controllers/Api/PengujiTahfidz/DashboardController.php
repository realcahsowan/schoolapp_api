<?php

namespace App\Http\Controllers\Api\PengujiTahfidz;

use App\Http\Controllers\Controller;
use App\Models\Tahfidz\Penguji;
use App\Models\Tahfidz\Examination;
use App\Settings\GeneralSettings;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user();
        $settings = app(GeneralSettings::class);
        $tahunAjaran = $settings->tahun_ajaran;
        $semester = $settings->semester;

        $pengujis = Penguji::with(['students.rapors' => function ($q) use ($tahunAjaran, $semester) {
            $q->where('tahun_ajaran', $tahunAjaran)
                ->where('semester', $semester)
                ->latest('id');
        }])
            ->currentYearSemester()
            ->where('employee_id', $user->employee_id)
            ->get();

        $students = $pengujis->flatMap(function ($penguji) {
            return $penguji->students ?? collect();
        })->unique('id')->values();
        $totalStudents = $students->count();
        $totalJuzTarget = $students->pluck('rapors')->map(fn ($g) => $g->sum('total_juz_pas'))->sum();

        $totalJuzAchieved = Examination::whereIn('student_id', $students->pluck('id'))
            ->whereIn('penguji_id', $pengujis->pluck('id'))
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->where('is_locked', true)
            ->count();

        return response()->json([
            'widgets' => [
                'total_students' => $totalStudents,
                'total_juz' => $totalJuzTarget,
                'total_achievement' => $totalJuzAchieved,
            ],
        ]);
    }
}
