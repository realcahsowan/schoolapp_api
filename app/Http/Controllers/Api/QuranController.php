<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\HasSuratName;

class QuranController extends Controller
{
    use HasSuratName;

    public function surah()
    {
        return response()->json([
            'surah' => $this->getAllSurat(),
        ]);
    }
}
