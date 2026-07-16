<?php

namespace App\Models\Tahfidz;

use Illuminate\Database\Eloquent\Model;

class MemorizationSummary extends Model
{
    protected $table = 'tahfidz__memorization_summaries';

    protected $fillable = [
        'student_id',
        'tahun_ajaran',
        'semester',
        'periode',
        'awal_periode',
        'akhir_periode',
        'total_halaman',
        'total_juz',
        'total_surat',
        'total_ayat',
        'detail_halaman',
        'detail_surat',
        'ringkasan',
        'kurikulum', // tambahkan field ini
    ];

    protected $casts = [
        'detail_halaman' => 'array',
        'detail_surat' => 'array',
        'ringkasan' => 'array',
        'kurikulum' => 'array', // tambahkan cast ini
    ];

    // Relasi ke student (jika ada model Student)
    public function student()
    {
        return $this->belongsTo(\App\Models\Student::class, 'student_id');
    }

    protected static function booted()
    {
        static::created(function ($model) {
            if ($model->periode === 'semesterly') {
                \App\Jobs\Tahfidz\RecordCompletedJuzzes::dispatch($model);
            }
        });

        static::updated(function ($model) {
            if ($model->periode === 'semesterly') {
                \App\Jobs\Tahfidz\RecordCompletedJuzzes::dispatch($model);
            }
        });
    }

    /**
     * Scope: filter journals by current tahun_ajaran & semester from GeneralSettings.
     */
    public function scopeCurrentYearSemester($query): void
    {
        $settings = app(\App\Settings\GeneralSettings::class);
        $query->where('tahun_ajaran', $settings->tahun_ajaran)
            ->where('semester', $settings->semester);
    }
}
