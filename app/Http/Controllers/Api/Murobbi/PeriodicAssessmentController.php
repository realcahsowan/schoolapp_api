<?php

namespace App\Http\Controllers\Api\Murobbi;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentMurobbi;
use App\Models\Tahfidz\KalenderHafalan;
use App\Models\Tahfidz\PenilaianPeriodik;
use App\Settings\GeneralSettings;
use App\Traits\HasSuratName;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PeriodicAssessmentController extends Controller
{
    use HasSuratName;

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
        $data = $this->getMurobbiData();
        $murobbiIds = $data['murobbi_ids'];

        $assessments = PenilaianPeriodik::with('student.classroom')
            ->whereIn('murobbi_id', $murobbiIds)
            ->orderBy('tanggal', 'desc')
            ->paginate(10);

        return response()->json($assessments);
    }

    public function createData()
    {
        $data = $this->getMurobbiData();
        $murobbiIds = $data['murobbi_ids'];
        $schoolIds = $data['school_ids'];

        $students = Student::whereHas('murobbis', fn($q) => $q->whereIn('tahfidz__murobbis.id', $murobbiIds))
            ->with('classroom')
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'nama' => $s->nama,
                'classroom' => $s->classroom ? [
                    'nama' => $s->classroom->nama,
                    'level' => $s->classroom->level,
                ] : null,
            ]);

        $availableDates = KalenderHafalan::whereIn('school_id', $schoolIds)
            ->where('is_weekly_examination', true)
            ->where('tanggal', '>=', now()->subDays(30))
            ->orderBy('tanggal', 'desc')
            ->get(['id', 'tanggal'])
            ->map(fn($k) => [
                'id' => $k->id,
                'tanggal' => $k->tanggal->format('Y-m-d'),
                'label' => $k->tanggal->translatedFormat('l, d F Y'),
            ]);

        $surah = $this->getAllSurat();

        return response()->json([
            'students' => $students,
            'availableDates' => $availableDates,
            'surah' => $surah,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->getMurobbiData();
        $murobbiIds = $data['murobbi_ids'];
        $settings = app(GeneralSettings::class);

        $validated = $request->validate([
            'student_id' => [
                'required',
                'exists:students,id',
                Rule::unique('tahfidz__penilaian_periodik')->where(function ($query) use ($request) {
                    return $query->where('tanggal', $request->tanggal)
                        ->where('student_id', $request->student_id);
                }),
            ],
            'tanggal' => 'required|date',
            'kehadiran' => 'required|string|in:hadir,sakit,izin,alpa',
            'jenis_izin' => 'nullable|string',
            'keterangan_izin_lainnya' => 'nullable|string',
            'target' => 'nullable|array',
            'pages_map' => 'nullable|array',
            'detail' => 'nullable|array',
        ], [
            'student_id.unique' => 'Penilaian periodik untuk siswa ini pada tanggal tersebut sudah ada.',
        ]);

        $pivot = StudentMurobbi::whereIn('murobbi_id', $murobbiIds)
            ->where('student_id', $request->student_id)
            ->firstOrFail();

        $detail = $validated['detail'] ?? [];
        $scores = [];
        foreach ($detail as $item) {
            $scores[] = (($item['kelancaran'] ?? 0) + ($item['fashohah'] ?? 0) + ($item['tajwid'] ?? 0)) / 3;
        }
        $finalScore = count($scores) > 0 ? round(array_sum($scores) / count($scores), 2) : 0;

        $assessment = PenilaianPeriodik::create([
            'student_id' => $validated['student_id'],
            'murobbi_id' => $pivot->murobbi_id,
            'tahun_ajaran' => $settings->tahun_ajaran,
            'semester' => $settings->semester,
            'tanggal' => $validated['tanggal'],
            'kehadiran' => $validated['kehadiran'],
            'jenis_izin' => $validated['jenis_izin'] ?? null,
            'keterangan_izin_lainnya' => $validated['keterangan_izin_lainnya'] ?? null,
            'target' => $validated['target'] ?? null,
            'pages_map' => $validated['pages_map'] ?? null,
            'detail' => $detail,
            'score' => $finalScore,
        ]);

        return response()->json([
            'message' => 'Penilaian Periodik berhasil disimpan',
            'assessment' => $assessment,
        ], 201);
    }

    public function edit(PenilaianPeriodik $assessment)
    {
        $data = $this->getMurobbiData();
        $murobbiIds = $data['murobbi_ids'];
        $schoolIds = $data['school_ids'];

        if (!in_array($assessment->murobbi_id, $murobbiIds)) {
            abort(403, 'Unauthorized');
        }

        $assessment->load('student.classroom');

        $students = Student::whereHas('murobbis', fn($q) => $q->whereIn('tahfidz__murobbis.id', $murobbiIds))
            ->with('classroom')
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'nama' => $s->nama,
                'classroom' => $s->classroom ? [
                    'nama' => $s->classroom->nama,
                    'level' => $s->classroom->level,
                ] : null,
            ]);

        $availableDates = KalenderHafalan::whereIn('school_id', $schoolIds)
            ->where('is_weekly_examination', true)
            ->where(function ($q) use ($assessment) {
                $q->where('tanggal', '>=', now()->subDays(30))
                  ->orWhere('tanggal', $assessment->tanggal);
            })
            ->orderBy('tanggal', 'desc')
            ->get(['id', 'tanggal'])
            ->map(fn($k) => [
                'id' => $k->id,
                'tanggal' => $k->tanggal->format('Y-m-d'),
                'label' => $k->tanggal->translatedFormat('l, d F Y'),
            ]);

        $surah = $this->getAllSurat();

        $assessmentData = $assessment->toArray();
        $assessmentData['tanggal'] = $assessment->tanggal ? \Carbon\Carbon::parse($assessment->tanggal)->format('Y-m-d') : null;

        return response()->json([
            'students' => $students,
            'availableDates' => $availableDates,
            'surah' => $surah,
            'assessment' => $assessmentData,
        ]);
    }

    public function update(Request $request, PenilaianPeriodik $assessment)
    {
        $data = $this->getMurobbiData();
        $murobbiIds = $data['murobbi_ids'];

        if (!in_array($assessment->murobbi_id, $murobbiIds)) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'kehadiran' => 'required|string|in:hadir,sakit,izin,alpa',
            'jenis_izin' => 'nullable|string',
            'keterangan_izin_lainnya' => 'nullable|string',
            'target' => 'nullable|array',
            'pages_map' => 'nullable|array',
            'detail' => 'nullable|array',
        ]);

        $detail = $validated['detail'] ?? [];
        $scores = [];
        foreach ($detail as $item) {
            $scores[] = (($item['kelancaran'] ?? 0) + ($item['fashohah'] ?? 0) + ($item['tajwid'] ?? 0)) / 3;
        }
        $finalScore = count($scores) > 0 ? round(array_sum($scores) / count($scores), 2) : 0;

        $assessment->update([
            'tanggal' => $validated['tanggal'],
            'kehadiran' => $validated['kehadiran'],
            'jenis_izin' => $validated['jenis_izin'] ?? null,
            'keterangan_izin_lainnya' => $validated['keterangan_izin_lainnya'] ?? null,
            'target' => $validated['target'] ?? null,
            'pages_map' => $validated['pages_map'] ?? null,
            'detail' => $detail,
            'score' => $finalScore,
        ]);

        return response()->json([
            'message' => 'Penilaian Periodik berhasil diperbarui',
            'assessment' => $assessment,
        ]);
    }

    public function destroy(PenilaianPeriodik $assessment)
    {
        $data = $this->getMurobbiData();
        $murobbiIds = $data['murobbi_ids'];

        if (!in_array($assessment->murobbi_id, $murobbiIds)) {
            abort(403, 'Unauthorized');
        }

        $assessment->delete();

        return response()->json([
            'message' => 'Penilaian Periodik berhasil dihapus',
        ]);
    }
}
