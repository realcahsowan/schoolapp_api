<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Tahfidz\Configuration;
use App\Models\Tahfidz\Examination;
use App\Models\Tahfidz\Mistake;
use App\Models\Tahfidz\Rapor;
use App\Scopes\CurrentYearSemesterScope;
use App\Settings\GeneralSettings;

class RaporTahfidzService
{
    /**
     * Generate all data required for the Tahfidz rapor PDF/view for a given rapor ID
     */
    public function generateRaporData(int $id): array
    {
        $settings = app(GeneralSettings::class);
        $rapor = Rapor::withoutGlobalScope(CurrentYearSemesterScope::class)
            ->find($id);
        $student = Student::with([
            'classrooms' => fn($q) => $q->where('tahun_ajaran', $rapor->tahun_ajaran),
            'dormitories'
        ])->find($rapor->student_id);

        $classroom = $student->classrooms->first() ?? $student->classroom;
        $schoolId = $classroom?->school_id ?? $student->school?->id;

        $configNames = ['bobotRapor', 'bobotAspekPas', 'petaPredikat', 'programs', 'tanggalRapor', 'lokasiRapor'];
        $configs = Configuration::where('school_id', $schoolId)->whereIn('name', $configNames)->get();
        $bobotRapor = $configs->where('name', 'bobotRapor')->first()?->payload;
        $bobotAspekPas = $configs->where('name', 'bobotAspekPas')->first()?->payload;
        $petaPredikat = $configs->where('name', 'petaPredikat')->first()?->payload;
        $programs = $configs->where('name', 'programs')->first()?->payload;
        $tanggalRapor = $configs->where('name', 'tanggalRapor')->first()?->payload;
        $lokasiRapor = $configs->where('name', 'lokasiRapor')->first()?->payload;
        $descriptions = collect($petaPredikat)->pluck('deskripsi', 'predikat');

        // set final_score
        if (is_null($rapor->final_score)) {
            // $conversion = app(GeneralSettings::class)->achievement_conversion;
            $conversion = $settings->achievement_conversion;
            $finalScore = static::calculateFinalScore($rapor, $bobotRapor, $conversion);
            $rapor->final_score = $finalScore;
        }

        // set predikat
        if (is_null($rapor->predikat)) {
            $predikat = 'E';
            foreach ($petaPredikat as $group) {
                $rentang = range($group['min'], $group['max']);
                if (in_array(ceil($rapor->final_score), $rentang)) {
                    $predikat = $group['predikat'];
                    break;
                }
            }

            $rapor->predikat = $predikat;
        }

        if ($rapor->total_juz_pas > 1) {
            $detailType = 'examinations';
            $detailIndex = 'juz';

            // $detailItems = Examination::lockedForCurrentYearSemester()->where('student_id', $rapor->student_id)->get();
            if ($rapor->tahun_ajaran == $settings->tahun_ajaran && $rapor->semester == $settings->semester) {
                $detailItems = Examination::currentYearSemester()->where('student_id', $rapor->student_id)->get();
            } else {
                $detailItems = Examination::where('student_id', $rapor->student_id)
                    ->where('tahun_ajaran', $rapor->tahun_ajaran)
                    ->where('semester', $rapor->semester)
                    ->get();
            }
        }

        if ($rapor->total_juz_pas == 1) {
            $detailType = 'mistakes';
            $detailIndex = 'page';
            if ($rapor->tahun_ajaran == $settings->tahun_ajaran && $rapor->semester == $settings->semester) {
                $detailItems = Mistake::currentYearSemester()->where('student_id', $rapor->student_id)->where('juz', head($rapor->pas_juz_map))->get();
            } else {
                $detailItems = Mistake::where('student_id', $rapor->student_id)
                    ->where('juz', head($rapor->pas_juz_map))
                    ->where('tahun_ajaran', $rapor->tahun_ajaran)
                    ->where('semester', $rapor->semester)
                    ->get();
            }
        }

        $dorm = $student->dormitories->where('pivot.tahun_ajaran', $rapor->tahun_ajaran)
            ->where('pivot.semester', $rapor->semester)
            ->sortByDesc('pivot.updated_at')
            ->first();

        $murobbis = $student->murobbis()->withoutGlobalScope(CurrentYearSemesterScope::class)->where('tahun_ajaran', $rapor->tahun_ajaran)->where('semester', $rapor->semester)->get();

        $murobbi = is_null($rapor->murobbi_employee_id) ? $murobbis->first() : $murobbis->where('employee_id', $rapor->murobbi_employee_id)->first();
        return compact(
            'rapor',
            'student',
            'classroom',
            'murobbi',
            'bobotRapor',
            'bobotAspekPas',
            'petaPredikat',
            'programs',
            'tanggalRapor',
            'lokasiRapor',
            'detailType',
            'detailIndex',
            'detailItems',
            'descriptions',
            'dorm'
        );
    }

    /**
     * Hitung skor akhir rapor.
     */
    public static function calculateFinalScore($achievement, $bobot, $konversi)
    {
        $total = 0;
        foreach ($konversi as $key => $value) {
            $total += $achievement->{$key} * $bobot[$value];
        }

        return $total / array_sum($bobot);
    }
}
