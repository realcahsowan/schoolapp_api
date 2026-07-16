<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guardian extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'nik',
        'tempat_lahir',
        'tanggal_lahir',
        'gender',
        'alamat',
        'telepon',
        'file_foto',
        'agama',
        'pendidikan',
        'pekerjaan',
        'relation_type',
        'relation_status',
        'is_alive',
        'modifed_by_owner',
        'telepon_verified_at',
        'telepon_verification_code',
        'telepon_verification_code_expired_at',
    ];

    public function schools()
    {
        return $this->belongsToMany(School::class, 'guardian_school');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'guardian_student');
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }
}
