<?php

namespace App\Models\Tahfidz;

use Illuminate\Database\Eloquent\Model;
use App\Models\School;
use App\Models\Employee;
use App\Models\Student;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Penguji extends Model
{
    use HasFactory;
    protected $table = 'tahfidz__pengujis';

    protected $fillable = [
        'school_id',
        'employee_id',
        'nama',
        'tahun_ajaran',
        'semester',
        'gender',
        'total_students',
        'percentage',
    ];

    // Relationship to School
    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    // Relationship to Employee
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    // Relationship to Student (many-to-many)
    public function students()
    {
        return $this->belongsToMany(
            Student::class,
            'tahfidz__penguji_student', // nama table pivot yang benar
            'penguji_id',
            'student_id'
        )->withPivot('tahun_ajaran', 'semester', 'periode')
        ->using(PengujiStudent::class);
    }

    protected static function booted()
    {
        //
    }

    /**
     * Scope a query to only include current year and semester.
     */
    public function scopeCurrentYearSemester($query)
    {
        (new \App\Scopes\CurrentYearSemesterScope)->apply($query, $this);
        return $query;
    }
}
