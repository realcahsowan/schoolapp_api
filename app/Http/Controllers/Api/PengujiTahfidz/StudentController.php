<?php

namespace App\Http\Controllers\Api\PengujiTahfidz;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Tahfidz\Penguji;
use App\Models\Tahfidz\Examination;
use App\Settings\GeneralSettings;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $pengujis = Penguji::currentYearSemester()
            ->where('employee_id', $user->employee_id)
            ->with(['students' => function ($query) {
                $query->with([
                    'classroom',
                    'rapor',
                    'pengujis',
                    'examinations' => function ($q) {
                        $q->where('tahun_ajaran', app(GeneralSettings::class)->tahun_ajaran)
                        ->where('semester', app(GeneralSettings::class)->semester);
                    }]);
            }])->get();

        $students = $pengujis->pluck('students')
            ->flatten(1)
            ->unique('id')
            ->values()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'nama' => $student->nama,
                    'nisn' => $student->nisn,
                    'classroom' => $student->classroom ? [
                        'id' => $student->classroom->id,
                        'nama' => $student->classroom->nama,
                        'level' => $student->classroom->level,
                    ] : null,
                    'rapor' => $student->rapor ? [
                        'total_juz_pas' => $student->rapor->total_juz_pas,
                        'pas_score' => $student->rapor->pas_score,
                        'pas_completed_juz' => $student->rapor->pas_completed_juz,
                    ] : null,
                    'examinations_count' => $student->examinations->count(),
                    'examinations_locked' => $student->examinations->where('is_locked', true)->count(),
                ];
            });

        return response()->json([
            'students' => $students,
        ]);
    }

    public function show($id)
    {
        $settings = app(GeneralSettings::class);
        $user = auth()->user();

        $student = Student::with([
            'classroom',
            'pengujis' => fn ($query) => $query->where('tahfidz__pengujis.tahun_ajaran', $settings->tahun_ajaran)
                ->where('tahfidz__pengujis.semester', $settings->semester),
            'rapor',
        ])->findOrFail($id);

        $penguji = $student->pengujis->where('employee_id', $user->employee_id)->first();

        if (!$penguji) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $examinations = Examination::where('student_id', $student->id)
            ->where('penguji_id', $penguji->id)
            ->with('penguji:id,nama')
            ->get()
            ->map(function ($exam) {
                return [
                    'id' => $exam->id,
                    'juz' => $exam->juz,
                    'score' => $exam->score,
                    'is_locked' => $exam->is_locked,
                    'is_nulled' => $exam->is_nulled,
                    'periode' => $exam->periode,
                ];
            });

        return response()->json([
            'student' => [
                'id' => $student->id,
                'nama' => $student->nama,
                'nisn' => $student->nisn,
                'classroom' => $student->classroom ? [
                    'id' => $student->classroom->id,
                    'nama' => $student->classroom->nama,
                    'level' => $student->classroom->level,
                ] : null,
                'rapor' => $student->rapor ? [
                    'total_juz_pas' => $student->rapor->total_juz_pas,
                    'pas_score' => $student->rapor->pas_score,
                    'pas_completed_juz' => $student->rapor->pas_completed_juz,
                    'pas_juz_scores' => $student->rapor->pas_juz_scores,
                ] : null,
            ],
            'examinations' => $examinations,
        ]);
    }
}
