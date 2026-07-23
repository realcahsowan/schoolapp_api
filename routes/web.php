<?php

use App\Http\Controllers\AdministrasiKhususController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\RaporTahfidzController;
use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\Route;

Route::get('/', function (GeneralSettings $settings) {
    return view('welcome', [
        'guardianappUrl' => $settings->guardianapp_url,
        'mentorappUrl' => $settings->mentorapp_url,
    ]);
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/administrasi-khusus', [AdministrasiKhususController::class, 'index']);
    Route::get('/rapor-tahfidz/{id}', RaporTahfidzController::class)->name('rapor-tahfidz');
});

Route::middleware(['auth', 'admin.position'])->group(function () {
    Route::get('/data-check', [\App\Http\Controllers\DataCheckController::class, 'index'])->name('data-check');
    Route::post('/data-check', [\App\Http\Controllers\DataCheckController::class, 'check'])->name('data-check.post');
});
