<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'SchoolApp') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
            background: #f5f5f4;
            color: #1b1b18;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .card {
            max-width: 480px;
            width: 100%;
            background: #fff;
            border-radius: 1rem;
            padding: 2.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,.08);
            text-align: center;
        }
        .logo {
            font-size: 2rem;
            font-weight: 700;
            color: #f53003;
            margin-bottom: .25rem;
        }
        .tagline {
            color: #706f6c;
            font-size: .875rem;
            margin-bottom: 1.5rem;
        }
        .links {
            display: flex;
            flex-direction: column;
            gap: .75rem;
            margin-bottom: 1.5rem;
        }
        .links a {
            display: block;
            padding: .75rem 1rem;
            border-radius: .5rem;
            border: 1px solid #e5e5e4;
            color: #1b1b18;
            text-decoration: none;
            font-weight: 500;
            font-size: .875rem;
            transition: background .15s;
        }
        .links a:hover {
            background: #fafaf9;
        }
        .links a small {
            display: block;
            font-weight: 400;
            color: #706f6c;
            font-size: .75rem;
            margin-top: .125rem;
        }
        .footer {
            font-size: .75rem;
            color: #a1a09a;
            border-top: 1px solid #e5e5e4;
            padding-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">SchoolApp</div>
        <p class="tagline">Aplikasi Manajemen Hafalan Santri (Tahfidz)</p>

        <div class="links">
            <a href="{{ $guardianappUrl }}" target="_blank" rel="noopener">
                Aplikasi Web Wali Santri
                <small>Portal wali santri untuk memantau hafalan</small>
            </a>

            <a href="{{ $mentorappUrl }}" target="_blank" rel="noopener">
                Aplikasi Web Murobbi
                <small>Portal murobbi untuk mengelola jurnal hafalan</small>
            </a>

            <a href="/administrasi-khusus">
                Administrasi Khusus
                <small>Portal panel Tata Usaha &amp; Admin Tahfidz</small>
            </a>

        </div>

        <div class="footer">
            Laravel {{ app()->version() }} &middot; schoolapp
        </div>
    </div>
</body>
</html>
