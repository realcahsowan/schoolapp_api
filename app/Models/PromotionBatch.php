<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotionBatch extends Model
{
    protected $fillable = [
        'tahun_ajaran',
        'tahun_ajaran_asal',
        'classrooms',
        'completed',
        'school_id',
    ];

    protected $casts = [
        'classrooms' => 'array',
        'completed' => 'boolean',
        'school_id' => 'integer',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
