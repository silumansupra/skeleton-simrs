<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="320" alt="Laravel Logo">
</p>

<h1 align="center">SIMRS Dashboard Skeleton</h1>

<p align="center">
  Starter kit dashboard untuk Sistem Informasi Manajemen Rumah Sakit berbasis Laravel 13
</p>

<p align="center">
  <a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
  <a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Laravel Version"></a>
  <img src="https://img.shields.io/badge/PHP-8.2+-blue" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/Tailwind_CSS-4.x-38bdf8" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/Alpine.js-3.x-77c1d2" alt="Alpine.js">
  <a href="LICENSE"><img src="https://img.shields.io/badge/license-MIT-green" alt="License MIT"></a>
</p>

---

## Tentang Proyek

**SIMRS Dashboard Skeleton** adalah starter kit siap pakai untuk membangun dashboard Sistem Informasi Manajemen Rumah Sakit (SIMRS) dengan Laravel 13. Proyek ini menyediakan fondasi core system — autentikasi, manajemen pengguna, kontrol menu, hak akses berbasis role, dan audit log — sehingga tim pengembang bisa langsung fokus membangun modul bisnis (rawat inap, rawat jalan, farmasi, kasemix, dll) tanpa memulai dari nol.

### Fitur Utama

- **Autentikasi lengkap** — Login, logout, lupa password, reset password, lockout otomatis setelah 5x percobaan gagal
- **Manajemen Pengguna** — CRUD user dengan soft delete, assign multi-role, toggle aktif/nonaktif, reset lock
- **Manajemen Menu & Submenu** — CRUD hierarkis dua level (parent → child), toggle aktif dengan cascade ke submenu
- **Hak Akses Berbasis Role** — Permission bitmask (0/1/3/7) per role per menu; superadmin bypass otomatis
- **Audit Log** — Pencatatan otomatis setiap aksi CRUD dengan data before/after, IP address, dan user agent
- **UI Modern** — Sidebar collapsible, toast notification, confirm modal, responsive — tanpa build step

---

## Stack Teknologi

| Layer       | Teknologi                                      |
|-------------|------------------------------------------------|
| Backend     | Laravel 13, PHP 8.2+                           |
| Frontend    | Tailwind CSS (Play CDN), Alpine.js 3.x         |
| Database    | MySQL / MariaDB                                |
| Icons       | Remix Icons 4.x                                |
| Auth        | Session-based (custom, tanpa Breeze/Jetstream) |

> **Catatan:** Proyek ini menggunakan Tailwind Play CDN untuk kemudahan development. Untuk production, disarankan beralih ke Vite + Tailwind CLI.

---

## Struktur Permission

Permission menggunakan sistem bitmask sederhana yang mudah diperluas:

| Nilai | Akses                              |
|-------|------------------------------------|
| `0`   | Tidak ada akses                    |
| `1`   | Lihat (read only)                  |
| `3`   | Lihat + Tambah + Edit              |
| `7`   | Full access (termasuk hapus)       |

Role `superadmin` selalu mendapat permission `7` di semua menu tanpa perlu konfigurasi.

---

## Instalasi

### Prasyarat

- PHP 8.2+
- Composer
- MySQL / MariaDB
- Web server (Laragon / XAMPP / Nginx)

### Langkah Setup

**1. Clone repositori**
```bash
git clone https://github.com/username/simrs-dashboard.git
cd simrs-dashboard
```

**2. Install dependensi**
```bash
composer install
```

**3. Konfigurasi environment**
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` sesuaikan database:
```env
APP_NAME="SIMRS"
APP_URL=http://simrs.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=simrs
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file
```

**4. Jalankan migration dan seeder**
```bash
php artisan migrate
php artisan db:seed
```

**5. Publish pagination (Tailwind style)**
```bash
php artisan vendor:publish --tag=laravel-pagination
```

**6. Akses aplikasi**

Buka `http://simrs.test` (Laragon) atau jalankan:
```bash
php artisan serve
```

---

## Akun Default

| Field    | Value              |
|----------|--------------------|
| Username | `superadmin`       |
| Password | `Admin@1234`       |
| Role     | Super Administrator |

> ⚠️ Segera ganti password setelah login pertama.

---

## Struktur Direktori

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── BaseController.php
│   │   ├── DashboardController.php
│   │   └── Pengaturan/
│   │       ├── AkunController.php
│   │       ├── AksesController.php
│   │       ├── AuditLogController.php
│   │       ├── MenuController.php
│   │       └── UserController.php
│   └── Middleware/
│       ├── CheckAuth.php
│       └── CheckPermission.php
├── Models/
│   ├── AuditLog.php
│   ├── Menu.php
│   ├── Role.php
│   ├── RoleMenuAccess.php
│   ├── User.php
│   └── UserMenuOverride.php
database/
├── migrations/
└── seeders/
    └── DatabaseSeeder.php
resources/
└── views/
    ├── auth/
    ├── dashboard/
    ├── errors/
    ├── layouts/
    └── pengaturan/
        ├── akun/
        ├── akses/
        ├── audit-log/
        ├── menu/
        └── user/
routes/
└── web.php
```

---

## Cara Menggunakan Middleware Permission di Route

```php
// Hanya bisa lihat
Route::get('/laporan', ...)->middleware('permission:laporan.index');

// Bisa tambah dan edit
Route::post('/laporan', ...)->middleware('permission:laporan.index,3');

// Full access termasuk hapus
Route::delete('/laporan/{id}', ...)->middleware('permission:laporan.index,7');
```

## Cara Mencatat Audit Log

```php
use App\Models\AuditLog;

AuditLog::catat(
    aksi:        'create_pasien',
    modul:       'rawat_jalan',
    tabel:       'pasien',
    record_id:   (string) $pasien->id,
    before:      null,
    after:       $pasien->toArray(),
    keterangan:  "Registrasi pasien baru: {$pasien->nama}",
);
```

---

## Roadmap

- [x] Autentikasi (login, logout, lupa password, reset password)
- [x] Manajemen pengguna & role
- [x] Manajemen menu & submenu
- [x] Hak akses berbasis role (bitmask)
- [x] Audit log
- [x] Layout dashboard (sidebar, navbar, toast, confirm modal)
- [ ] Modul rawat jalan
- [ ] Modul rawat inap
- [ ] Modul farmasi
- [ ] Modul kasemix / INA-CBG
- [ ] Integrasi BPJS VClaim & SatuSehat FHIR
- [ ] Multi-database connection (SIMRS legacy)
- [ ] Export laporan (PDF, Excel)

---

## Kontribusi

Pull request dan issue sangat disambut. Untuk perubahan besar, buka issue terlebih dahulu untuk mendiskusikan yang ingin diubah.

1. Fork repositori
2. Buat branch fitur (`git checkout -b fitur/nama-fitur`)
3. Commit perubahan (`git commit -m 'feat: tambah modul X'`)
4. Push ke branch (`git push origin fitur/nama-fitur`)
5. Buat Pull Request

---

## Lisensi

Proyek ini dilisensikan di bawah [MIT License](LICENSE).

---

<p align="center">
  Dibuat untuk ekosistem SIMRS Indonesia &mdash; semoga bermanfaat.
</p>