<?php

namespace App\Models\Tahfidz;

use Illuminate\Database\Eloquent\Model;
use App\Models\School;

class KalenderHafalan extends Model
{
    protected $table = 'tahfidz__kalender_hafalans';

    protected $fillable = [
        'school_id',
        'tahun_ajaran',
        'semester',
        'year',
        'month',
        'week',
        'day',
        'tanggal',
        'hp_summary',
        'hs_summary',
        'is_hp_only',
        'is_weekly_examination',
        'is_disabled',
    ];

    protected $casts = [
        'tanggal' => 'date:Y-m-d',
        'hp_summary' => 'json',
        'hs_summary' => 'json',
        'is_hp_only' => 'boolean',
        'is_weekly_examination' => 'boolean',
        'is_disabled' => 'boolean',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function getHpSummaryForInfolistAttribute(): array
    {
        $summary = $this->hp_summary ?? [];
        $result = [];
        foreach ($summary as $kelas => $details) {
            $result[] = [
                'kelas' => $kelas,
                'details' => $details,
            ];
        }
        return $result;
    }

    public function getHsSummaryForInfolistAttribute(): array
    {
        $summary = $this->hs_summary ?? [];
        $result = [];
        foreach ($summary as $kelas => $details) {
            $result[] = [
                'kelas' => $kelas,
                'details' => $details,
            ];
        }
        return $result;
    }
}
