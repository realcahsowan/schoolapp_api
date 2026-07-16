<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dormitory extends Model
{
    use HasFactory;

    protected $fillable = [
        'institution_id',
        'name',
        'capacity',
        'rooms',
        'is_full',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'dormitory_employee');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'dormitory_student');
    }
}
