<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $tahun_ajaran;
    public int $semester;
    public array $years;
    public array $kurikulum;
    public array $tingkat;
    public array $jurusan;
    public array $roles;
    public string $password;
    public array $jabatans;
    public array $achievement_conversion;
    public array $jenis_izin;
    public string $guardianapp_url;
    public string $mentorapp_url;

    public static function group(): string
    {
        return 'general';
    }
}
