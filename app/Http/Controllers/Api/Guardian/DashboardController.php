<?php

namespace App\Http\Controllers\Api\Guardian;

use App\Http\Controllers\Controller;
use App\Models\Tahfidz\Journal;
use App\Models\Tahfidz\MemorizationSummary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $guardian = $user->guardian;

        if (!$guardian) {
            return response()->json([
                'students' => [],
                'selectedStudent' => null,
                'latestJournal' => null,
                'attendanceToday' => null,
                'progress' => 0,
                'guardian' => null,
                'dormitory' => null,
                'memorizationSummary' => null,
            ]);
        }

        $generalSettings = app(\App\Settings\GeneralSettings::class);

        $students = $guardian->students()->with(['dormitories' => function ($query) use ($generalSettings) {
            $query->where('is_active', true)
                  ->where('tahun_ajaran', $generalSettings->tahun_ajaran)
                  ->where('semester', $generalSettings->semester);
        }, 'murobbis', 'classroom'])->get();

        $studentId = $request->query('student_id');
        $selectedStudent = $studentId
            ? $students->firstWhere('id', (int) $studentId)
            : $students->first();

        if (!$selectedStudent && $students->isNotEmpty()) {
            $selectedStudent = $students->first();
        }

        $latestJournal = $selectedStudent
            ? Journal::where('student_id', $selectedStudent->id)
                ->with('murobbi')
                ->orderBy('tanggal', 'desc')
                ->orderBy('created_at', 'desc')
                ->first()
            : null;

        $attendanceToday = $selectedStudent
            ? Journal::where('student_id', $selectedStudent->id)
                ->whereDate('tanggal', now()->toDateString())
                ->first()
            : null;

        $memorizationSummary = $selectedStudent
            ? MemorizationSummary::where('student_id', $selectedStudent->id)
                ->where('periode', 'semesterly')
                ->orderBy('tahun_ajaran', 'desc')
                ->orderBy('semester', 'desc')
                ->first()
            : null;

        $progress = 75;

        return response()->json([
            'students' => $students,
            'selectedStudent' => $selectedStudent,
            'latestJournal' => $latestJournal,
            'attendanceToday' => $attendanceToday,
            'progress' => $progress,
            'guardian' => $guardian,
            'dormitory' => $selectedStudent?->dormitories->first(),
            'memorizationSummary' => $memorizationSummary,
        ]);
    }
}
