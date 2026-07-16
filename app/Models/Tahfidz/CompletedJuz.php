<?php

namespace App\Models\Tahfidz;

use App\Models\Student;
use Illuminate\Database\Eloquent\Model;

class CompletedJuz extends Model
{
    protected $table = 'tahfidz__completed_juz';

    protected $fillable = [
        'student_id',
        'tahun_ajaran',
        'semester',
        'juz_number',
        'completed_at',
    ];

    public $timestamps = false;

    // Relationships (optional, example)
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}