<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model implements HasName
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'nsm',
        'npsn',
        'jenjang',
        'alamat',
        'telepon',
        'logo',
        'rdm_id',
        'rdm_db',
        'alias',
        'fullname',
        'institution_id',
    ];

    public function getFilamentName(): string
    {
        return $this->nama;
    }

    public function kalenderHafalans(): HasMany
    {
        return $this->hasMany(\App\Models\Tahfidz\KalenderHafalan::class);
    }

    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class);
    }

    public function students()
    {
        return $this->hasManyThrough(
            \App\Models\Student::class,
            \App\Models\Classroom::class,
            "school_id", // Foreign key on Classroom table...
            "classroom_id", // Foreign key on TeachingDistribution table...
            "id", // Local key on School table...
            "id", // Local key on Classroom table...
        );
    }
}
