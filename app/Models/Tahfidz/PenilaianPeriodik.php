<?php

namespace App\Models\Tahfidz;

use App\Scopes\CurrentYearSemesterScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ScopedBy(CurrentYearSemesterScope::class)]
class PenilaianPeriodik extends Model
{
    protected $table = 'tahfidz__penilaian_periodik';


    protected $fillable = [
        'student_id',
        'murobbi_id',
        'tahun_ajaran',
        'semester',
        'tanggal',
        'kehadiran',
        'jenis_izin',
        'keterangan_izin_lainnya',
        'target',
        'juz_map',
        'pages_map',
        'detail',
        'score',
    ];

    protected $casts = [
        'target' => 'array',
        'juz_map' => 'array',
        'pages_map' => 'array',
        'detail' => 'array',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Student::class, 'student_id');
    }


    protected static function booted()
    {
        static::created(function (self $model) {
            $model->updatePeriodicScore();
        });
        static::updated(function (self $model) {
            $model->updatePeriodicScore();
        });
    }

    /**
     * Update periodic_score pada Rapor sesuai formula: sum(score)/jumlahPelaksanaanPeriodik dari konfigurasi sekolah.
     */
    public function updatePeriodicScore(): void
    {
        $student = $this->student;
        if (! $student) {
            return;
        }
        $tahunAjaran = $this->tahun_ajaran;
        $semester = $this->semester;

        // Ambil rapor terkait
        $rapor = \App\Models\Tahfidz\Rapor::query()
            ->where('student_id', $student->id)
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->first();
        if (! $rapor) {
            return;
        }
        // Hitung total skor periodik
        $totalScore = self::query()
            ->where('student_id', $student->id)
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->sum('score');
        // Ambil jumlahPelaksanaanPeriodik dari konfigurasi sekolah
        $school = $student->school;
        if (! $school) {
            return;
        }
        $config = \App\Models\Tahfidz\Configuration::query()
            ->where('school_id', $school->id)
            ->where('name', 'jumlahPelaksanaanPeriodik')
            ->first();
        $jumlahPelaksanaanPeriodik = $config?->payload ?? null;
        if (! $jumlahPelaksanaanPeriodik || (int) $jumlahPelaksanaanPeriodik === 0) {
            $rapor->periodic_score = null;
        } else {
            $rapor->periodic_score = $totalScore / (int) $jumlahPelaksanaanPeriodik;
        }
        $rapor->save();
    }

    public function murobbi(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Murobbi::class, 'murobbi_id');
    }
}
