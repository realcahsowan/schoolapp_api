<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule) {
        $tahunAjaran = app(\App\Settings\GeneralSettings::class)->tahun_ajaran;
        $semester = app(\App\Settings\GeneralSettings::class)->semester;

        // Jadwal harian 23:30 untuk create-memorization-summaries (weekly)
        \Illuminate\Support\Facades\Cache::forget('memorization-summary-week');
        $today = \Illuminate\Support\Carbon::today();
        $week = \App\Models\Tahfidz\KalenderHafalan::where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->where('tanggal', $today)
            ->value('week') ?? 1;
        $schedule->command("memorization:create-summaries {$tahunAjaran} {$semester} weekly {$week}")
            ->dailyAt('23:30');

        // Jadwal jurnal:summary setiap hari jam 06:50 dan 19:50
        $schedule->command("journal:summary --tahun_ajaran={$tahunAjaran} --semester={$semester}")
            ->dailyAt('06:50');
        $schedule->command("journal:summary --tahun_ajaran={$tahunAjaran} --semester={$semester}")
            ->dailyAt('19:50');
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        $middleware->trustProxies(
            at: [
                '173.245.48.0/20',
                '103.21.244.0/22',
                '103.22.200.0/22',
                '103.31.4.0/22',
                '141.101.64.0/18',
                '108.162.192.0/18',
                '190.93.240.0/20',
                '188.114.96.0/20',
                '197.234.240.0/22',
                '198.41.128.0/17',
                '162.158.0.0/15',
                '104.16.0.0/13',
                '104.24.0.0/14',
                '172.64.0.0/13',
                '131.0.72.0/22',
            ],
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
                | Request::HEADER_X_FORWARDED_AWS_ELB
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
