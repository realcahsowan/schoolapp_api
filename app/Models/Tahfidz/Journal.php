<?php

namespace App\Models\Tahfidz;

use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    protected $table = 'tahfidz__journals';

    protected $fillable = [
        'murobbi_id',
        'student_id',
        'kalender_id',
        'tahun_ajaran',
        'semester',
        'year',
        'month',
        'week',
        'tanggal',
        'kehadiran',
        'is_terlambat',
        'jenis_izin',
        'keterangan_izin_lainnya',
        'keterangan_sakit',
        'status',
        'detail_capaian',
        'detail_extra',
        'detail_khusus',
        'pelanggaran',
        'score_detail',
        'is_melanggar',
        'is_hp_only',
        'score',
        'waktu',
        'catatan',
    ];

    protected $casts = [
        'detail_capaian' => 'array',
        'detail_extra' => 'array',
        'detail_khusus' => 'array',
        'pelanggaran' => 'array',
        'score_detail' => 'array',
        'is_terlambat' => 'boolean',
        'is_melanggar' => 'boolean',
        'is_hp_only' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(\App\Models\Student::class, 'student_id');
    }

    public function murobbi()
    {
        return $this->belongsTo(\App\Models\Murobbi::class, 'murobbi_id');
    }

    public function kalenderHafalan()
    {
        return $this->belongsTo(KalenderHafalan::class, 'kalender_id');
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
