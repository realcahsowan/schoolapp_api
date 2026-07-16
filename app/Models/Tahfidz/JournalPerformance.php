<?php

namespace App\Models\Tahfidz;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Murobbi;

class JournalPerformance extends Model
{
    protected $table = 'tahfidz__journal_performances';

    protected $fillable = [
        'murobbi_id',
        'tahun_ajaran',
        'semester',
        'jenis_periode',
        'angka_periode',
        'awal',
        'akhir',
        'total_hari',
        'total_hp_only',
        'target',
        'realisasi',
    ];

    protected $casts = [
        'awal' => 'date',
        'akhir' => 'date',
        'semester' => 'integer',
        'total_hari' => 'integer',
        'total_hp_only' => 'integer',
        'target' => 'integer',
        'realisasi' => 'integer',
    ];

    public function murobbi(): BelongsTo
    {
        return $this->belongsTo(Murobbi::class, 'murobbi_id');
    }

    public function school()
    {
        return $this->hasOneThrough(
            \App\Models\School::class,
            \App\Models\Murobbi::class,
            'id', // Foreign key on Murobbi table
            'id', // Foreign key on School table
            'murobbi_id', // Local key on JournalPerformance
            'school_id' // Local key on Murobbi
        );
    }
}

