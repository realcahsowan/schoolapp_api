<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    use HasFactory;

    protected $table = 'employee_educations';
    protected $fillable = [
        'employee_id',
        'nama_lembaga',
        'lokasi_lembaga',
        'strata',
        'jenjang',
        'fakultas',
        'jurusan',
        'tahun_masuk',
        'tahun_lulus',
        'nomor_induk',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
