<?php

namespace App\Traits;

use App\Settings\GeneralSettings;

trait JabatanTrait
{
    public function getJabatanOptions(): array
    {
        $primaryJabatans = [
            'guru' => 'Guru',
            'wali-kelas' => 'Wali Kelas',
            'tata-usaha' => 'Tata Usaha',
            'kurikulum' => 'Kurikulum',
            'bendahara' => 'Bendahara',
            'kepala-sekolah' => 'Kepala Sekolah'
        ];
        return array_merge($primaryJabatans, app(GeneralSettings::class)->jabatans);
    }
}
