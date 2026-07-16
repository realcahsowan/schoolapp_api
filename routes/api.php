<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GeneralSettingsController;
use App\Http\Controllers\Api\QuranController;
use App\Http\Controllers\Api\Murobbi\DashboardController;
use App\Http\Controllers\Api\Murobbi\StudentController;
use App\Http\Controllers\Api\Murobbi\JournalController;
use App\Http\Controllers\Api\Murobbi\CalendarController;
use App\Http\Controllers\Api\Murobbi\PeriodicAssessmentController;
use App\Http\Controllers\Api\Murobbi\ProfileController;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // General
    Route::get('/general-settings', [GeneralSettingsController::class, 'index']);
    Route::get('/quran/surah', [QuranController::class, 'surah']);

    // Murobbi routes
    Route::prefix('murobbi')->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/dashboard/journal-detail/{tanggal}', [DashboardController::class, 'getJournalDetail']);

        // Students
        Route::get('/students', [StudentController::class, 'index']);
        Route::get('/students/search', [StudentController::class, 'search']);
        Route::get('/students/{student}', [StudentController::class, 'show']);
        Route::get('/students/{student}/journals', [StudentController::class, 'journals']);
        Route::get('/students/{student}/memorization-summaries', [StudentController::class, 'memorizationSummaries']);

        // Journals
        Route::get('/journals', [JournalController::class, 'index']);
        Route::get('/journals/create-data', [JournalController::class, 'createData']);
        Route::post('/journals', [JournalController::class, 'store']);
        Route::get('/journals/{journal}', [JournalController::class, 'show']);
        Route::get('/journals/{journal}/edit', [JournalController::class, 'edit']);
        Route::put('/journals/{journal}', [JournalController::class, 'update']);
        Route::delete('/journals/{journal}', [JournalController::class, 'destroy']);

        // Calendar
        Route::get('/calendar', [CalendarController::class, 'index']);
        Route::get('/calendar/{tanggal}', [CalendarController::class, 'show']);

        // Periodic Assessments
        Route::get('/periodic-assessments', [PeriodicAssessmentController::class, 'index']);
        Route::get('/periodic-assessments/create-data', [PeriodicAssessmentController::class, 'createData']);
        Route::post('/periodic-assessments', [PeriodicAssessmentController::class, 'store']);
        Route::get('/periodic-assessments/{assessment}/edit', [PeriodicAssessmentController::class, 'edit']);
        Route::put('/periodic-assessments/{assessment}', [PeriodicAssessmentController::class, 'update']);
        Route::delete('/periodic-assessments/{assessment}', [PeriodicAssessmentController::class, 'destroy']);

        // Profile
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::post('/profile/signature', [ProfileController::class, 'uploadSignature']);
        Route::put('/profile/password', [ProfileController::class, 'updatePassword']);
    });

    // Penguji Tahfidz routes
    Route::prefix('penguji-tahfidz')->middleware('auth:sanctum')->group(function () {
        Route::get('/dashboard', App\Http\Controllers\Api\PengujiTahfidz\DashboardController::class);
        Route::get('/students', [App\Http\Controllers\Api\PengujiTahfidz\StudentController::class, 'index']);
        Route::get('/students/{id}', [App\Http\Controllers\Api\PengujiTahfidz\StudentController::class, 'show']);
        Route::get('/examinations/{id}', [App\Http\Controllers\Api\PengujiTahfidz\ExaminationController::class, 'show']);
        Route::post('/examinations/{id}/not-submitted', [App\Http\Controllers\Api\PengujiTahfidz\ExaminationController::class, 'setNotSubmitted']);
        Route::put('/examinations/{id}/unlock', [App\Http\Controllers\Api\PengujiTahfidz\ExaminationController::class, 'unlock']);
        Route::get('/examinations/{id}/edit', App\Http\Controllers\Api\PengujiTahfidz\EditExaminationController::class);
        Route::put('/proses-pas', App\Http\Controllers\Api\PengujiTahfidz\ProsesPasController::class);
    });

    // Guardian routes
    Route::prefix('guardian')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Api\Guardian\DashboardController::class, 'index']);

        Route::get('/journals', [App\Http\Controllers\Api\Guardian\JournalController::class, 'index']);
        Route::get('/journals/{journal}', [App\Http\Controllers\Api\Guardian\JournalController::class, 'show']);

        Route::get('/student-profile', [App\Http\Controllers\Api\Guardian\StudentProfileController::class, 'index']);

        Route::get('/profile', [App\Http\Controllers\Api\Guardian\ProfileController::class, 'show']);
        Route::put('/profile', [App\Http\Controllers\Api\Guardian\ProfileController::class, 'update']);
        Route::put('/profile/password', [App\Http\Controllers\Api\Guardian\ProfileController::class, 'updatePassword']);
        Route::delete('/profile', [App\Http\Controllers\Api\Guardian\ProfileController::class, 'destroy']);

        Route::get('/rapor-tahfidz/{id}', App\Http\Controllers\Api\Guardian\RaporTahfidzController::class);
    });
});
