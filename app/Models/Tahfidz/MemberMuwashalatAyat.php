<?php

namespace App\Models\Tahfidz;

use App\Exceptions\TahfidzException;
use App\Models\Murobbi;
use App\Models\Student;
use App\Scopes\CurrentYearSemesterScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[ScopedBy(CurrentYearSemesterScope::class)]
class MemberMuwashalatAyat extends Model
{
    use HasFactory;

    protected $table = 'tahfidz__member_muwashalat_ayats';

    protected $fillable = [
        'student_id',
        'murobbi_id',
        'tahun_ajaran',
        'semester',
        'score',
        'detail',
        'details',
        'summary',
        'pages_map',
    ];

    protected $casts = [
        'pages_map' => 'array',
        'details' => 'array',
        'detail' => 'array',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::saving(function ($model) {
            $student = Student::with(['rapor'])->find($model->student_id);

            if (is_null($student->rapor)) {
                throw new TahfidzException('Student has no tahfidz category and program set.');
            }

            $student->rapor->update(['sa_score' => $model->score]);
        });
    }

    public function murobbi(): BelongsTo
    {
        return $this->belongsTo(Murobbi::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
