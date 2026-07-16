<?php

namespace App\Http\Controllers\Api\PengujiTahfidz;

use App\Http\Controllers\Controller;
use App\Models\Tahfidz\Configuration;
use App\Models\Tahfidz\Examination;
use App\Models\Tahfidz\Mistake;
use App\Settings\GeneralSettings;
use App\Traits\QuranTrait;
use Illuminate\Support\Str;

class EditExaminationController extends Controller
{
    use QuranTrait;

    public function __invoke($id)
    {
        $examination = Examination::with([
            'student' => fn ($q) => $q->with(['school', 'classroom']),
            'penguji.employee'
        ])->findOrFail($id);

        $hash = $examination->hash ?? $this->generateExaminationHash($examination);

        if (!$examination->hash) {
            $examination->update(['hash' => $hash]);
        }

        if (!$examination || !$examination->penguji || !$examination->penguji->employee) {
            return response()->json(['message' => 'Anda tidak berhak mengakses ujian ini.'], 403);
        }
        if (auth()->user()->employee->id !== $examination->penguji->employee->id) {
            return response()->json(['message' => 'Anda tidak berhak mengakses ujian ini.'], 403);
        }

        if ($examination->is_locked) {
            return response()->json(['message' => 'Ujian sudah terkunci'], 422);
        }

        $student = $examination->student;
        $configurations = Configuration::where('school_id', $examination->student->classroom->school->id)->get();
        $confPoinKriteria = $configurations->where('name', 'poinKriteriaPas')->first()?->payload;
        $confBobotPas = $configurations->where('name', 'bobotAspekPas')->first()?->payload;

        $mistakes = $this->getMistakesRecords($examination);
        if ($mistakes->count() === 0) {
            $this->generateMistakesData($examination, $confPoinKriteria);
            $mistakes = $this->getMistakesRecords($examination);
        }

        $firstPage = $mistakes->sortBy('page')->first()?->page;
        $lastPage = $mistakes->sortBy('page')->last()?->page;

        $kriterias = $this->getPoinKriteria($confPoinKriteria);

        $mistakes = $mistakes->each(function ($mistake) use ($kriterias) {
            $detail = [];
            foreach ($kriterias as $group => $kriteria) {
                $detail[$group] = [];
                foreach (array_keys($kriteria) as $label) {
                    foreach ($mistake->detail[$group] ?? [] as $key => $val) {
                        if (Str::of($label)->contains($key) || Str::of($key)->contains($label)) {
                            $detail[$group][$label] = $val;
                        }
                    }
                }
            }
            $mistake->detail = $detail;
            return $mistake;
        });

        $bobotPas = $confBobotPas ? array_map(fn ($v) => (int) $v, $confBobotPas) : [];
        $normalRawScore = $this->generateRawScore($confPoinKriteria);
        $nulledRawScore = $this->generateRawScore($confPoinKriteria, true);
        $mistakesTemplate = $this->generateMistakesTemplate($confPoinKriteria);

        $juzMap = $this->getjuzMap();
        $pages = $juzMap[$examination->juz] ?? [1, 20];

        $baseData = [
            'bobot' => $bobotPas,
            'kriterias' => $kriterias,
            'mistakes' => $mistakes->pluck('detail', 'page')?->toArray() ?? [],
            'scores' => $mistakes->pluck('score', 'page')?->sortKeys()?->toArray() ?? [],
            'rawScores' => $mistakes->pluck('raw_score', 'page')?->sortKeys()?->toArray() ?? [],
            'nulleds' => $mistakes->pluck('is_nulled', 'page')?->sortKeys()?->toArray() ?? [],
            'page' => $firstPage,
        ];

        return response()->json([
            'student' => [
                'id' => $student->id,
                'nama' => $student->nama,
                'classroom' => $student->classroom ? [
                    'nama' => $student->classroom->nama,
                    'level' => $student->classroom->level,
                ] : null,
            ],
            'examinationJuz' => $examination->juz,
            'examinationScore' => $examination->score,
            'hash' => $hash,
            'firstPage' => $firstPage,
            'lastPage' => $lastPage,
            'pages' => range($pages[0], $pages[1]),
            'normalRawScore' => $normalRawScore,
            'nulledRawScore' => $nulledRawScore,
            'mistakesTemplate' => $mistakesTemplate,
            'initialData' => $baseData,
        ]);
    }

    public function getMistakesRecords($examination)
    {
        $settings = app(GeneralSettings::class);
        return Mistake::where(function ($query) use ($examination, $settings) {
            $query->where('penguji_id', $examination->penguji_id)
                ->where('student_id', $examination->student_id)
                ->where('tahun_ajaran', $settings->tahun_ajaran)
                ->where('semester', $settings->semester)
                ->where('juz', $examination->juz);
        })->get();
    }

    public function generateExaminationHash($examination)
    {
        $studentId = $examination->student->id;
        $pengujiId = $examination->penguji->id;
        $year = $examination->tahun_ajaran;
        $semester = $examination->semester;
        $juz = $examination->juz;
        return "s-{$studentId}-p-{$pengujiId}-ta-{$year}-sem-{$semester}-juz-{$juz}";
    }
}
