<?php

namespace App\Traits;

use App\Settings\GeneralSettings;
use Illuminate\Support\Arr;

trait SekolahTrait
{
    public static function getJenjangOptions(): array
    {
        return [
            'dasar' => 'SD / MI / Paket A',
            'menengah' => 'SMP / MTs / Paket B',
            'atas' => 'SMA / MA / Paket C'
        ];
    }

    public static function getKelasLevelOptions(string $jenjang): array
    {
        $levels = [
            'dasar' => range(1, 6),
            'menengah' => range(7, 9),
            'atas' => range(10, 12)
        ];

        return Arr::get($levels, $jenjang, []);
    }
}
