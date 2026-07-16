<?php

namespace App\Models\Tahfidz;

use App\Models\School;
use App\Models\Student;
use App\Settings\GeneralSettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Examination extends Model
{
    protected $table = 'tahfidz__examinations';

    protected $fillable = [
        'student_id',
        'penguji_id',
        'tahun_ajaran',
        'semester',
        'juz',
        'score',
        'old_score',
        'is_nulled',
        'is_remedialed',
        'juz_part',
        'periode',
        'hash',
        'detail',
        'is_locked',
        'school_id',
        'is_manually_modified',
    ];

    protected $casts = [
        'juz' => 'integer',
        'score' => 'float',
        'old_score' => 'float',
        'is_nulled' => 'boolean',
        'is_remedialed' => 'boolean',
        'is_locked' => 'boolean',
        'detail' => 'array',
        'is_manually_modified' => 'boolean',
    ];

    // relations
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    protected static function booted(): void
    {
        static::updated(function ($model) {
            // Only trigger when is_locked changed from false to true
            if ($model->is_locked && $model->getOriginal('is_locked') === false) {
                // Get related student and latest rapor for current year/semester
                $student = $model->student;
                $tahunAjaran = $model->tahun_ajaran;
                $semester = $model->semester;
                $periode = $model->periode;

                // Get or create rapor for this student, tahun_ajaran, semester (pakai relasi rapor terbaru)
                $rapor = $student->rapor;
                // Jika tidak ada rapor yang cocok, lewati saja
                if (! $rapor || $rapor->tahun_ajaran !== $tahunAjaran || $rapor->semester !== $semester) {
                    return;
                }

                // Ambil semua examination student di tahun/semester/periode sama dan sudah dikunci
                $exams = $student->examinations()
                    ->where('tahun_ajaran', $tahunAjaran)
                    ->where('semester', $semester)
                    ->where('periode', $periode)
                    ->where('is_locked', true)
                    ->get();

                // completed_juz = semua juz yang ada di examination
                $completedJuz = $exams->pluck('juz')->unique()->values()->all();
                // scores per juz
                $juzScores = $exams->mapWithKeys(fn ($exam) => [$exam->juz => $exam->score])->all();
                // avg score (atau cara lain sesuai konvensi, pakai mean)
                $avgScore = $exams->avg('score');

                $rapor->pas_completed_juz = $completedJuz;
                $rapor->pas_juz_scores = $juzScores;
                $rapor->pas_score = $avgScore;
                $rapor->save();
            }
        });
    }

    public function penguji(): BelongsTo
    {
        return $this->belongsTo(Penguji::class, 'penguji_id');
    }

    public function mistakes()
    {
        return $this->hasMany(\App\Models\Tahfidz\Mistake::class, 'examination_id');
    }

    public function scopeCurrentYearSemester(Builder $query): void
    {
        $query->where('tahun_ajaran', app(GeneralSettings::class)->tahun_ajaran)
            ->where('semester', app(GeneralSettings::class)->semester);
    }

    public function scopeLockedForCurrentYearSemester(Builder $query): void
    {
        $query->where('tahun_ajaran', app(GeneralSettings::class)->tahun_ajaran)
            ->where('semester', app(GeneralSettings::class)->semester)
            ->where('is_locked', true);
    }
}
