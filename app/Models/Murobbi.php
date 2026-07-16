<?php

namespace App\Models;

use App\Scopes\CurrentYearSemesterScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;

class Murobbi extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $table = 'tahfidz__murobbis';

    protected $fillable = [
        'employee_id',
        'school_id',
        'nama',
        'nama_pendek',
        'gender',
        'tahun_ajaran',
        'semester',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new CurrentYearSemesterScope());
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'tahfidz__student_murobbi')
            ->using(\App\Models\StudentMurobbi::class)
            ->withPivot(['category', 'program', 'is_active'])
            ->withTimestamps();
    }

    public function journals()
    {
        return $this->hasMany(\App\Models\Tahfidz\Journal::class, 'murobbi_id');
    }

    public function journalSummaries()
    {
        return $this->hasMany(\App\Models\Tahfidz\JournalSummary::class, 'murobbi_id');
    }

    /**
     * Relasi ke PenilaianPeriodik.
     */
    public function penilaianPeriodiks()
    {
        return $this->hasMany(\App\Models\Tahfidz\PenilaianPeriodik::class, 'murobbi_id');
    }

    /**
     * Relasi ke MemberMuwashalatAyat.
     */
    public function memberMuwashalatAyats()
    {
        return $this->hasMany(\App\Models\Tahfidz\MemberMuwashalatAyat::class, 'murobbi_id');
    }

    /**
     * Relasi ke JournalPerformance.
     */
    public function journalPerformances()
    {
        return $this->hasMany(\App\Models\Tahfidz\JournalPerformance::class, 'murobbi_id');
    }

    /**
     * Get the user's most recent order.
     */
    public function latestJournalSummary(): HasOne
    {
        return $this->hasOne(\App\Models\Tahfidz\JournalSummary::class)->latestOfMany();
    }

    public function scopeWhereHasStudents(Builder $query): Builder
    {
        return $query->has('students');
    }
}
