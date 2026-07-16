<?php

namespace App\Http\Controllers\Api\PengujiTahfidz;

use App\Http\Controllers\Controller;
use App\Models\Tahfidz\Examination;
use App\Models\Tahfidz\Rapor;
use Illuminate\Support\Arr;

class ExaminationController extends Controller
{
    public function show($id)
    {
        $examination = Examination::with(['student', 'penguji', 'mistakes'])->findOrFail($id);

        return response()->json([
            'examination' => [
                'id' => $examination->id,
                'student_id' => $examination->student_id,
                'penguji_id' => $examination->penguji_id,
                'tahun_ajaran' => $examination->tahun_ajaran,
                'semester' => $examination->semester,
                'juz' => $examination->juz,
                'score' => $examination->score,
                'old_score' => $examination->old_score,
                'is_nulled' => $examination->is_nulled,
                'is_locked' => $examination->is_locked,
                'periode' => $examination->periode,
                'student' => $examination->student ? [
                    'id' => $examination->student->id,
                    'nama' => $examination->student->nama,
                ] : null,
                'penguji' => $examination->penguji ? [
                    'id' => $examination->penguji->id,
                    'nama' => $examination->penguji->nama,
                ] : null,
                'mistakes_count' => $examination->mistakes->count(),
            ],
        ]);
    }

    public function setNotSubmitted(int $id)
    {
        $examination = Examination::with('mistakes')->findOrFail($id);

        $examination->update([
            'old_score' => $examination->score,
            'score' => 0,
            'is_nulled' => true,
            'is_locked' => true,
        ]);

        $examination->mistakes()->delete();

        return response()->json([
            'message' => 'Ujian ditandai sebagai tidak disetor',
        ]);
    }

    public function unlock($id)
    {
        $examination = Examination::findOrFail($id);
        $rapor = Rapor::where('student_id', $examination->student_id)->first();

        if ($rapor) {
            $scores = Arr::get($rapor, 'pas_juz_scores', []);
            Arr::forget($scores, (string) $examination->juz);
            $score = 0;
            if ($rapor->total_juz_pas > 0) {
                $score = array_sum($scores) / $rapor->total_juz_pas;
            }

            $rapor->update([
                'completed_juz_pas' => count($scores),
                'pas_juz_scores' => $scores,
                'pas_score' => $score,
            ]);
        }

        $examination->update(['is_locked' => false]);

        return response()->json([
            'message' => 'Ujian berhasil dibuka kembali',
        ]);
    }
}
