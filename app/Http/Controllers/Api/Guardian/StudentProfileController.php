<?php

namespace App\Http\Controllers\Api\Guardian;

use App\Http\Controllers\Controller;
use App\Models\Tahfidz\MemorizationSummary;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentProfileController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $guardian = $user->guardian;

        if (!$guardian) {
            return response()->json([
                'students' => [],
                'selectedStudent' => null,
            ]);
        }

        $students = $guardian->students()->get();

        $studentId = $request->query('student_id');
        $selectedStudentId = $studentId ?: $students->first()?->id;

        $selectedStudent = null;
        $journals = null;
        $memorizationSummaries = null;
        $rapors = null;

        if ($selectedStudentId) {
            $selectedStudent = Student::with([
                'classroom.school',
                'dormitories',
                'murobbis',
            ])->where('id', $selectedStudentId)
              ->whereHas('guardians', function ($query) use ($guardian) {
                  $query->where('guardians.id', $guardian->id);
              })
              ->first();

            if ($selectedStudent) {
                $journals = $selectedStudent->journals()
                    ->orderBy('tanggal', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->paginate(10)
                    ->withQueryString();

                $memorizationSummaries = MemorizationSummary::where('student_id', $selectedStudent->id)
                    ->orderBy('akhir_periode', 'desc')
                    ->get()
                    ->groupBy('periode');

                $rapors = $selectedStudent->rapors()
                    ->orderBy('tahun_ajaran', 'desc')
                    ->orderBy('semester', 'desc')
                    ->get();
            }
        }

        return response()->json([
            'students' => $students,
            'selectedStudent' => $selectedStudent,
            'journals' => $journals,
            'memorizationSummaries' => $memorizationSummaries,
            'rapors' => $rapors,
        ]);
    }
}
