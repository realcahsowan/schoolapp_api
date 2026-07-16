# SchoolApp API

Laravel 12 REST API untuk sistem manajemen hafalan santri (Tahfidz). Database yang sama dengan **schoolapp** (`DB_DATABASE=schoolapp`), menyediakan:

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
| PDF | DomPDF |
| Database | MySQL (sama dengan schoolapp) |
| PHP | ^8.2 |

---

## Setup

```bash
cd ~/Projects/moved/schoolapp_api
cp .env.example .env
composer install
npm install && npm run build
PATH="/home/taufiq/.config/herd-lite/bin:$PATH" php artisan serve --host=localhost --port=8010
```

## URLs

| URL | Deskripsi |
|-----|-----------|
| `http://localhost:8010` | Welcome page — card shortcut ke Aplikasi Wali Santri & Murobbi |
| `http://localhost:8010/login` | Login web (session) |
| `http://localhost:8010/administrasi-khusus` | Portal panel (auth required) + tombol logout |
| `http://localhost:8010/admin` | Panel Admin |
| `http://localhost:8010/admin-tahfidz/{school}` | Panel Admin Tahfidz |
| `http://localhost:8010/tata-usaha/{school}` | Panel Tata Usaha |
| `http://localhost:8010/api/...` | REST API |

## General Settings (`/admin/settings`)

| Setting | Tipe | Deskripsi |
|---------|------|-----------|
| `tahun_ajaran` | string | Tahun ajaran aktif |
| `semester` | int | Semester aktif |
| `password` | string | Default password untuk akun baru |
| `guardianapp_url` | string | URL eksternal Aplikasi Wali Santri |
| `mentorapp_url` | string | URL eksternal Aplikasi Murobbi |
| `years` | array | Daftar tahun ajaran |
| `roles` | array (KV) | Mapping roles |
| `jabatans` | array (KV) | Mapping jabatan |
| `jenis_izin` | array (KV) | Mapping jenis izin |
| `kurikulum` | array (Repeater) | Daftar kurikulum (nama + alias) |

## Tata Usaha — Fitur Unggulan

### Manajemen Kelas (`/tata-usaha/{school}`)
- **Form Classroom** — Select level (dinamis sesuai jenjang sekolah), rombel (A-Z), jurusan, wali kelas (dari Position), kurikulum
- **Alias otomatis** — terisi `{nama}-{school->alias}` saat simpan via model `saving` event

### Manajemen Siswa
- **Import/Export Excel** via ActionGroup "Data Siswa":
  - Panduan Template Data (modal)
  - Unduh Template Data (Excel)
  - Upload Data Siswa — pilih kelas + file Excel, auto-normalisasi gender, konversi tanggal Excel serial, deduplikasi NIS/Email, auto-create akun User
  - Export Student Data

### Fitur Lain
- **Promosi Kelas** — halaman khusus kenaikan kelas per tahun ajaran

## API Endpoints

| Group | Prefix | Jumlah Route |
|-------|--------|-------------|
| Auth | `/api` | 3 |
| Supporting | `/api` | 2 |
| Murobbi | `/api/murobbi` | 25 |
| Penguji Tahfidz | `/api/penguji-tahfidz` | 8 |
| Guardian | `/api/guardian` | 9 |

## Auth Flow

### Web
1. Buka `/login` → form login session-based
2. Login → redirect ke `/administrasi-khusus`
3. Akses panel sesuai role
4. Logout via tombol di halaman administrasi-khusus

### API (Sanctum)
1. `POST /api/login` → `token` (Sanctum)
   - Guardian: response includes `guardian` + `students`
   - Murobbi: response includes `employee` + `murobbis`
2. Header `Authorization: Bearer {token}` untuk semua request
3. `POST /api/logout` → revoke token
4. 401 → frontend redirect ke /login

## PDF Rapor

Endpoint `/api/guardian/rapor-tahfidz/{id}` mengembalikan file PDF langsung.
Menggunakan `barryvdh/laravel-dompdf` dengan template di `resources/views/tahfidz/rapor.blade.php`.
