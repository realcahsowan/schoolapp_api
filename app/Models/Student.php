<?php

namespace App\Models;

use App\Settings\GeneralSettings;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'nis',
        'nisn',
        'nik',
        'tempat_lahir',
        'tanggal_lahir',
        'gender',
        'alamat',
        'telepon',
        'anak_ke',
        'jumlah_saudara',
        'sekolah_asal',
        'nomor_ijazah',
        'riwayat_kelas',
        'is_graduated',
        'is_beasiswa',
        'is_active',
        'has_siblings',
        'virtual_account',
        'agama',
        'file_foto',
        'pendidikan',
        'kode_emis',
        'propinsi',
        'kabupaten_kota',
        'kecamatan',
        'kelurahan',
        'kodepos',
        'tingkat_id',
        'classroom_id',
    ];

    protected $casts = [
        'riwayat_kelas' => 'array',
        'is_graduated' => 'boolean',
        'is_beasiswa' => 'boolean',
        'is_active' => 'boolean',
        'has_siblings' => 'boolean',
    ];

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function classrooms()
    {
        return $this->belongsToMany(Classroom::class, 'classroom_student');
    }

    public function school()
    {
        return $this->hasOneThrough(
            School::class,
            Classroom::class,
            'id', // Foreign key on Classroom table...
            'id', // Foreign key on School table...
            'classroom_id', // Local key on Student table...
            'school_id' // Local key on Classroom table...
        );
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'student_id');
    }

    public function murobbis()
    {
        return $this->belongsToMany(Murobbi::class, 'tahfidz__student_murobbi')
            ->using(\App\Models\StudentMurobbi::class)
            ->withPivot(['category', 'program', 'is_active']);
    }

    // If you want the reverse relation
    public function guardians()
    {
        return $this->belongsToMany(Guardian::class, 'guardian_student');
    }

    /**
     * Relationship to Dormitory model.
     */
    public function dormitories()
    {
        return $this->belongsToMany(Dormitory::class, 'dormitory_student')
            ->withPivot('id', 'room', 'is_active', 'tahun_ajaran', 'semester', 'created_at', 'updated_at')
            ->distinct();
    }

    /**
     * Get the active dormitory for the student based on pivot attributes.
     */
    public function getActiveDormitoryAttribute()
    {
        $tahunAjaran = app(GeneralSettings::class)->tahun_ajaran;
        $semester = app(GeneralSettings::class)->semester;

        return $this->dormitories()
            ->wherePivot('is_active', true)
            ->wherePivot('tahun_ajaran', $tahunAjaran)
            ->wherePivot('semester', $semester)
            ->first();
    }

    public function pengujis()
    {
        return $this->belongsToMany(
            \App\Models\Tahfidz\Penguji::class,
            'tahfidz__penguji_student',
            'student_id',
            'penguji_id'
        )->withPivot('tahun_ajaran', 'semester', 'periode')
            ->using(\App\Models\Tahfidz\PengujiStudent::class);
    }

    // accessor
    public function getMurobbiAttribute()
    {
        // Jika relasi murobbis sudah dimuat, ambil yang pertama dengan status active
        if ($this->relationLoaded('murobbis')) {
            return $this->murobbis->firstWhere(
                'pivot.is_active',
                true
            );
        }

        // Jika belum dimuat, jalankan query
        return $this->murobbis()
            ->wherePivot(
                'is_active',
                true
            )
            ->first();
    }

    public function penilaianPeriodik()
    {
        return $this->hasMany(\App\Models\Tahfidz\PenilaianPeriodik::class, 'student_id');
    }

    public function penilaianPeriodiks()
    {
        return $this->hasMany(\App\Models\Tahfidz\PenilaianPeriodik::class, 'student_id');
    }

    public function examinations()
    {
        return $this->hasMany(\App\Models\Tahfidz\Examination::class, 'student_id');
    }

    public function rapors()
    {
        return $this->hasMany(\App\Models\Tahfidz\Rapor::class, 'student_id');
    }

    public function rapor(): HasOne
    {
        return $this->hasOne(\App\Models\Tahfidz\Rapor::class)->latestOfMany();
    }

    /**
     * Get all of the muwashalat ayat records for the student.
     */
    public function memberMuwashalatAyats()
    {
        return $this->hasMany(\App\Models\Tahfidz\MemberMuwashalatAyat::class, 'student_id');
    }

    public function muwashalat(): HasOne
    {
        return $this->hasOne(\App\Models\Tahfidz\MemberMuwashalatAyat::class)->latestOfMany();
    }

    /**
     * Get all Journals associated with the student.
     */
    public function journals()
    {
        return $this->hasMany(\App\Models\Tahfidz\Journal::class, 'student_id');
    }

    /**
     * Get all memorization summaries associated with the student.
     */
    public function memorizationSummaries()
    {
        return $this->hasMany(\App\Models\Tahfidz\MemorizationSummary::class, 'student_id');
    }


    protected static function booted()
    {
        static::saved(function (Student $student) {
            // Only run if classroom_id has changed
            if ($student->wasChanged('classroom_id') && $student->classroom_id) {
                // Set is_active = true for current classroom_id, others false
                $classroomIds = $student->classrooms()->pluck('classroom_id')->toArray();
                $pivotData = [];
                foreach ($classroomIds as $cid) {
                    $pivotData[$cid] = ['is_active' => $cid == $student->classroom_id];
                }
                // Ensure new classroom_id always included
                if (! in_array($student->classroom_id, $classroomIds)) {
                    $pivotData[$student->classroom_id] = ['is_active' => true];
                }
                $student->classrooms()->sync($pivotData, false); // false agar tidak detach
            }
        });
    }

    /**
     * Sync/recalculate PAS, SA, and Periodik scores for all rapors for this student for the given period.
     */
    public function syncRaporsWithExaminations(?string $tahunAjaran = null, ?string $semester = null): void
    {
        $tahunAjaran ??= (app(\App\Settings\GeneralSettings::class)->tahun_ajaran);
        $semester ??= (app(\App\Settings\GeneralSettings::class)->semester);
        // Prioritaskan koleksi relasi yang sudah di-eager load
        $rapors = $this->relationLoaded('rapors')
            ? $this->rapors->where('tahun_ajaran', $tahunAjaran)->where('semester', $semester)
            : \App\Models\Tahfidz\Rapor::where('student_id', $this->id)
                ->where('tahun_ajaran', $tahunAjaran)
                ->where('semester', $semester)
                ->get();
        // examinations
        $allExams = $this->relationLoaded('examinations')
            ? $this->examinations->where('tahun_ajaran', $tahunAjaran)->where('semester', $semester)->where('is_locked', true)
            : collect();
        // member muwashalat ayat
        $muwashalats = $this->relationLoaded('memberMuwashalatAyats')
        ? $this->memberMuwashalatAyats->where('tahun_ajaran', $tahunAjaran)->where('semester', $semester)
            ->whereNotNull('score')
            : collect();
        // penilaian periodik
        $periodiks = $this->relationLoaded('penilaianPeriodik')
            ? $this->penilaianPeriodik->where('tahun_ajaran', $tahunAjaran)->where('semester', $semester)
            : collect();
        foreach ($rapors as $rapor) {
            // Gunakan hasil eager load jika ada
            $examinations = $allExams->isNotEmpty()
                ? $allExams->where('student_id', $rapor->student_id)
                : \App\Models\Tahfidz\Examination::where('student_id', $rapor->student_id)
                    ->where('tahun_ajaran', $tahunAjaran)
                    ->where('semester', $semester)
                    ->where('is_locked', true)
                    ->get();
            $pasJuzScores = [];
            foreach ($examinations as $exam) {
                if ($exam->juz !== null && $exam->score !== null) {
                    $pasJuzScores[$exam->juz] = $exam->score;
                }
            }
            if (! empty($pasJuzScores)) {
                $rapor->pas_juz_scores = $pasJuzScores;
                $rapor->pas_completed_juz = array_keys($pasJuzScores);
                $totalJuzPas = (int) ($rapor->total_juz_pas ?? 0);
                $sumScore = array_sum($pasJuzScores);
                $rapor->pas_score = $totalJuzPas > 0 ? $sumScore / $totalJuzPas : 0;
                $rapor->pas_succeed = $totalJuzPas > 0 && count($rapor->pas_completed_juz) == $totalJuzPas;
            }
            $memberSa = $muwashalats->isNotEmpty()
                ? $muwashalats->whereNotNull('score')->firstWhere('student_id', $rapor->student_id)
                : \App\Models\Tahfidz\MemberMuwashalatAyat::where('student_id', $rapor->student_id)
                    ->where('tahun_ajaran', $rapor->tahun_ajaran)
                    ->where('semester', $rapor->semester)
                    ->whereNotNull('score')
                    ->first();
            $rapor->sa_score = $memberSa?->score ?? null;
            $periodikScores = $periodiks->isNotEmpty()
                ? $periodiks->where('student_id', $rapor->student_id)->pluck('score')->filter(fn($score) => $score !== null)
                : \App\Models\Tahfidz\PenilaianPeriodik::where('student_id', $rapor->student_id)
                    ->where('tahun_ajaran', $rapor->tahun_ajaran)
                    ->where('semester', $rapor->semester)
                    ->pluck('score')->filter(fn($score) => $score !== null);
            $rapor->periodic_score = $periodikScores->count() > 0 ? $periodikScores->avg() : null;
            $rapor->save();
        }
    }
}
