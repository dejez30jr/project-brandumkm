# Dokumentasi Project — UMKM Branding Gerobak

## Daftar Dokumen

| Dokumen | Isi |
|---------|-----|
| [DEPLOYMENT.md](./DEPLOYMENT.md) | Setup VPS, Docker, SSL, dan perintah operasional |
| [GPS-WEBVIEW-SETUP.md](./GPS-WEBVIEW-SETUP.md) | Konfigurasi GPS permission untuk WebView APK |

## Tech Stack

- **Backend:** Laravel 11, PHP 8.2
- **Admin Panel:** Filament 3.3
- **Database:** MySQL 8.0
- **Server:** Nginx + PHP-FPM (via Docker)
- **Frontend Mobile:** WebView APK (WebIntoApp)

## Akun Default (Production)

Seeder: `AdminSeeder`

| Role | Email | Password |
|------|-------|----------|
| admin | admin@brandumkm.com | password |
| client | client@brandumkm.com | password |

## Akun Testing (Semua Role)

Seeder: `TestSeeder`

| Role | Email | Password |
|------|-------|----------|
| admin | admin@test.com | password |
| client | client@test.com | password |
| pic_lapangan | pic.jogja@test.com | password |
| pic_lapangan | pic.bandung@test.com | password |
| design | design@test.com | password |
| team_pasang | pasang@test.com | password |

Untuk menjalankan TestSeeder:
```bash
php artisan db:seed --class=TestSeeder
```

## Menjalankan Tests

```bash
php artisan test
```

55 tests, 126 assertions — mencakup models, observers, services, dan lifecycle.
