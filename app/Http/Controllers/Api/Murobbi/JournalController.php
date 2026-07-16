<?php

namespace App\Http\Controllers\Api\Murobbi;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentMurobbi;
use App\Models\Tahfidz\KalenderHafalan;
use App\Models\Tahfidz\Journal;
use App\Settings\GeneralSettings;
use App\Traits\HasSuratName;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JournalController extends Controller
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

        $journals = Journal::whereIn('murobbi_id', $murobbiIds)
            ->with('student')
            ->orderBy('tanggal', 'desc')
            ->paginate(20);

        return response()->json($journals);
    }

    public function createData()
    {
        $data = $this->getMurobbiData();
        $murobbiIds = $data['murobbi_ids'];
        $schoolIds = $data['school_ids'];

        $students = Student::whereHas('murobbis', fn($q) => $q->whereIn('tahfidz__murobbis.id', $murobbiIds))
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'nama' => $s->nama,
            ]);

        $availableDates = KalenderHafalan::whereIn('school_id', $schoolIds)
            ->orderBy('tanggal', 'desc')
            ->get(['id', 'tanggal', 'hp_summary', 'hs_summary'])
            ->map(fn($k) => [
                'id' => $k->id,
                'tanggal' => $k->tanggal?->format('Y-m-d'),
                'label' => $k->tanggal?->translatedFormat('l, d F Y'),
                'hp_summary' => $k->hp_summary,
                'hs_summary' => $k->hs_summary,
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

        $validated = $request->validate([
            'student_id' => [
                'required',
                'exists:students,id',
                Rule::unique('tahfidz__journals')->where(function ($query) use ($request) {
                    return $query->where('tanggal', $request->tanggal)
                        ->where('waktu', $request->waktu)
                        ->where('student_id', $request->student_id);
                }),
            ],
            'tanggal' => 'required|date',
            'waktu' => 'required|string|in:pagi,sore',
            'kehadiran' => 'required|string',
            'status' => 'nullable|string',
            'detail_capaian' => 'nullable|array',
            'detail_extra' => 'nullable|array',
            'detail_khusus' => 'nullable|array',
            'pelanggaran' => 'nullable|array',
            'catatan' => 'nullable|string',
        ], [
            'student_id.unique' => 'Mutabaah untuk siswa ini pada tanggal dan waktu tersebut sudah ada.',
        ]);

        if (($validated['status'] ?? '') === 'tidak_setoran') {
            $validated['detail_capaian'] = null;
            $validated['detail_extra'] = null;
            $validated['detail_khusus'] = null;
        }

        $pivot = StudentMurobbi::whereIn('murobbi_id', $murobbiIds)
            ->where('student_id', $request->student_id)
            ->firstOrFail();

        $murobbiId = $pivot->murobbi_id;

        $student = Student::with('classroom')->findOrFail($request->student_id);
        $school_id = $student->classroom?->school_id;

        $kalender = KalenderHafalan::where('tanggal', $request->tanggal)
            ->where('school_id', $school_id)
            ->first();

        if ($kalender) {
            $validated['kalender_id'] = $kalender->id;
            $validated['year'] = $kalender->year;
            $validated['month'] = $kalender->month;
            $validated['week'] = $kalender->week;
            $validated['tahun_ajaran'] = $kalender->tahun_ajaran;
            $validated['semester'] = $kalender->semester;
        }

        $validated['murobbi_id'] = $murobbiId;
        $journal = Journal::create($validated);

        return response()->json([
            'message' => 'Mutabaah Harian berhasil disimpan',
            'journal' => $journal,
        ], 201);
    }

    public function show(Journal $journal)
    {
        $data = $this->getMurobbiData();
        $murobbiIds = $data['murobbi_ids'];

        if (!in_array($journal->murobbi_id, $murobbiIds)) {
            abort(403, 'Unauthorized');
        }

        $journal->load('student');
        $surah = $this->getAllSurat();

        $journalData = $journal->toArray();
        $journalData['tanggal'] = $journal->tanggal ? \Carbon\Carbon::parse($journal->tanggal)->format('Y-m-d') : null;
        $journalData['surah'] = $surah;

        return response()->json([
            'journal' => $journalData,
        ]);
    }

    public function edit(Journal $journal)
    {
        $data = $this->getMurobbiData();
        $murobbiIds = $data['murobbi_ids'];
        $schoolIds = $data['school_ids'];

        if (!in_array($journal->murobbi_id, $murobbiIds)) {
            abort(403, 'Unauthorized');
        }

        $students = Student::whereHas('murobbis', fn($q) => $q->whereIn('tahfidz__murobbis.id', $murobbiIds))
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'nama' => $s->nama,
            ]);

        $availableDates = KalenderHafalan::whereIn('school_id', $schoolIds)
            ->orderBy('tanggal', 'desc')
            ->get(['id', 'tanggal', 'hp_summary', 'hs_summary'])
            ->map(fn($k) => [
                'id' => $k->id,
                'tanggal' => $k->tanggal?->format('Y-m-d'),
                'label' => $k->tanggal?->translatedFormat('l, d F Y'),
                'hp_summary' => $k->hp_summary,
                'hs_summary' => $k->hs_summary,
            ]);

        $surah = $this->getAllSurat();
        $journalData = $journal->toArray();
        $journalData['tanggal'] = $journal->tanggal ? \Carbon\Carbon::parse($journal->tanggal)->format('Y-m-d') : null;

        return response()->json([
            'students' => $students,
            'availableDates' => $availableDates,
            'surah' => $surah,
            'journal' => $journalData,
        ]);
    }

    public function update(Request $request, Journal $journal)
    {
        $data = $this->getMurobbiData();
        $murobbiIds = $data['murobbi_ids'];

        if (!in_array($journal->murobbi_id, $murobbiIds)) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'student_id' => [
                'required',
                'exists:students,id',
                Rule::unique('tahfidz__journals')->where(function ($query) use ($request) {
                    return $query->where('tanggal', $request->tanggal)
                        ->where('waktu', $request->waktu)
                        ->where('student_id', $request->student_id);
                })->ignore($journal->id),
            ],
            'tanggal' => 'required|date',
            'waktu' => 'required|string|in:pagi,sore',
            'kehadiran' => 'required|string',
            'status' => 'nullable|string',
            'detail_capaian' => 'nullable|array',
            'detail_extra' => 'nullable|array',
            'detail_khusus' => 'nullable|array',
            'pelanggaran' => 'nullable|array',
            'catatan' => 'nullable|string',
        ], [
            'student_id.unique' => 'Mutabaah untuk siswa ini pada tanggal dan waktu tersebut sudah ada.',
        ]);

        if (($validated['status'] ?? '') === 'tidak_setoran') {
            $validated['detail_capaian'] = null;
            $validated['detail_extra'] = null;
            $validated['detail_khusus'] = null;
        }

        $pivot = StudentMurobbi::whereIn('murobbi_id', $murobbiIds)
            ->where('student_id', $request->student_id)
            ->firstOrFail();

        $student = Student::with('classroom')->findOrFail($request->student_id);
        $school_id = $student->classroom?->school_id;

        $kalender = KalenderHafalan::where('tanggal', $request->tanggal)
            ->where('school_id', $school_id)
            ->first();

        if ($kalender) {
            $validated['kalender_id'] = $kalender->id;
            $validated['year'] = $kalender->year;
            $validated['month'] = $kalender->month;
            $validated['week'] = $kalender->week;
            $validated['tahun_ajaran'] = $kalender->tahun_ajaran;
            $validated['semester'] = $kalender->semester;
        }

        $validated['murobbi_id'] = $pivot->murobbi_id;
        $journal->update($validated);

        return response()->json([
            'message' => 'Mutabaah Harian berhasil diperbarui',
            'journal' => $journal,
        ]);
    }

    public function destroy(Journal $journal)
    {
        $data = $this->getMurobbiData();
        $murobbiIds = $data['murobbi_ids'];

        if (!in_array($journal->murobbi_id, $murobbiIds)) {
            abort(403, 'Unauthorized');
        }

        $journal->delete();

        return response()->json([
            'message' => 'Mutabaah Harian berhasil dihapus',
        ]);
    }
}
