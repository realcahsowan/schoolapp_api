<?php

namespace App\Models\Tahfidz;

use App\Models\Student;
use App\Scopes\CurrentYearSemesterScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ScopedBy(CurrentYearSemesterScope::class)]
class Rapor extends Model
{
    protected $table = 'tahfidz__rapors';

    protected $fillable = [
        'student_id',
        'murobbi_employee_id',
        'tahun_ajaran',
        'semester',
        'category',
        'program',
        'periodic_score',
        'sa_score',
        'pas_score',
        'pas_succeed', // boolean
        'pas_has_customized_juz', // boolean
        'pas_juz_map', // json
        'pas_juz_scores', // json
        'pas_completed_juz', // json
        'pas_disabled_juz', // json
        'total_juz_pas',
        'kepala_tahfidz_name',
        'kepala_tahfidz_employee_id',
        'final_score',
        'predikat',
        'notes',
        'lokasi',
        'tanggal', // date
    ];

    protected $casts = [
        'murobbi_employee_id' => 'integer',
        'sa_score' => 'decimal:2',
        'pas_score' => 'decimal:2',
        'pas_succeed' => 'boolean',
        'pas_has_customized_juz' => 'boolean',
        'pas_juz_map' => 'array',
        'pas_juz_scores' => 'array',
        'pas_completed_juz' => 'array',
        'pas_disabled_juz' => 'array',
        'total_juz_pas' => 'integer',
        'kepala_tahfidz_employee_id' => 'integer',
        'final_score' => 'decimal:2',
        'predikat' => 'string',
        'tanggal' => 'date',
    ];

    protected $dates = [
        'tanggal',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
