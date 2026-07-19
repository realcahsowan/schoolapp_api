<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'SchoolApp') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700;plus-jakarta-sans:500,600,700;800" rel="stylesheet" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Instrument Sans', 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
            background: #f5f5f4;
            color: #1c1917;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            overflow: hidden;
        }

        .bento-wrapper {
            max-width: 1100px;
            width: 100%;
        }

        /* Header */
        .header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 0.5rem;
        }
        .header-logo {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            object-fit: cover;
            background: #fff;
            padding: 4px;
            border: 1px solid #e7e5e4;
        }
        .header-text h1 {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: #1c1917;
        }
        .header-text p {
            font-size: 0.8rem;
            color: #78716c;
            font-weight: 500;
            margin-top: 2px;
        }
        .header-desc {
            font-size: 0.8rem;
            color: #78716c;
            line-height: 1.7;
            margin-bottom: 2rem;
            padding-left: calc(56px + 1rem);
        }

        /* Bento Grid */
        .bento-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: auto auto auto;
            gap: 1rem;
        }

        /* Cards */
        .bento-card {
            border-radius: 20px;
            padding: 2rem;
            position: relative;
            overflow: hidden;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            cursor: default;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            border: 1px solid #e7e5e4;
        }
        .bento-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 60px rgba(0,0,0,0.08);
        }

        /* Card variants */
        .card-wali {
            background: #fff;
            grid-row: span 2;
            min-height: 420px;
        }
        .card-wali .card-accent { background: #3b82f6; }

        .card-murobbi {
            background: #fff;
            grid-row: span 2;
            min-height: 420px;
        }
        .card-murobbi .card-accent { background: #22c55e; }

        .card-admin-full {
            background: #fff;
            grid-column: 1 / -1;
            flex-direction: row;
            align-items: center;
            gap: 1.5rem;
            padding: 1.5rem 2rem;
        }
        .card-admin-full .card-accent { background: #ef4444; margin-bottom: 0; }
        .card-admin-full .card-body { flex: 1; }
        .card-admin-full .card-cta { margin-top: 0; }

        /* Card inner */
        .card-accent {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.25rem;
            flex-shrink: 0;
        }
        .card-accent svg {
            width: 24px;
            height: 24px;
            stroke: #fff;
            fill: none;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            letter-spacing: -0.01em;
            margin-bottom: 0.5rem;
            color: #1c1917;
        }
        .card-desc {
            font-size: 0.8rem;
            color: #78716c;
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        /* CTA Button */
        .card-cta {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: auto;
            padding: 0.65rem 1.25rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            width: fit-content;
        }
        .card-cta svg {
            width: 16px;
            height: 16px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }
        .cta-blue { background: #3b82f6; color: #fff; }
        .cta-blue:hover { background: #2563eb; }
        .cta-green { background: #22c55e; color: #fff; }
        .cta-green:hover { background: #16a34a; }
        .cta-red { background: #ef4444; color: #fff; }
        .cta-red:hover { background: #dc2626; }

        /* Illustration area */
        .card-illustration {
            position: absolute;
            bottom: -10px;
            right: -10px;
            width: 180px;
            height: 180px;
            opacity: 0.1;
            pointer-events: none;
        }

        /* Large card illustration */
        .card-wali .card-illustration,
        .card-murobbi .card-illustration {
            width: 240px;
            height: 240px;
            opacity: 0.12;
        }

        /* Feature list for large cards */
        .card-features {
            list-style: none;
            margin-top: 0.5rem;
            margin-bottom: 1rem;
        }
        .card-features li {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.78rem;
            color: #57534e;
            padding: 0.35rem 0;
        }
        .card-features li svg {
            width: 14px;
            height: 14px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            flex-shrink: 0;
        }
        .feat-blue svg { color: #3b82f6; }
        .feat-green svg { color: #22c55e; }

        /* Decorative glow */
        .bento-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(0,0,0,0.04), transparent);
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 2rem;
            font-size: 0.7rem;
            color: #a8a29e;
            font-weight: 500;
        }
        .footer span {
            color: #78716c;
        }

        /* Background grid dots */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: radial-gradient(circle at 1px 1px, rgba(0,0,0,0.04) 1px, transparent 0);
            background-size: 32px 32px;
            pointer-events: none;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .bento-grid {
                grid-template-columns: 1fr;
            }
            .card-wali, .card-murobbi {
                grid-row: span 1;
                min-height: auto;
            }
            .card-illustration {
                width: 140px;
                height: 140px;
            }
            .card-wali .card-illustration,
            .card-murobbi .card-illustration {
                width: 160px;
                height: 160px;
            }
        }
    </style>
</head>
<body>
    <div class="bento-wrapper">
        <!-- Header -->
        <div class="header">
            <img src="{{ asset('img/school-logo.png') }}" alt="SchoolApp Logo" class="header-logo">
            <div class="header-text">
                <h1>SchoolApp</h1>
                <p>Sistem Manajemen Hafalan Santri (Tahfidz)</p>
            </div>
        </div>
        <p class="header-desc">Sistem manajemen hafalan santri berbasis web untuk pondok pesantren. Mengelola jurnal, penilaian, dan rapor tahfidz secara digital.</p>

        <!-- Bento Grid -->
        <div class="bento-grid">

            <!-- Card Wali Santri (Large) -->
            <a href="{{ $guardianappUrl }}" target="_blank" rel="noopener" class="bento-card card-wali">
                <div class="card-accent">
                    <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <h2 class="card-title">Wali Santri</h2>
                <p class="card-desc">Portal khusus orang tua & wali untuk memantau perkembangan hafalan santri secara real-time.</p>

                <ul class="card-features feat-blue">
                    <li>
                        <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        Pantau progres hafalan harian
                    </li>
                    <li>
                        <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        Lihat jurnal & penilaian periodik
                    </li>
                    <li>
                        <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        Download rapor tahfidz
                    </li>
                    <li>
                        <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        Profil santri lengkap
                    </li>
                </ul>

                <span class="card-cta cta-blue">
                    Buka Portal
                    <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </span>

                <!-- Decorative illustration -->
                <svg class="card-illustration" viewBox="0 0 200 200" fill="none">
                    <circle cx="100" cy="70" r="30" stroke="#3b82f6" stroke-width="2" opacity="0.4"/>
                    <path d="M55 160 Q100 120 145 160" stroke="#3b82f6" stroke-width="2" opacity="0.4" fill="none"/>
                    <circle cx="100" cy="70" r="10" fill="#3b82f6" opacity="0.2"/>
                    <path d="M80 60 L100 40 L120 60" stroke="#3b82f6" stroke-width="1.5" opacity="0.25" fill="none"/>
                    <rect x="70" y="100" width="60" height="40" rx="8" stroke="#3b82f6" stroke-width="1.5" opacity="0.2" fill="none"/>
                    <line x1="80" y1="115" x2="120" y2="115" stroke="#3b82f6" stroke-width="1" opacity="0.2"/>
                    <line x1="80" y1="125" x2="110" y2="125" stroke="#3b82f6" stroke-width="1" opacity="0.15"/>
                </svg>
            </a>

            <!-- Card Murobbi (Large) -->
            <a href="{{ $mentorappUrl }}" target="_blank" rel="noopener" class="bento-card card-murobbi">
                <div class="card-accent">
                    <svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/><line x1="8" y1="7" x2="16" y2="7"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                </div>
                <h2 class="card-title">Murobbi</h2>
                <p class="card-desc">Portal pengelolaan hafalan santri untuk murobbi. Kelola jurnal, penilaian, dan monitoring santri.</p>

                <ul class="card-features feat-green">
                    <li>
                        <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        Input & kelola jurnal hafalan
                    </li>
                    <li>
                        <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        Penilaian periodik santri
                    </li>
                    <li>
                        <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        Monitoring kalender hafalan
                    </li>
                    <li>
                        <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        Rekap & statistik kelompok
                    </li>
                </ul>

                <span class="card-cta cta-green">
                    Buka Portal
                    <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </span>

                <!-- Decorative illustration -->
                <svg class="card-illustration" viewBox="0 0 200 200" fill="none">
                    <rect x="40" y="50" width="120" height="80" rx="12" stroke="#22c55e" stroke-width="2" opacity="0.4" fill="none"/>
                    <line x1="60" y1="80" x2="140" y2="80" stroke="#22c55e" stroke-width="1.5" opacity="0.25"/>
                    <line x1="60" y1="95" x2="120" y2="95" stroke="#22c55e" stroke-width="1.5" opacity="0.2"/>
                    <line x1="60" y1="110" x2="100" y2="110" stroke="#22c55e" stroke-width="1.5" opacity="0.15"/>
                    <circle cx="150" cy="50" r="15" stroke="#22c55e" stroke-width="1.5" opacity="0.25" fill="none"/>
                    <path d="M145 50 L150 45 L155 50" stroke="#22c55e" stroke-width="1.5" opacity="0.25" fill="none"/>
                    <circle cx="50" cy="150" r="20" stroke="#22c55e" stroke-width="1" opacity="0.2" fill="none"/>
                    <path d="M100 150 L130 150" stroke="#22c55e" stroke-width="1" opacity="0.15"/>
                    <path d="M100 160 L140 160" stroke="#22c55e" stroke-width="1" opacity="0.1"/>
                </svg>
            </a>

            <!-- Card Admin Panel (Full Width) -->
            <a href="/administrasi-khusus" class="bento-card card-admin-full">
                <div class="card-accent">
                    <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                </div>
                <div class="card-body">
                    <h2 class="card-title">Admin Panel</h2>
                    <p class="card-desc" style="margin-bottom:0;">Akses administrasi khusus: Tata Usaha & Admin Tahfidz.</p>
                </div>
                <span class="card-cta cta-red">
                    Masuk
                    <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </span>
            </a>

        </div>

        <!-- Footer -->
        <div class="footer">
            &copy; {{ date('Y') }} <span>{{ config('app.name', 'SchoolApp') }}</span> &middot; Sistem Manajemen Hafalan Tahfidz
        </div>
    </div>
</body>
</html>
