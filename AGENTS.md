# SchoolApp API

Laravel 12 REST API untuk sistem manajemen hafalan santri (Tahfidz). Mengkonsumsi database yang sama dengan **schoolapp** (`~/Projects/schoolapp`) dan menyediakan:
- **Filament Panels** — Admin, Admin Tahfidz, Tata Usaha
- **REST API** — untuk **mentorapp_react**, **guardianapp_react**, **penguji-tahfidz**
- **Web Portal** — login + administrasi khusus

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| Framework | Laravel 12 |
| Panel | Filament 3 (v5.6) |
| Auth API | Laravel Sanctum (token-based) |
| Auth Web | Laravel session-based |
| Settings | Spatie Laravel Settings |
| PDF | barryvdh/laravel-dompdf |
| Database | MySQL (sama dengan schoolapp) |
| PHP | ^8.2 |

---

## Struktur Project

```
schoolapp_api/
├── app/
│   ├── Console/Commands/                     # 33 Artisan commands dari schoolapp
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   └── LoginController.php       # Login web
│   │   │   ├── AdministrasiKhususController.php
│   │   │   ├── RaporTahfidzController.php    # Download PDF via web
│   │   │   └── Api/
│   │   │       ├── AuthController.php        # Login/me/logout — include guardian + students
│   │   │       ├── GeneralSettingsController.php
│   │   │       ├── QuranController.php
│   │   │       ├── Guardian/                  # Portal Orang Tua
│   │   │       │   ├── DashboardController.php
│   │   │       │   ├── JournalController.php
│   │   │       │   ├── StudentProfileController.php
│   │   │       │   ├── ProfileController.php
│   │   │       │   └── RaporTahfidzController.php
│   │   │       ├── Murobbi/
│   │   │       │   ├── DashboardController.php
│   │   │       │   ├── StudentController.php
│   │   │       │   ├── JournalController.php
│   │   │       │   ├── CalendarController.php
│   │   │       │   ├── PeriodicAssessmentController.php
│   │   │       │   └── ProfileController.php
│   │   │       └── PengujiTahfidz/
│   │   │           ├── DashboardController.php
│   │   │           ├── StudentController.php
│   │   │           ├── ExaminationController.php
│   │   │           ├── EditExaminationController.php
│   │   │           └── ProsesPasController.php
│   ├── Filament/
│   │   ├── AdminTahfidz/                     # Panel Admin Tahfidz
│   │   │   ├── Pages/                        # 10 custom pages
│   │   │   └── Resources/                    # 7 resources
│   │   ├── Pages/                            # Shared pages (Admin)
│   │   │   ├── GeneralSettingsPage.php
│   │   │   └── KirimPesanWhatsapp.php
│   │   ├── Resources/                        # Shared resources (Admin)
│   │   │   ├── Dormitories/
│   │   │   ├── Employees/
│   │   │   ├── Guardians/
│   │   │   ├── Institutions/
│   │   │   ├── Schools/
│   │   │   └── Users/
│   │   └── TataUsaha/                        # Panel Tata Usaha
│   │       ├── Pages/                        # Promotion page
│   │       └── Resources/                    # 3 resources
│   ├── Models/
│   │   ├── User.php, Employee.php, Murobbi.php, Student.php
│   │   ├── Classroom.php, School.php, Guardian.php
│   │   ├── Dormitory.php, Institution.php
│   │   ├── Position.php, Education.php
│   │   ├── PromotionBatch.php, ImpersonationToken.php
│   │   ├── StudentMurobbi.php
│   │   └── Tahfidz/
│   │       ├── Journal.php, KalenderHafalan.php
│   │       ├── PenilaianPeriodik.php, MemorizationSummary.php
│   │       ├── Examination.php, Rapor.php
│   │       ├── KurikulumHafalan.php, JournalSummary.php
│   │       ├── JournalPerformance.php, MemberMuwashalatAyat.php
│   │       ├── Configuration.php, Penguji.php
│   │       ├── PengujiStudent.php, Mistake.php, CompletedJuz.php
│   ├── Providers/Filament/
│   │   ├── AdminPanelProvider.php
│   │   ├── AdminTahfidzPanelProvider.php
│   │   └── TataUsahaPanelProvider.php
│   ├── Scopes/CurrentYearSemesterScope.php
│   ├── Services/
│   │   ├── GenerateJournalPerformanceService.php
│   │   └── RaporTahfidzService.php
│   ├── Settings/GeneralSettings.php
│   ├── Traits/
│   │   ├── CalendarTrait.php
│   │   ├── HasSuratName.php
│   │   ├── JabatanTrait.php
│   │   ├── KelasTrait.php
│   │   ├── QuranTrait.php
│   │   ├── ReminderTrait.php
│   │   └── SekolahTrait.php
│   ├── Exceptions/TahfidzException.php
│   └── Observers/KalenderHafalanObserver.php
├── bootstrap/providers.php                    # PanelProviders + AppServiceProvider
├── config/
│   ├── cors.php                               # Allow localhost:5173 & 5174
│   └── ...
├── routes/
│   ├── web.php                                # Login, administrasi-khusus, rapor-tahfidz
│   └── api.php                                # REST endpoints
├── resources/
│   └── views/
│       ├── auth/login.blade.php               # Halaman login web
│       ├── administrasi-khusus.blade.php       # Portal panel
│       ├── tahfidz/rapor.blade.php             # Template PDF rapor
│       └── filament/                           # Blade views untuk custom pages
└── database/
    └── migrations/
```

---

## Web Routes

| Method | Path | Deskripsi |
|--------|------|-----------|
| GET | `/` | Welcome page |
| GET | `/login` | Form login web |
| POST | `/login` | Login web (session) |
| POST | `/logout` | Logout web |
| GET | `/administrasi-khusus` | Portal panel (auth required) |
| GET | `/rapor-tahfidz/{id}` | Download PDF rapor (auth required) |

## Filament Panels

| Panel | Path | Tenant | Deskripsi |
|-------|------|--------|-----------|
| Admin | `/admin` | - | Manajemen pengguna, sekolah, asrama, institusi |
| Admin Tahfidz | `/admin-tahfidz/{school}` | School (alias) | Manajemen hafalan, jurnal, penilaian, rapor |
| Tata Usaha | `/tata-usaha/{school}` | School (alias) | Manajemen kelas, siswa, wali murid, kenaikan kelas |

## API Endpoints

Base URL: `http://localhost:8010/api`

### Auth
| Method | Path | Deskripsi |
|--------|------|-----------|
| POST | `/api/login` | Login → token + user + relasi |
| GET | `/api/me` | User info + relasi |
| POST | `/api/logout` | Revoke current token |

### Supporting
| Method | Path | Deskripsi |
|--------|------|-----------|
| GET | `/api/general-settings` | tahun_ajaran, semester, years, jenis_izin |
| GET | `/api/quran/surah` | 114 surah Al-Quran |

### Murobbi (prefix: `/api/murobbi` — 25 endpoints)
Dashboard, Students (CRUD + search), Journals (CRUD), Calendar, Periodic Assessments, Profile

### Penguji Tahfidz (prefix: `/api/penguji-tahfidz` — 8 endpoints)
Dashboard, Students, Examinations, PAS processing

### Guardian (prefix: `/api/guardian` — 9 endpoints)
Dashboard, Journals, Student Profile, Profile Management, Rapor PDF download

---

## Alur Autentikasi

### Web (Filament)
1. Buka `/login` → form login session-based
2. Login → redirect ke `/administrasi-khusus`
3. Dari sana bisa akses Admin `/admin`, Admin Tahfidz `/admin-tahfidz/{school}`, Tata Usaha `/tata-usaha/{school}`

### API (Sanctum)
1. `POST /api/login` → return `token` (Sanctum)
   - Guardian: response includes `guardian` + `students`
   - Murobbi: response includes `employee` + `murobbis`
2. Header `Authorization: Bearer {token}`
3. `POST /api/logout` → revoke token
4. 401 → React redirect ke /login

---

## Model Mapping

| Database Table | Model | Notes |
|---------------|-------|-------|
| `users` | `App\Models\User` | FilamentUser + HasTenants, Sanctum auth |
| `employees` | `App\Models\Employee` | hasMany Murobbi, Position, Education |
| `guardians` | `App\Models\Guardian` | belongsToMany Students |
| `students` | `App\Models\Student` | belongsToMany Guardian, Murobbi, Dormitory, Classroom |
| `classrooms` | `App\Models\Classroom` | belongsTo School |
| `schools` | `App\Models\School` | hasMany Classroom, Filament HasName |
| `dormitories` | `App\Models\Dormitory` | belongsToMany Student |
| `tahfidz__murobbis` | `App\Models\Murobbi` | Global scope: year/semester |
| `tahfidz__journals` | `App\Models\Tahfidz\Journal` | |
| `tahfidz__memorization_summaries` | `App\Models\Tahfidz\MemorizationSummary` | |
| `tahfidz__examinations` | `App\Models\Tahfidz\Examination` | |
| `tahfidz__rapors` | `App\Models\Tahfidz\Rapor` | Global scope: year/semester |
| `tahfidz__configurations` | `App\Models\Tahfidz\Configuration` | |
| `tahfidz__mistakes` | `App\Models\Tahfidz\Mistake` | |
| `tahfidz__pengujis` | `App\Models\Tahfidz\Penguji` | |
| `tahfidz__student_murobbi` | `App\Models\StudentMurobbi` | Pivot, auto-create Rapor |

---

## Catatan Penting

- **Database**: `DB_DATABASE=schoolapp` (MySQL) — sama dengan project schoolapp
- **Session**: SESSION_DRIVER=file (bukan array) agar Livewire CSRF token berfungsi
- **Model User**: implements `FilamentUser` + `HasTenants` — method `canAccessPanel()` mengecek role/posisi
- **PDF Rapor**: `barryvdh/laravel-dompdf` via `RaporTahfidzService` + `resources/views/tahfidz/rapor.blade.php`
- **Global Scope**: `CurrentYearSemesterScope` applied ke model Rapor, Murobbi, PenilaianPeriodik, MemberMuwashalatAyat
- **CORS**: `config/cors.php` mengizinkan `localhost:5173` (mentorapp) dan `localhost:5174` (guardianapp)
- **Settings**: `GeneralSettings` menyimpan tahun ajaran aktif, semester, kurikulum, dll
- **Commands**: 33 artisan commands di `app/Console/Commands/` (dari schoolapp)

---

## Cara Menjalankan

```bash
cd ~/Projects/moved/schoolapp_api
cp .env.example .env
composer install
npm install && npm run build
PATH="/home/taufiq/.config/herd-lite/bin:$PATH" php artisan serve --host=localhost --port=8010
```

- API: `http://localhost:8010/api/`
- Panel Admin: `http://localhost:8010/admin`
- Panel Admin Tahfidz: `http://localhost:8010/admin-tahfidz/{school}`
- Panel Tata Usaha: `http://localhost:8010/tata-usaha/{school}`
