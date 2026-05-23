# Deployment Guide

## Spesifikasi VPS Minimum

| Resource | Minimum | Rekomendasi |
|----------|---------|-------------|
| CPU | 2 vCPU | 4 vCPU |
| RAM | 4 GB | 8 GB |
| Storage | 100 GB SSD | 250 GB SSD |
| OS | Ubuntu 22.04 | Ubuntu 22.04 |
| Bandwidth | Unmetered | Unmetered |

**Estimasi storage:**
- Per UMKM: 5 foto (~20MB) + 1 video (~40MB) + design (~10MB) + stiker (~15MB) = **~85MB**
- 2000 UMKM × 85MB = **~170 GB** file upload
- Database + OS + buffer = ~30 GB
- **Total kebutuhan: ~200 GB**

Rekomendasi: mulai dengan 250 GB SSD, atau gunakan object storage (S3/MinIO) untuk file upload jika ingin hemat disk VPS.

---

## Setup VPS

### 1. Install Docker & Docker Compose

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Docker
curl -fsSL https://get.docker.com | sh
sudo usermod -aG docker $USER

# Install Docker Compose plugin
sudo apt install docker-compose-plugin -y

# Verify
docker --version
docker compose version
```

### 2. Clone Repository

```bash
cd /opt
git clone <repo-url> umkm-branding
cd umkm-branding
```

### 3. Setup Environment

```bash
cp .env.example .env
nano .env
```

Edit nilai berikut:
```env
APP_NAME="UMKM Branding"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=umkm_branding
DB_USERNAME=umkm_user
DB_PASSWORD=<password-kuat-random>

FILESYSTEM_DISK=public
```

### 4. Build & Run

```bash
docker compose up -d --build
```

### 5. Setup Aplikasi (pertama kali)

```bash
# Generate app key
docker compose exec app php artisan key:generate

# Jalankan migration
docker compose exec app php artisan migrate --force

# Seed data awal (14 kota + admin)
docker compose exec app php artisan db:seed --force

# Buat storage link
docker compose exec app php artisan storage:link

# Cache config & routes
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

### 6. Setup SSL (Certbot)

```bash
# Install certbot di host (bukan di container)
sudo apt install certbot -y

# Stop container sementara
docker compose down

# Generate certificate
sudo certbot certonly --standalone -d yourdomain.com

# Setelah dapat cert, update nginx config untuk HTTPS
# Lalu start ulang
docker compose up -d
```

Alternatif: gunakan Cloudflare proxy (gratis) untuk SSL tanpa setup certbot.

---

## Perintah Operasional

| Aksi | Command |
|------|---------|
| Start | `docker compose up -d` |
| Stop | `docker compose down` |
| Rebuild | `docker compose up -d --build` |
| Logs | `docker compose logs -f app` |
| Masuk container | `docker compose exec app sh` |
| Jalankan artisan | `docker compose exec app php artisan <command>` |
| Backup database | `docker compose exec db mysqldump -u root -p umkm_branding > backup.sql` |
| Restore database | `docker compose exec -i db mysql -u root -p umkm_branding < backup.sql` |

---

## Update Deployment

```bash
cd /opt/umkm-branding
git pull origin main
docker compose up -d --build
docker compose exec app php artisan migrate --force
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
```

---

## Monitoring

### Cek status container
```bash
docker compose ps
```

### Cek disk usage (penting karena banyak upload)
```bash
docker system df
du -sh /var/lib/docker/volumes/
```

### Cek logs error
```bash
docker compose exec app tail -f storage/logs/laravel.log
```

---

## Backup Otomatis (Cron)

Tambahkan ke crontab host (`crontab -e`):

```cron
# Backup database setiap hari jam 2 pagi
0 2 * * * cd /opt/umkm-branding && docker compose exec -T db mysqldump -u root -psecret umkm_branding | gzip > /opt/backups/db_$(date +\%Y\%m\%d).sql.gz

# Hapus backup lebih dari 7 hari
0 3 * * * find /opt/backups -name "*.sql.gz" -mtime +7 -delete
```

---

## Catatan Penting

- **SSL wajib aktif** sebelum GPS bisa berfungsi di WebView Android
- **upload_max_filesize** sudah di-set 50MB di docker/php.ini (untuk video 2 menit)
- **client_max_body_size** sudah di-set 55MB di docker/nginx.conf
- Jangan expose port 3306 ke public di production — hapus `ports` di service `db` di docker-compose.yml
