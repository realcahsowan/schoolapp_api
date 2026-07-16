<?php

namespace App\Models\Tahfidz;

use App\Models\School;
use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{
    protected $table = 'tahfidz__configurations';

    protected $fillable = [
        'name',
        'payload',
        'locked',
        'school_id',
    ];

    protected $casts = [
        'payload' => 'array',
        'locked' => 'boolean',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
