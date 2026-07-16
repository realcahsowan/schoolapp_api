<?php

namespace App\Models\Tahfidz;

use App\Models\Murobbi;
use Illuminate\Database\Eloquent\Model;

class JournalSummary extends Model
{
    protected $table = 'tahfidz__journal_summaries';

    protected $fillable = [
        'murobbi_id',
        'kalender_id',
        'tahun_ajaran',
        'semester',
        'tanggal',
        'target',
        'target_pagi',
        'target_sore',
        'terisi',
        'terisi_pagi',
        'terisi_sore',
        'input_summary',
        'completed',
        'completed_pagi',
        'completed_sore',
        'hp_only',
    ];

    protected $casts = [
        'input_summary' => 'json',
        'completed' => 'boolean',
        'completed_pagi' => 'boolean',
        'completed_sore' => 'boolean',
        'hp_only' => 'boolean',
        'tanggal' => 'date',
    ];

    // Relasi ke murobbi
    public function murobbi()
    {
        return $this->belongsTo(Murobbi::class, 'murobbi_id');
    }

    // Relasi ke kalender hafalan
    public function kalender()
    {
        return $this->belongsTo(KalenderHafalan::class, 'kalender_id');
    }

    /**
     * Scope: filter journals by current tahun_ajaran & semester from GeneralSettings.
     */
    public function scopeCurrentYearSemester($query): void
    {
        $settings = app(\App\Settings\GeneralSettings::class);
        $query->where('tahun_ajaran', $settings->tahun_ajaran)
            ->where('semester', $settings->semester);
    }
}
