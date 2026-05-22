# PHASE INFRA — Setup Server & Infrastruktur
**Jadwal:** Setelah semua bug fix selesai (pasca PHASE 1-3)  
**Prasyarat:** Akses SSH ke VPS dari client  
**Status:** ⏳ Pending

---

## A. Setup VPS

| # | Task | Detail |
|---|---|---|
| I-01 | Provisioning VPS | Min spec: 2 vCPU, 4GB RAM (IDCloudHost / DigitalOcean ~Rp 100-200rb/bulan) |
| I-02 | Install Nginx | Web server |
| I-03 | Install PHP 8.2 + ekstensi | `php8.2-fpm`, `php8.2-mysql`, `php8.2-gd`, `php8.2-zip`, `php8.2-mbstring`, `php8.2-xml`, `php8.2-curl` |
| I-04 | Install MySQL 8.0 | Database server |
| I-05 | Install Composer | PHP dependency manager |
| I-06 | Konfigurasi Nginx virtual host | Point domain ke `public/` folder Laravel |
| I-07 | Setup firewall (UFW) | Allow port 80, 443, 22 saja |

---

## B. SSL & HTTPS

| # | Task | Detail |
|---|---|---|
| I-08 | Install Certbot | `apt install certbot python3-certbot-nginx` |
| I-09 | Generate SSL certificate | `certbot --nginx -d domain.com` |
| I-10 | Auto-renew SSL | Certbot sudah setup cron otomatis, verifikasi aktif |
| I-11 | Force HTTPS di Laravel | Set `APP_URL=https://domain.com` di `.env` |

> **Catatan:** SSL wajib aktif sebelum GPS bisa berfungsi di browser dan WebIntoApp.

---

## C. Migrasi Database SQLite → MySQL

| # | Task | Detail |
|---|---|---|
| I-12 | Buat database MySQL | `CREATE DATABASE umkm_branding CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;` |
| I-13 | Buat user MySQL | Buat user khusus, jangan pakai root |
| I-14 | Update `.env` production | Ganti `DB_CONNECTION=sqlite` ke `DB_CONNECTION=mysql` + isi kredensial |
| I-15 | Jalankan migration | `php artisan migrate --force` |
| I-16 | Jalankan seeder | `php artisan db:seed --class=AdminSeeder` dan `KotaSeeder` |

---

## D. Deploy Codebase

| # | Task | Command |
|---|---|---|
| I-17 | Clone repository ke VPS | `git clone <repo> /var/www/umkm-branding` |
| I-18 | Install dependencies | `composer install --no-dev --optimize-autoloader` |
| I-19 | Copy `.env` | Copy `.env.example` ke `.env`, isi semua value |
| I-20 | Generate app key | `php artisan key:generate` |
| I-21 | Storage link | `php artisan storage:link` |
| I-22 | Set permission | `chown -R www-data:www-data storage bootstrap/cache` |
| I-23 | Build cache | `php artisan config:cache && route:cache && view:cache` |

---

## E. Konfigurasi Upload File

| # | Task | Detail |
|---|---|---|
| I-24 | Cek `upload_max_filesize` di `php.ini` | Set minimal `20M` untuk support video 15MB |
| I-25 | Cek `post_max_size` di `php.ini` | Set minimal `25M` |
| I-26 | Cek `max_execution_time` | Set minimal `120` detik untuk upload besar |
| I-27 | Konfigurasi Nginx `client_max_body_size` | Set `25M` di nginx config |

---

## F. Backlog Lanjutan (Opsional)

| # | Task | Prioritas |
|---|---|---|
| I-28 | Setup Redis | Untuk cache dan queue notifikasi email | Medium |
| I-29 | Setup Laravel Queue Worker | Agar notifikasi email tidak blocking request | Medium |
| I-30 | Setup Supervisor | Agar queue worker tetap jalan setelah server restart | Medium |
| I-31 | Setup backup otomatis (Spatie Backup) | Sudah ada package-nya, tinggal konfigurasi | Low |

---

## Checklist

- [ ] VPS bisa diakses via SSH
- [ ] Nginx berjalan dan domain mengarah ke app
- [ ] SSL aktif, HTTPS berfungsi
- [ ] Database MySQL terhubung
- [ ] Migration dan seeder berhasil
- [ ] Upload foto/video berfungsi di production
- [ ] GPS berfungsi di browser production (HTTPS)
- [ ] GPS berfungsi di WebIntoApp Android
