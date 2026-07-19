<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - {{ config('app.name', 'SchoolApp') }}</title>
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
            max-width: 400px;
            width: 100%;
            background: #fff;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,.08);
        }
        .logo {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1c1917;
            text-align: center;
            margin-bottom: .25rem;
        }
        .logo img {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            object-fit: cover;
            margin-bottom: .5rem;
        }
        .tagline {
            text-align: center;
            color: #706f6c;
            font-size: .8125rem;
            margin-bottom: 1.5rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            font-size: .8125rem;
            font-weight: 500;
            margin-bottom: .375rem;
            color: #1b1b18;
        }
        input {
            width: 100%;
            padding: .625rem .75rem;
            border: 1px solid #e5e5e4;
            border-radius: .5rem;
            font-size: .875rem;
            font-family: inherit;
            outline: none;
            transition: border-color .15s;
        }
        input:focus {
            border-color: #f53003;
        }
        .error {
            color: #dc2626;
            font-size: .75rem;
            margin-top: .25rem;
        }
        .alert {
            background: #fee2e2;
            color: #991b1b;
            font-size: .8125rem;
            padding: .75rem;
            border-radius: .5rem;
            margin-bottom: 1rem;
        }
        .checkbox {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin-bottom: 1rem;
        }
        .checkbox input {
            width: auto;
        }
        .checkbox label {
            margin: 0;
            font-size: .8125rem;
            color: #706f6c;
        }
        button {
            width: 100%;
            padding: .625rem;
            background: #f53003;
            color: #fff;
            border: none;
            border-radius: .5rem;
            font-size: .875rem;
            font-weight: 500;
            font-family: inherit;
            cursor: pointer;
            transition: background .15s;
        }
        button:hover {
            background: #d42a00;
        }
        .footer {
            margin-top: 1.5rem;
            text-align: center;
            font-size: .75rem;
            color: #a1a09a;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">
            <img src="{{ asset('img/school-logo.png') }}" alt="Logo">
            <div>{{ config('app.name', 'SchoolApp') }}</div>
        </div>
        <p class="tagline">Masuk ke administrasi khusus</p>

        @if ($errors->any())
            <div class="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input id="password" type="password" name="password" required autocomplete="current-password">
            </div>
            <div class="checkbox">
                <input id="remember" type="checkbox" name="remember">
                <label for="remember">Ingat saya</label>
            </div>
            <button type="submit">Masuk</button>
        </form>

        <div class="footer">
            {{ config('app.name', 'SchoolApp') }} &middot; Laravel {{ app()->version() }}
        </div>
    </div>
</body>
</html>
