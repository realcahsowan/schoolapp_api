<?php

namespace App\Models\Tahfidz;

use App\Models\School;
use App\Settings\GeneralSettings;
use Illuminate\Database\Eloquent\Model;

class KurikulumHafalan extends Model
{
    protected $table = 'tahfidz__kurikulum_hafalans';

    protected $fillable = [
        'school_id',
        'tahun_ajaran',
        'semester',
        'grade',
        'program',
        'detail_hafalan_baru',
        'total_ayat_hafalan_baru',
        'total_surat_hafalan_baru',
        'total_juz_hafalan_baru',
        'detail_hafalan_murojaah',
        'total_ayat_hafalan_murojaah',
        'total_surat_hafalan_murojaah',
        'total_juz_hafalan_murojaah',
    ];

    protected $casts = [
        'detail_hafalan_baru' => 'array',
        'detail_hafalan_murojaah' => 'array',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function scopeCurrentYearSemester($query)
    {
        $settings = app(GeneralSettings::class);
        $tahunAjaran = $settings->tahun_ajaran;
        $semester = $settings->semester;
        return $query->where('tahun_ajaran', $tahunAjaran)
                     ->where('semester', $semester);
    }
}
