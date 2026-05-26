# Brand UMKM — Dokumentasi Project

Sistem manajemen branding gerobak UMKM berbasis web. Dibangun untuk mengelola seluruh siklus hidup proses branding — mulai dari pendataan UMKM di lapangan, approval client, pembuatan desain, hingga pemasangan stiker dan dokumentasi akhir.

---

## Daftar Isi

- [Gambaran Umum](#gambaran-umum)
- [Tech Stack](#tech-stack)
- [Arsitektur Sistem](#arsitektur-sistem)
- [Struktur Folder](#struktur-folder)
- [Database Schema](#database-schema)
- [Role & Hak Akses](#role--hak-akses)
- [Alur Kerja (Lifecycle)](#alur-kerja-lifecycle)
- [Fitur Utama](#fitur-utama)
- [Setup Development](#setup-development)
- [Deployment (Production)](#deployment-production)
- [Perintah Artisan Penting](#perintah-artisan-penting)
- [Testing](#testing)
- [Konfigurasi Tambahan](#konfigurasi-tambahan)

---

## Gambaran Umum

Brand UMKM adalah platform internal yang digunakan untuk mengelola proses branding gerobak UMKM. Sistem ini melibatkan beberapa tim:

1. **PIC Lapangan** — mendata UMKM, mengukur panel gerobak, mengambil foto dan koordinat GPS
2. **Client** — mereview dan menyetujui/menolak data UMKM yang masuk
3. **Tim Desain** — membuat desain stiker berdasarkan data yang sudah diapprove
4. **Team Pasang** — memasang stiker dan mendokumentasikan hasilnya
5. **Admin** — mengelola seluruh user, data, dan monitoring progress

Semua interaksi dilakukan melalui panel admin berbasis Filament 3.

---

## Tech Stack

| Komponen | Teknologi |
|----------|-----------|
| Framework | Laravel 11 |
| Admin Panel | Filament 3.3 |
| Database | MySQL 8.0 |
| PHP | >= 8.2 |
| Frontend Build | Vite 5 + Tailwind CSS 4 |
| Export Excel | Maatwebsite Excel 3.1 |
| Export PDF | Barryvdh DomPDF 3.1 |
| Backup | Spatie Laravel Backup 9.3 |
| Container | Docker (PHP 8.2 FPM Alpine + Nginx + Supervisor) |
| CI | GitHub Actions (PHPUnit) |

---

## Arsitektur Sistem

```
┌─────────────────────────────────────────────────────┐
│                    Browser / Mobile                   │
└──────────────────────────┬──────────────────────────┘
                           │ HTTPS (443)
┌──────────────────────────▼──────────────────────────┐
│                      Nginx                           │
│              (reverse proxy + SSL)                   │
└──────────────────────────┬──────────────────────────┘
                           │ FastCGI (9000)
┌──────────────────────────▼──────────────────────────┐
│                    PHP-FPM 8.2                        │
│            Laravel 11 + Filament 3.3                 │
└──────────────────────────┬──────────────────────────┘
                           │
┌──────────────────────────▼──────────────────────────┐
│                     MySQL 8.0                         │
└─────────────────────────────────────────────────────┘
```

Supervisor menjalankan PHP-FPM dan Nginx dalam satu container. Queue dijalankan secara synchronous (tidak ada worker terpisah).

---

## Struktur Folder

```
brandumkm/
├── app/
│   ├── Exports/            # Export Excel (UmkmExport)
│   ├── Filament/           # Panel admin (Resources, Pages, Widgets)
│   │   ├── Pages/          # Custom pages (BackupPage, CustomLogin)
│   │   └── Resources/      # CRUD resources per entitas
│   ├── Http/Controllers/   # Controller tradisional (PDF export)
│   ├── Jobs/               # Queue jobs (OptimizeUmkmPhoto)
│   ├── Models/             # Eloquent models
│   ├── Notifications/      # Laravel notification class
│   ├── Observers/          # Model observers (Umkm, UmkmDesign)
│   ├── Providers/          # Service providers
│   └── Services/           # Business logic (NotifikasiService)
├── bootstrap/
├── config/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── docker/                 # Konfigurasi Docker (nginx, php.ini, supervisord)
├── docs/                   # Dokumentasi
├── lang/                   # Lokalisasi
├── public/
├── resources/
│   ├── css/
│   ├── js/
│   └── views/              # Blade templates (export, filament custom views)
├── routes/
├── storage/
├── tests/
├── docker-compose.yml
├── Dockerfile
├── composer.json
├── package.json
└── vite.config.js
```

---

## Database Schema

### Tabel Utama

#### `kotas`
Master data kota tempat operasional.

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint | Primary key |
| nama | string | Nama kota (unique) |

#### `users`
Semua pengguna sistem.

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint | Primary key |
| name | string | Nama lengkap |
| email | string | Email login (unique) |
| password | string | Hashed password |
| role | enum | `admin`, `client`, `design`, `pic_lapangan`, `team_pasang` |
| kota_id | FK nullable | Relasi ke kota (untuk PIC Lapangan) |
| is_active | boolean | Status aktif/nonaktif |

#### `umkms`
Data utama UMKM yang akan di-branding.

| Grup Kolom | Isi |
|------------|-----|
| Data Pemilik | nama_pemilik, nama_usaha, alamat_usaha, no_wa, no_rekening, nama_bank, atas_nama_rekening |
| Operasional | jam_buka, jam_tutup, request_text, catatan, radius |
| Geotagging | latitude, longitude, sharelock_url |
| Ukuran Panel | 18 kolom (depan/kanan/kiri × atas/tengah/bawah × width/height) dalam cm |
| Panel M2 | 9 kolom auto-calculated (cm² → m²) + total_area_branding |
| Kriteria | memenuhi_kriteria (boolean, otomatis true jika total >= 1.5 m²) |
| Status | enum lifecycle (lihat bagian Alur Kerja) |
| Foto Survey | foto_depan, foto_kanan, foto_kiri, foto_plang_alfamart, foto_tampak_jauh, video_validasi |
| Design Final | design_final, design_gerobak_depan/kiri/kanan |
| Foto Stiker | stiker_tampak_depan/kanan/kiri, foto_wide |
| Pemasangan | tanggal_pasang, nama_team_pasang |
| Relasi | kota_id (FK), submitted_by (FK ke users), approved_by (FK nullable) |

#### `umkm_designs`
Riwayat desain per UMKM (bisa multi-versi).

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| umkm_id | FK | UMKM yang didesain |
| designer_id | FK | User designer |
| nama_desainer | string | Nama desainer (denormalisasi) |
| file_path | string | Path file desain utama |
| gerobak_depan/kiri/kanan | string | Path mockup per sisi |
| status | enum | `pending`, `approved`, `revision_needed`, `revised` |
| catatan_revisi | text | Catatan dari client jika perlu revisi |
| versi | integer | Nomor versi desain |
| approved_at | timestamp | Waktu approval |
| approved_by | FK | User yang approve |

#### `after_brandings`
Dokumentasi foto setelah branding selesai.

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| umkm_id | FK | UMKM terkait |
| file_path | string | Path foto |
| keterangan | text | Deskripsi |
| uploaded_by | FK | User yang upload |

#### `notifikasis`
Sistem notifikasi internal.

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| user_id | FK | Penerima notifikasi |
| judul | string | Judul notifikasi |
| pesan | text | Isi pesan |
| tipe | string | Kategori (umkm_baru, perlu_design, dll) |
| notifiable_type/id | morph | Polymorphic ke model terkait |
| is_read | boolean | Status baca |

---

## Role & Hak Akses

### Admin
- Akses penuh ke semua resource
- Kelola user (CRUD)
- Lihat dashboard summary per kota
- Akses halaman backup
- Menerima log notifikasi dari semua aktivitas

### Client
- Review dan approve/reject UMKM yang masuk
- Review desain (approve atau minta revisi)
- Export data ke Excel/PDF
- Single session enforcement (login baru akan logout session lama)

### PIC Lapangan
- Input data UMKM baru (survey lapangan)
- Upload foto gerobak dan koordinat GPS
- Hanya melihat data UMKM yang dia submit sendiri

### Design (Tim Desain)
- Melihat UMKM yang sudah diapprove dan perlu didesain
- Upload desain dan mockup gerobak
- Menerima notifikasi jika desain perlu revisi

### Team Pasang
- Melihat UMKM yang desainnya sudah diapprove
- Upload foto stiker terpasang
- Input tanggal pemasangan dan nama team

---

## Alur Kerja (Lifecycle)

```
┌──────────┐     ┌──────────┐     ┌──────────────────┐     ┌───────────┐
│  pending  │────▶│ approved │────▶│ menunggu_didesain │────▶│ designing │
└──────────┘     └──────────┘     └──────────────────┘     └─────┬─────┘
      │                                                          │
      │ rejected                                                 ▼
      ▼                                                  ┌───────────────┐
┌──────────┐                                             │ design_review │
│ rejected │                                             └───────┬───────┘
└──────────┘                                                     │
                                              ┌──────────────────┼──────────────────┐
                                              ▼                                     ▼
                                    ┌─────────────────┐                  ┌─────────────────────┐
                                    │ revision_needed │                  │   design_approved    │
                                    └────────┬────────┘                  └──────────┬──────────┘
                                             │                                      │
                                             ▼                                      ▼
                                    ┌──────────┐                         ┌─────────────────────┐
                                    │ revision │                         │ waiting_installation │
                                    └──────────┘                         └──────────┬──────────┘
                                                                                    │
                                                                                    ▼
                                                                        ┌────────────────────────┐
                                                                        │ installation_completed  │
                                                                        └───────────┬────────────┘
                                                                                    │
                                                                                    ▼
                                                                            ┌──────────┐
                                                                            │  branded  │
                                                                            └─────┬────┘
                                                                                  │
                                                                                  ▼
                                                                        ┌───────────────────┐
                                                                        │ terbranding_final │
                                                                        └───────────────────┘
```

### Penjelasan Transisi

| Dari | Ke | Trigger |
|------|----|---------|
| pending | approved | Client menyetujui |
| pending | rejected | Client menolak (wajib isi alasan) |
| approved | menunggu_didesain | Otomatis via Observer |
| menunggu_didesain | designing | Designer mulai mengerjakan |
| designing | design_review | Designer upload desain |
| design_review | design_approved | Client approve desain |
| design_review | revision_needed | Client minta revisi |
| revision_needed | revision | Designer upload revisi |
| design_approved | waiting_installation | Otomatis via Observer |
| waiting_installation | installation_completed | Team pasang upload foto stiker |
| installation_completed | branded | Admin/Client konfirmasi |
| branded | terbranding_final | Finalisasi akhir |

---

## Fitur Utama

### 1. Perhitungan Otomatis Luas Panel (m²)
Saat PIC Lapangan mengisi ukuran panel (width × height dalam cm), sistem otomatis menghitung:
- Luas per panel dalam m² (width × height / 10000)
- Total area branding (jumlah semua panel)
- Flag `memenuhi_kriteria` jika total >= 1.5 m²

### 2. Geotagging
Setiap UMKM menyimpan koordinat GPS (latitude/longitude) dan URL sharelock untuk validasi lokasi.

### 3. Optimasi Foto (Background Job)
Setiap foto yang diupload akan diproses oleh job `OptimizeUmkmPhoto`:
- Kompresi JPEG/PNG/WebP ke quality 75%
- Dijalankan via queue (3x retry, backoff 30 detik)

### 4. Sistem Notifikasi
Notifikasi otomatis dikirim ke role yang relevan saat terjadi perubahan status:
- UMKM baru masuk → Client
- UMKM diapprove → Designer + PIC Lapangan
- UMKM ditolak → PIC Lapangan
- Desain baru diupload → Client
- Desain perlu revisi → Designer
- Desain diapprove → Designer + Team Pasang
- Semua event → Admin (sebagai log)

Notifikasi ditampilkan dengan ikon bell + badge di header panel.

### 5. Export Data
- **Excel** — Export lengkap semua kolom UMKM termasuk URL foto, ukuran panel, status, dll
- **PDF** — Export ringkasan data UMKM dalam format landscape A4

### 6. Dashboard & Widget
- Summary stats (total UMKM per status)
- Chart progress branding
- Tabel UMKM yang perlu didesain
- Summary per kota
- Tabel UMKM terbranding

### 7. Backup Database
Halaman backup terintegrasi di panel admin menggunakan Spatie Laravel Backup.

### 8. Single Session (Client)
User dengan role `client` hanya bisa login di satu device. Login baru akan menghapus session sebelumnya.

---

## Setup Development

### Prasyarat

Pastikan sudah terinstall di mesin lokal:

- **PHP >= 8.2** dengan ekstensi:
  - pdo_mysql, mbstring, zip, gd, exif, bcmath, intl, opcache
- **Composer** (versi terbaru)
- **Node.js >= 18** + npm
- **MySQL 8.0** (atau MariaDB 10.6+)
- **Git**

### Langkah Instalasi

```bash
# 1. Clone repository
git clone <repository-url> brandumkm
cd brandumkm

# 2. Install PHP dependencies
composer install

# 3. Install Node dependencies
npm install

# 4. Salin file environment
cp .env.example .env

# 5. Generate application key
php artisan key:generate

# 6. Buat database MySQL
mysql -u root -p -e "CREATE DATABASE brandumkm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 7. Konfigurasi .env
# Sesuaikan bagian database:
#   DB_DATABASE=brandumkm
#   DB_USERNAME=root
#   DB_PASSWORD=<password-kamu>

# 8. Jalankan migrasi
php artisan migrate

# 9. Jalankan seeder (data kota + user default)
php artisan db:seed

# 10. Buat symbolic link storage
php artisan storage:link

# 11. Build frontend assets
npm run build

# 12. Jalankan development server
php artisan serve
```

### Akses Aplikasi

Buka `http://localhost:8000/admin/login`

**Akun Default (dari seeder):**

| Email | Password | Role |
|-------|----------|------|
| admin@test.com | password | Admin |
| client@test.com | password | Client |
| pic.jogja@test.com | password | PIC Lapangan |
| pic.bandung@test.com | password | PIC Lapangan |
| design@test.com | password | Design |
| pasang@test.com | password | Team Pasang |

### Development Mode (Hot Reload)

```bash
# Terminal 1 — Laravel server
php artisan serve

# Terminal 2 — Vite dev server (hot reload CSS/JS)
npm run dev
```

### IDE Helper (Opsional)

Project ini sudah include `barryvdh/laravel-ide-helper` untuk autocomplete di IDE:

```bash
php artisan ide-helper:generate
php artisan ide-helper:models --nowrite
```

---

## Deployment (Production)

### Menggunakan Docker

Project sudah dilengkapi `Dockerfile` dan `docker-compose.yml` untuk deployment.

#### Struktur Container

| Service | Image | Port | Fungsi |
|---------|-------|------|--------|
| app | Custom (PHP 8.2 FPM Alpine) | 80, 443 | Aplikasi Laravel + Nginx |
| db | mysql:8.0 | 3306 | Database |

#### Langkah Deploy

```bash
# 1. Siapkan file .env di server
# Pastikan konfigurasi database sesuai dengan docker-compose

# 2. Siapkan SSL certificate
# Letakkan di /etc/letsencrypt/live/<domain>/

# 3. Build dan jalankan
docker compose up -d --build

# 4. Jalankan migrasi (pertama kali)
docker exec umkm-app php artisan migrate --force

# 5. Jalankan seeder (pertama kali)
docker exec umkm-app php artisan db:seed --force

# 6. Optimasi
docker exec umkm-app php artisan config:cache
docker exec umkm-app php artisan route:cache
docker exec umkm-app php artisan view:cache
```

#### Konfigurasi Docker

**php.ini** (`docker/php.ini`):
- upload_max_filesize: 50M
- post_max_size: 55M
- max_execution_time: 120s
- memory_limit: 256M
- OPcache enabled

**Nginx** (`docker/nginx.conf`):
- SSL termination
- client_max_body_size: 55M
- FastCGI timeout: 120s
- Domain: hanz.vanila.app (sesuaikan)

**Supervisor** (`docker/supervisord.conf`):
- Menjalankan PHP-FPM dan Nginx secara bersamaan

#### Volume Persistent

```yaml
volumes:
  db-data:      # Data MySQL
  app-storage:  # File upload (storage/app/public)
```

---

## Perintah Artisan Penting

```bash
# Migrasi database
php artisan migrate

# Rollback migrasi terakhir
php artisan migrate:rollback

# Jalankan seeder
php artisan db:seed

# Clear semua cache
php artisan optimize:clear

# Cache untuk production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Buat storage link
php artisan storage:link

# Backup database (via Spatie)
php artisan backup:run --only-db

# Backup lengkap (file + database)
php artisan backup:run

# Jalankan queue worker (jika QUEUE_CONNECTION != sync)
php artisan queue:work

# Generate IDE helper
php artisan ide-helper:generate
php artisan ide-helper:models --nowrite
```

---

## Testing

### Menjalankan Test

```bash
# Semua test
vendor/bin/phpunit

# Test tertentu
vendor/bin/phpunit tests/Feature/UmkmLifecycleTest.php
vendor/bin/phpunit tests/Feature/NotifikasiFlowTest.php
```

### Test yang Tersedia

| File | Cakupan |
|------|---------|
| `tests/Feature/UmkmLifecycleTest.php` | Alur lengkap lifecycle UMKM dari pending sampai terbranding |
| `tests/Feature/NotifikasiFlowTest.php` | Verifikasi notifikasi terkirim ke role yang benar |
| `tests/Unit/Services/` | Unit test service layer |
| `tests/Unit/Models/` | Unit test model logic (kalkulasi m², relasi) |
| `tests/Unit/Observers/` | Unit test observer behavior |

### CI/CD

GitHub Actions menjalankan test otomatis pada:
- Push ke branch `master` atau `*.x`
- Pull request
- Jadwal harian (cron)

Matrix test: PHP 8.2 dan 8.3.

---

## Konfigurasi Tambahan

### Environment Variables Penting

```env
# Aplikasi
APP_NAME="Brand UMKM"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-kamu.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=brandumkm
DB_USERNAME=brandumkm
DB_PASSWORD=<password>

# Queue (sync untuk development, database/redis untuk production)
QUEUE_CONNECTION=sync

# Session
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Filesystem
FILESYSTEM_DISK=local
```

### Konfigurasi Backup

File: `config/backup.php`

- Source: seluruh base_path() kecuali vendor/ dan node_modules/
- Destination: local disk
- Bisa dikonfigurasi ke S3 atau disk lain

### Upload Limit

Jika mengubah batas upload, sesuaikan di tiga tempat:
1. `docker/php.ini` → upload_max_filesize, post_max_size
2. `docker/nginx.conf` → client_max_body_size
3. Filament form validation (di resource masing-masing)

---

## Catatan Pengembangan

### Konvensi Penamaan
- Model: PascalCase singular (`Umkm`, `UmkmDesign`)
- Tabel: snake_case plural (`umkms`, `umkm_designs`)
- Kolom: snake_case (`nama_usaha`, `foto_depan`)
- Status: snake_case (`waiting_installation`, `design_review`)

### Observer Pattern
Logika bisnis utama ada di Observer, bukan di Controller:
- `UmkmObserver` — handle transisi status, dispatch photo optimization
- `UmkmDesignObserver` — handle transisi status desain, update UMKM saat desain diapprove

### Notifikasi
Semua notifikasi dikelola melalui `NotifikasiService` (static methods). Tidak menggunakan Laravel Notification channel bawaan, melainkan menyimpan langsung ke tabel `notifikasis`.

### Auto-Calculate
Perhitungan luas panel dilakukan di model event `saving` (bukan di controller), sehingga konsisten dari manapun data disimpan.

---

## Troubleshooting

### Storage permission error
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Foto tidak muncul
```bash
php artisan storage:link
```
Pastikan `FILESYSTEM_DISK=local` dan foto disimpan di `storage/app/public/`.

### Migration error (enum)
Jika ada error saat alter enum di MySQL, pastikan menggunakan MySQL 8.0+ (bukan MariaDB versi lama yang tidak support ALTER ENUM).

### Queue job tidak jalan
Cek `QUEUE_CONNECTION` di `.env`. Jika `sync`, job langsung dieksekusi. Jika `database`, pastikan worker berjalan:
```bash
php artisan queue:work
```
