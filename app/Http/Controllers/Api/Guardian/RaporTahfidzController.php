<?php

namespace App\Http\Controllers\Api\Guardian;

use App\Http\Controllers\Controller;
use App\Models\Tahfidz\Rapor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class RaporTahfidzController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        $service = app(\App\Services\RaporTahfidzService::class);
        $data = $service->generateRaporData($id);
        $student = $data['student'];

        return PDF::loadView('tahfidz.rapor', $data)
            ->setPaper('a4', 'portrait')
            ->stream("rapor_tahfidz_{$student->name}.pdf");
    }
}
