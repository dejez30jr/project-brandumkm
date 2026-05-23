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

## Akun Default

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@brandumkm.com | password |
| Client | client@brandumkm.com | password |

Untuk testing lengkap semua role, jalankan:
```bash
php artisan db:seed --class=TestSeeder
```

## Menjalankan Tests

```bash
php artisan test
```

55 tests, 126 assertions — mencakup models, observers, services, dan lifecycle.
