<?php

namespace App\Http\Controllers\Api\Murobbi;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentMurobbi;
use App\Settings\GeneralSettings;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    protected function getMurobbiIds(): array
    {
        $employee = auth()->user()->employee;
        $settings = app(GeneralSettings::class);

        return $employee->murobbis()
            ->where('tahun_ajaran', $settings->tahun_ajaran)
            ->where('semester', $settings->semester)
            ->pluck('id')
            ->toArray();
    }

    public function index()
    {
        $murobbiIds = $this->getMurobbiIds();

        $students = Student::whereHas('murobbis', function ($q) use ($murobbiIds) {
            $q->whereIn('tahfidz__murobbis.id', $murobbiIds);
        })
            ->with(['classroom', 'dormitories', 'murobbis' => function ($q) use ($murobbiIds) {
                $q->whereIn('tahfidz__murobbis.id', $murobbiIds);
            }])
            ->get()
            ->map(function ($student) {
                $murobbi = $student->murobbis->first();

                return [
                    'id' => $student->id,
                    'nama' => $student->nama,
                    'nis' => $student->nis,
                    'nisn' => $student->nisn,
                    'nik' => $student->nik,
                    'gender' => $student->gender,
                    'tempat_lahir' => $student->tempat_lahir,
                    'tanggal_lahir' => $student->tanggal_lahir,
                    'alamat' => $student->alamat,
                    'telepon' => $student->telepon,
                    'file_foto' => $student->file_foto,
                    'classroom' => $student->classroom ? [
                        'id' => $student->classroom->id,
                        'nama' => $student->classroom->nama,
                        'level' => $student->classroom->level,
                    ] : null,
                    'dormitories' => $student->dormitories->map(fn($d) => [
                        'id' => $d->id,
                        'name' => $d->name,
                        'pivot' => [
                            'room' => $d->pivot->room,
                            'is_active' => $d->pivot->is_active,
                        ],
                    ]),
                    'pivot' => [
                        'category' => $murobbi?->pivot?->category,
                        'program' => $murobbi?->pivot?->program,
                        'is_active' => $murobbi?->pivot?->is_active,
                    ],
                ];
            });

        return response()->json([
            'students' => $students,
        ]);
    }

    public function search(Request $request)
    {
        $murobbiIds = $this->getMurobbiIds();
        $query = $request->get('q');

        $students = Student::whereHas('murobbis', function ($q) use ($murobbiIds) {
            $q->whereIn('tahfidz__murobbis.id', $murobbiIds);
        })
            ->where('nama', 'like', "%{$query}%")
            ->get(['id', 'nama', 'nisn'])
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'nama' => $student->nama,
                    'nisn' => $student->nisn,
                ];
            });

        return response()->json([
            'students' => $students,
        ]);
    }

    public function show(Student $student)
    {
        $murobbiIds = $this->getMurobbiIds();

        $exists = StudentMurobbi::whereIn('murobbi_id', $murobbiIds)
            ->where('student_id', $student->id)
            ->exists();

        abort_unless($exists, 403);

        $student->load(['classroom', 'dormitories', 'journals' => function ($q) {
            $q->latest()->take(10);
        }, 'penilaianPeriodiks' => function ($q) {
            $q->latest()->take(10);
        }, 'memorizationSummaries', 'murobbis' => function ($q) use ($murobbiIds) {
            $q->whereIn('tahfidz__murobbis.id', $murobbiIds);
        }]);

        $murobbi = $student->murobbis->first();

        return response()->json([
            'student' => [
                'id' => $student->id,
                'nama' => $student->nama,
                'nis' => $student->nis,
                'nisn' => $student->nisn,
                'nik' => $student->nik,
                'gender' => $student->gender,
                'tempat_lahir' => $student->tempat_lahir,
                'tanggal_lahir' => $student->tanggal_lahir,
                'alamat' => $student->alamat,
                'telepon' => $student->telepon,
                'file_foto' => $student->file_foto,
                'classroom' => $student->classroom ? [
                    'id' => $student->classroom->id,
                    'nama' => $student->classroom->nama,
                    'level' => $student->classroom->level,
                ] : null,
                'dormitories' => $student->dormitories->map(fn($d) => [
                    'id' => $d->id,
                    'name' => $d->name,
                    'room' => $d->pivot->room,
                    'is_active' => $d->pivot->is_active,
                ]),
                'pivot' => [
                    'category' => $murobbi?->pivot?->category,
                    'program' => $murobbi?->pivot?->program,
                    'is_active' => $murobbi?->pivot?->is_active,
                ],
                'recent_journals' => $student->journals->map(fn($j) => [
                    'id' => $j->id,
                    'tanggal' => \Carbon\Carbon::parse($j->tanggal)->format('Y-m-d'),
                    'waktu' => $j->waktu,
                    'kehadiran' => $j->kehadiran,
                    'status' => $j->status,
                ]),
                'recent_assessments' => $student->penilaianPeriodiks->map(fn($p) => [
                    'id' => $p->id,
                    'tanggal' => \Carbon\Carbon::parse($p->tanggal)->format('Y-m-d'),
                    'score' => $p->score,
                    'kehadiran' => $p->kehadiran,
                ]),
                'memorization_summaries' => $student->memorizationSummaries->map(fn($s) => [
                    'id' => $s->id,
                    'periode' => $s->periode,
                    'awal_periode' => \Carbon\Carbon::parse($s->awal_periode)->format('Y-m-d'),
                    'akhir_periode' => \Carbon\Carbon::parse($s->akhir_periode)->format('Y-m-d'),
                    'total_halaman' => $s->total_halaman,
                    'ringkasan' => $s->ringkasan,
                ]),
            ],
        ]);
    }

    public function journals(Student $student, Request $request)
    {
        $murobbiIds = $this->getMurobbiIds();

        $exists = StudentMurobbi::whereIn('murobbi_id', $murobbiIds)
            ->where('student_id', $student->id)
            ->exists();

        abort_unless($exists, 403);

        $journals = $student->journals()
            ->whereIn('murobbi_id', $murobbiIds)
            ->with('murobbi')
            ->orderBy('tanggal', 'desc')
            ->paginate(20);

        return response()->json($journals);
    }

    public function memorizationSummaries(Student $student, Request $request)
    {
        $murobbiIds = $this->getMurobbiIds();

        $exists = StudentMurobbi::whereIn('murobbi_id', $murobbiIds)
            ->where('student_id', $student->id)
            ->exists();

        abort_unless($exists, 403);

        $periode = $request->get('periode');
        $perPage = $request->integer('per_page', 15);

        $summaries = $student->memorizationSummaries()
            ->when($periode, fn($q) => $q->where('periode', $periode))
            ->orderBy('awal_periode', 'desc')
            ->paginate($perPage);

        return response()->json([
            'summaries' => $summaries->items(),
            'current_page' => $summaries->currentPage(),
            'last_page' => $summaries->lastPage(),
            'per_page' => $summaries->perPage(),
            'total' => $summaries->total(),
        ]);
    }
}
