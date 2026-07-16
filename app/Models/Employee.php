<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'nik',
        'nip',
        'tempat_lahir',
        'tanggal_lahir',
        'gender',
        'alamat',
        'telepon',
        'file_foto',
        'file_signature',
        'institution_id',
        'angkatan_stipi',
        'nuptk',
        'peg_id',
        'golongan_darah',
        'tanggal_mulai_bekerja',
        'status_perkawinan',
        'nama_ayah',
        'nama_ibu',
        'nama_pasangan',
        'jumlah_anak',
        'riwayat_mengajar',
        'pendidikan_terakhir',
    ];

    protected $casts = [
        'riwayat_mengajar' => 'array',
    ];

    public function positions()
    {
        return $this->hasMany(Position::class);
    }

    public function educations()
    {
        return $this->hasMany(Education::class);
    }

    public function dormitories()
    {
        return $this->belongsToMany(\App\Models\Dormitory::class, 'dormitory_employee', 'employee_id', 'dormitory_id')
            ->withPivot('id', 'room', 'is_active');
    }

    public function murobbis()
    {
        return $this->hasMany(Murobbi::class, 'employee_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'employee_id');
    }

    /**
     * Get the active dormitory for the student based on pivot attributes.
     */
    public function getActiveDormitoryAttribute()
    {
        return $this->dormitories()
            ->wherePivot('is_active', true)
            ->first();
    }
}
