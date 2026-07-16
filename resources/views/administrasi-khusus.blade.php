<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Administrasi Khusus - {{ config('app.name', 'SchoolApp') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
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
        .container {
            max-width: 640px;
            width: 100%;
            text-align: center;
        }
        h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: .25rem;
        }
        .subtitle {
            color: #706f6c;
            font-size: .875rem;
            margin-bottom: 1.5rem;
        }
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .card {
            display: block;
            padding: 1.5rem 1rem;
            border-radius: .75rem;
            border: 1px solid #e5e5e4;
            background: #fff;
            text-decoration: none;
            transition: box-shadow .15s, transform .15s;
        }
        .card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,.1);
            transform: translateY(-2px);
        }
        .card .title {
            font-size: 1rem;
            font-weight: 600;
            display: block;
            margin-bottom: .375rem;
        }
        .card .desc {
            font-size: .75rem;
            color: #706f6c;
        }
        .user-info {
            font-size: .8125rem;
            color: #706f6c;
            margin-bottom: 1.5rem;
        }
        .actions {
            display: flex;
            gap: .75rem;
            justify-content: center;
        }
        .actions a, .actions button {
            padding: .5rem 1.25rem;
            border-radius: 9999px;
            font-size: .8125rem;
            font-weight: 500;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: background .15s;
        }
        .btn-logout {
            background: #f53003;
            color: #fff;
        }
        .btn-logout:hover {
            background: #d42a00;
        }
        .btn-password {
            background: #e5e5e4;
            color: #1b1b18;
        }
        .btn-password:hover {
            background: #d4d4d3;
        }
        .footer {
            margin-top: 2rem;
            font-size: .75rem;
            color: #a1a09a;
            border-top: 1px solid #e5e5e4;
            padding-top: 1rem;
        }
        .home-link {
            color: #2563eb;
            font-size: .8125rem;
            text-decoration: none;
        }
        .home-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Administrasi Khusus</h1>
        <p class="subtitle">Pilih panel administrasi yang akan dikelola</p>

        @if ($user)
            <div class="user-info">
                {{ $user->name }} &middot; {{ ucfirst($user->role) }}
            </div>
        @endif

        <div class="grid">
            @foreach ($cards as $card)
                <a href="{{ $card['url'] }}" class="card" target="_blank" rel="noopener">
                    <span class="title">{{ $card['title'] }}</span>
                    <span class="desc">{{ $card['desc'] }}</span>
                </a>
            @endforeach
        </div>

        <div class="actions">
            <a href="/" class="home-link">&larr; Beranda</a>
            <form method="POST" action="{{ route('logout') }}" style="display:inline">
                @csrf
                <button type="submit" class="btn-logout">Logout</button>
            </form>
        </div>

        <div class="footer">
            SchoolApp &middot; Laravel {{ app()->version() }}
        </div>
    </div>
</body>
</html>
