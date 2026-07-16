<?php

namespace App\Jobs\Tahfidz;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use App\Exceptions\TahfidzException;
use App\Models\Student;
use App\Models\Tahfidz\Configuration;
use App\Models\Tahfidz\Examination;
use App\Settings\GeneralSettings;
use Illuminate\Support\Arr;

class GenerateExaminations implements ShouldQueue
{
    use Dispatchable;
    use Queueable;

    public int $studentId;
    public int $pengujiId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $studentId, int $pengujiId)
    {
        $this->studentId = $studentId;
        $this->pengujiId = $pengujiId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $year = app(GeneralSettings::class)->tahun_ajaran;
        $semester = app(GeneralSettings::class)->semester;


        $student = Student::with([
            'classroom',
            'murobbis' => fn($q) => $q->where('tahun_ajaran', $year)->where('semester', $semester)
                ->wherePivot('is_active', true),
        ])->find($this->studentId);

        $program = $student->murobbis->first()?->pivot->program;
        if (is_null($program)) {
            throw new TahfidzException('Student has no tahfidz and program set.');
        }

        $configuration = Configuration::where('school_id', $student->classroom->school_id)
            ->where('name', 'juzUjian')
            ->first();
        $items = Arr::get(collect($configuration->payload)->where('semester', $semester)->first(), 'detail', []);
        $juzUjian = collect($items)->where('program', $program)->first();

        if (is_null($juzUjian)) {
            throw new TahfidzException('Juz Ujian Not Found!');
        }

        $juzUjianOfGrade = collect($juzUjian['juz_map'])->where('grade', $student->classroom->level)->first();
        $arr = $juzMap = Arr::get($juzUjianOfGrade, 'juz', []);

        // TODO: ternyata butuh achievement untuk customized juz map
        // if (count(Arr::get($student->achievement, 'pas_juz_map')) > count($juzMap)) {
        //     // use juz map from achievement
        //     $arr = $student->achievement->pas_juz_map;
        // }

        // if (count(array_intersect(Arr::get($student->achievement, 'pas_juz_map', []), $juzMap)) == 0) {
        //     // use juz map from achievement
        //     $arr = $student->achievement->pas_juz_map;
        // }

        // if (count(array_diff(Arr::get($student->achievement, 'pas_juz_map', []), $juzMap)) > 0) {
        //     // use juz map from achievement
        //     $arr = $student->achievement->pas_juz_map;
        // }

        // if ($student->achievement->pas_has_customized_juz) {
        //     $arr = $student->achievement->pas_juz_map;
        // }

        // generate examination model
        foreach ($arr as $juz) {
            Examination::firstOrCreate([
                'tahun_ajaran' => $year,
                'semester' => $semester,
                'penguji_id' => $this->pengujiId,
                'student_id' => $this->studentId,
                'school_id' => $student->classroom->school_id,
                'juz' => $juz,
                'periode' => 'pas',
                'hash' => static::generateExaminationHash($this->studentId, $this->pengujiId, $juz, $year, $semester),
                // 'is_locked' => false,
            ]);
        }
    }

    public static function generateExaminationHash($studentId, $pengujiId, $juz, $year, $semester)
    {
        return "s-{$studentId}-p-{$pengujiId}-ta-{$year}-sem-{$semester}-juz-{$juz}";
    }
}
