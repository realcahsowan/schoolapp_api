<?php

namespace App\Http\Controllers\Api\Guardian;

use App\Http\Controllers\Controller;
use App\Models\Tahfidz\Journal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JournalController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $guardian = $user->guardian;

        if (!$guardian) {
            return response()->json([
                'students' => [],
                'journals' => null,
            ]);
        }

        $students = $guardian->students()->get();
        $studentId = $request->query('student_id') ?: $students->first()?->id;

        $journals = null;
        if ($studentId) {
            $journals = Journal::where('student_id', $studentId)
                ->with('murobbi')
                ->orderBy('tanggal', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate(15)
                ->withQueryString();
        }

        return response()->json([
            'students' => $students,
            'journals' => $journals,
            'selectedStudentId' => (int) $studentId,
        ]);
    }

    public function show(Request $request, Journal $journal): JsonResponse
    {
        $user = Auth::user();
        $guardian = $user->guardian;

        if (!$guardian || !$guardian->students()->where('students.id', $journal->student_id)->exists()) {
            abort(403, 'Forbidden');
        }

        $journal->load(['student.classroom.school', 'murobbi']);

        return response()->json([
            'journal' => $journal,
        ]);
    }
}
