<?php

namespace App\Http\Controllers;

class AdministrasiKhususController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $cards = [
            [
                'url' => '/admin',
                'title' => 'Admin',
                'desc' => 'Panel Admin Sekolah',
                'color' => 'bg-rose-50 text-rose-600',
            ],
            [
                'url' => '/tata-usaha',
                'title' => 'Tata Usaha',
                'desc' => 'Panel Tata Usaha',
                'color' => 'bg-stone-100 text-stone-800',
            ],
            [
                'url' => '/admin-tahfidz',
                'title' => 'Admin Tahfidz',
                'desc' => 'Panel Admin Tahfidz',
                'color' => 'bg-amber-50 text-amber-700',
            ],
        ];

        return view('administrasi-khusus', compact('cards', 'user'));
    }
}
