<?php

namespace App\Models\Tahfidz;

use App\Settings\GeneralSettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Mistake extends Model
{
    protected $table = 'tahfidz__mistakes';
    protected $fillable = [
        'student_id',
        'penguji_id',
        'examination_id',
        'tahun_ajaran',
        'semester',
        'juz',
        'page',
        'score',
        'raw_score',
        'detail',
        'is_disabled',
        'is_nulled',
        'is_locked',
        'juz_part', // nullable
        'periode', // nullable
    ];

    protected $casts = [
        'score' => 'float',
        'raw_score' => 'array',
        'detail' => 'array',
        'is_disabled' => 'boolean',
        'is_nulled' => 'boolean',
        'is_locked' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(\App\Models\Student::class);
    }

    public function penguji()
    {
        return $this->belongsTo(\App\Models\Tahfidz\Penguji::class);
    }

    public function examination()
    {
        return $this->belongsTo(\App\Models\Tahfidz\Examination::class);
    }

    public function scopeCurrentYearSemester(Builder $query): void
    {
        $query->where('tahun_ajaran', app(GeneralSettings::class)->tahun_ajaran)
            ->where('semester', app(GeneralSettings::class)->semester);
    }
}
