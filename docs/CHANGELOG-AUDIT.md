# Dokumen Serah Terima - Update & Perbaikan Sistem
**Project:** Aplikasi Manajemen & Workflow Branding UMKM  
**Tanggal:** 24 Mei 2026  
**Vendor:** Garapan Dev  
**Branch:** `fix/audit-and-refactor` → merged ke `main`

---

## 1. Informasi Teknis

| Item | Detail |
|------|--------|
| Framework | Laravel 11 + Filament 3.3 |
| PHP | 8.2 |
| Database | MySQL 8.0 |
| Server | VPS 129.212.231.5 (Docker) |
| Domain | https://hanz.vanila.app |
| Repository | https://github.com/dejez30jr/project-brandumkm |

---

## 2. Kondisi Sebelum Pengerjaan

Sistem sudah berjalan dengan fitur dasar per role. Terdapat beberapa bug dan ketidaksesuaian dengan PRD yang dilaporkan:

- Widget KPI tidak sinkron dengan data aktual
- Export Excel/PDF field tidak lengkap, text terpotong
- Tombol approve/reject error 500 di popup
- Backup management gagal (mysqldump tidak tersedia di container)
- Validasi ukuran panel tidak memblokir step berikutnya
- Form user management tidak redirect setelah save
- Duplikasi tombol di halaman terbranding
- Image upload tanpa kompresi (membebani storage)

---

## 3. Daftar Pekerjaan yang Dilakukan

### 3.1 Bug Fixes (12 item)

| No | Issue | File & Line | Status |
|----|-------|-------------|--------|
| 1 | Widget "Design Sudah Direvisi" tidak update | `EditUmkmDesign.php:19-28` | ✅ Fixed |
| 2 | Error 500 create UMKM (kota_id null) | `UmkmResource.php:173` | ✅ Fixed |
| 3 | Tombol Approve/Reject error nested modal | `UmkmResource.php:927-960` | ✅ Fixed |
| 4 | Duplikasi tombol "Lihat" di Terbranding | `UmkmTerbrandingResource.php:122` | ✅ Fixed |
| 5 | Form user tidak redirect setelah save | `UserResource/Pages/CreateUser.php:14` | ✅ Fixed |
| 6 | Validasi area < 1.5 M² tidak block next | `UmkmResource.php:449-466` | ✅ Fixed |
| 7 | KPI "Perlu di-Design" tampil 0 | `SummaryStatsWidget.php:183` | ✅ Fixed |
| 8 | KPI "Perlu di-Branding" tampil 0 | `SummaryStatsWidget.php:58` | ✅ Fixed |
| 9 | Backup gagal dump database | `BackupPage.php` (full rewrite) | ✅ Fixed |
| 10 | Tombol "Buat" muncul di Pemasangan Stiker | `UmkmStikerResource.php:23-25` | ✅ Fixed |
| 11 | KPI filter URL mismatch dengan data | `SummaryStatsWidget.php` (multiple) | ✅ Fixed |
| 12 | Widget tidak auto-refresh | `SummaryStatsWidget.php:17` | ✅ Fixed |

### 3.2 Fitur Baru (5 item)

| No | Fitur | File | Keterangan |
|----|-------|------|------------|
| 1 | Tombol "Lihat Design" + Download | `UmkmStikerResource.php:126-175` | Team Pasang bisa download file design untuk printing |
| 2 | KPI "Perlu Di-review Client" | `SummaryStatsWidget.php:130-134` | Card UMKM pending, redirect ke filter |
| 3 | KPI "Design Perlu Di-review" | `SummaryStatsWidget.php:167-173` | Hanya tampil di Client |
| 4 | Filter "Approved" custom | `UmkmResource.php:687-706` | Filter semua status selain pending & rejected |
| 5 | Image compression otomatis | `UmkmResource.php:483-570`, `UmkmStikerResource.php:58-93` | Max 1200px, 5MB foto, 30MB video |

### 3.3 Audit Export (PRD Section 5.2)

| Item | Sebelum | Sesudah |
|------|---------|---------|
| Excel text wrap | Tidak ada | `setWrapText(true)` semua cell |
| Excel column width | Auto (terpotong) | Custom per kolom |
| Excel header | Plain | Bold + amber background |
| Excel field coverage | Partial | Semua field PRD |
| Excel file path | Raw path | Full URL |
| Excel "Approved By" | User ID | Nama user |
| PDF field | Minimal (5 kolom) | 15 kolom termasuk GPS, personalia |
| PDF text | Terpotong | Word-wrap CSS |
| Export filter | 3 opsi | 4 opsi (per PIC, Desainer, Kota, Raw) |

### 3.4 Chart "Progres UMKM Per Kota"

| Sebelum | Sesudah |
|---------|---------|
| 3 status (approved, pending, rejected) | 6 kategori stacked bar |
| Query N+1 per kota | Single `selectRaw` per kota |

### 3.5 Backup Management (Rewrite)

| Sebelum | Sesudah |
|---------|---------|
| Pakai `mysqldump` (gagal di container) | Laravel native |
| Output: SQL dump | Output: Excel + JSON + file per UMKM |
| Struktur flat | Struktur folder per UMKM |

Struktur ZIP hasil backup:
```
backup_umkm_all_2026-05-24.zip
├── umkm_1_NamaUsaha/
│   ├── foto/
│   ├── video/
│   ├── design/
│   └── pemasangan/
├── data/
│   ├── data_umkm.xlsx
│   └── data_umkm.json
```

---

## 4. File yang Dimodifikasi

### Core Application
```
app/Exports/UmkmExport.php
app/Filament/Pages/BackupPage.php
app/Filament/Resources/UmkmResource.php
app/Filament/Resources/UmkmDesignResource.php
app/Filament/Resources/UmkmDesignResource/Pages/CreateUmkmDesign.php
app/Filament/Resources/UmkmDesignResource/Pages/EditUmkmDesign.php
app/Filament/Resources/UmkmStikerResource.php
app/Filament/Resources/UmkmTerbrandingResource.php
app/Filament/Resources/UserResource.php
app/Filament/Resources/UserResource/Pages/CreateUser.php
app/Filament/Resources/UserResource/Pages/EditUser.php
app/Filament/Resources/UmkmResource/Widgets/SummaryStatsWidget.php
app/Filament/Resources/UmkmResource/Widgets/UmkmPerluDesignTableWidget.php
app/Filament/Resources/UmkmResource/Widgets/UmkmTerbrandingTableWidget.php
app/Filament/Resources/AdminResource/Widgets/UmkmChartWidget.php
app/Models/Umkm.php
app/Models/UmkmDesign.php
app/Observers/UmkmDesignObserver.php
app/Observers/UmkmObserver.php
app/Providers/Filament/AdminPanelProvider.php
app/Services/NotifikasiService.php
```

### Views & Templates
```
resources/views/exports/umkm-pdf.blade.php
resources/views/filament/forms/components/get-location-button.blade.php
```

### Database
```
database/migrations/2026_05_24_000001_add_prd_fields.php
database/seeders/KotaSeeder.php
```

### Infrastructure
```
Dockerfile
docker-compose.yml
docker/nginx.conf
docker/php.ini
.dockerignore
.gitignore
```

### Tests
```
tests/Feature/UmkmLifecycleTest.php
tests/Feature/NotifikasiFlowTest.php
tests/Unit/Models/UmkmTest.php
tests/Unit/Observers/UmkmDesignObserverTest.php
tests/Unit/Observers/UmkmObserverTest.php
tests/Unit/Services/NotifikasiServiceTest.php
```

---

## 5. Migration yang Perlu Dijalankan

```bash
php artisan migrate --force
```

Migration baru: `2026_05_24_000001_add_prd_fields.php`
- Tambah kolom `tanggal_pasang` (date) di tabel `umkms`
- Tambah kolom `nama_team_pasang` (string) di tabel `umkms`
- Tambah kolom `nama_desainer` (string) di tabel `umkm_designs`
- Update enum `status` di tabel `umkms` (tambah: `menunggu_didesain`, `waiting_installation`, `revision`, `installation_completed`, `terbranding_final`)

---

## 6. Deployment

```bash
cd /opt/umkm-branding
docker compose up -d --build app
docker compose exec app php artisan migrate --force
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

---

## 7. Catatan Penting

1. **File `AdminPanelProvider.php`** — di server ada duplikat di `app/Filament/Resources/AdminPanelProvider.php` yang harus dihapus. File yang benar ada di `app/Providers/Filament/AdminPanelProvider.php`.

2. **Status workflow** — setelah UMKM di-approve, status langsung berubah ke `menunggu_didesain` (bukan tetap `approved`). Ini sesuai PRD section 4.

3. **Image compression** — semua upload foto sekarang auto-resize max 1200x1200px. Estimasi hemat storage ~90% per foto.

4. **Nested modal limitation** — Filament v3 tidak support action modal di dalam modal. Tombol approve/reject di popup detail menggunakan direct action (approve) dan JS prompt (reject) sebagai workaround.

5. **Export eager-loading** — semua export query sudah include `with(['kota', 'submittedBy', 'umkmDesign', 'approvedBy'])` untuk menghindari N+1 query.
