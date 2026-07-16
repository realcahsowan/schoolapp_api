<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'alias',
        'level',
        'rombel',
        'jurusan_id',
        'tingkat_id',
        'kurikulum_id',
        'tahun_ajaran',
        'history',
        'is_promoted',
        'employee_id',
        'school_id',
    ];

    protected $casts = [
        'history' => 'array',
        'is_promoted' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Classroom $classroom) {
            if ($classroom->nama && $classroom->school_id) {
                $schoolAlias = School::where('id', $classroom->school_id)->value('alias');
                $classroom->alias = $classroom->nama . '-' . ($schoolAlias ?? '');
            }
        });
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
        return $this->hasMany(Student::class, 'classroom_id');
    }

    public function scopeCurrentYear($query)
    {
        $settings = app(\App\Settings\GeneralSettings::class);
        return $query->where('tahun_ajaran', $settings->tahun_ajaran);
    }
}
