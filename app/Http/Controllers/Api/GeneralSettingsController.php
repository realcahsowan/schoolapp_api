<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Settings\GeneralSettings;

class GeneralSettingsController extends Controller
{
    public function index()
    {
        $settings = app(GeneralSettings::class);
        
        return response()->json([
            'tahun_ajaran' => $settings->tahun_ajaran,
            'semester' => $settings->semester,
            'years' => $settings->years,
            'jenis_izin' => $settings->jenis_izin,
        ]);
    }
}
