<?php

namespace App\Observers;

use App\Models\Tahfidz\KalenderHafalan;
use Illuminate\Support\Facades\Artisan;

class KalenderHafalanObserver
{
    /**
     * Handle the KalenderHafalan "created" event.
     */
    public function created(KalenderHafalan $kalenderHafalan): void
    {
        // Jalankan command tahfidz:generate-kurikulum-hafalan setelah data baru ditambahkan
        Artisan::call('tahfidz:generate-kurikulum-hafalan');
    }

    /**
     * Jika perlu untuk updated event juga, duplikasikan method ini
     */
    public function updated(KalenderHafalan $kalenderHafalan): void
    {
        Artisan::call('tahfidz:generate-kurikulum-hafalan');
    }
}
