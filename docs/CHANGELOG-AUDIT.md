# Changelog - Fix & Audit (24 Mei 2026)

## Bug Fixes

### 1. Widget "Design Sudah Direvisi" tidak update setelah designer edit
- **File:** `app/Filament/Resources/UmkmDesignResource/Pages/EditUmkmDesign.php:19-28`
- **Penyebab:** `UmkmDesignObserver::updating()` reset status ke `pending` karena `EditRecord` page tidak set status `revised`
- **Fix:** Tambah `mutateFormDataBeforeSave()` yang set `status = 'revised'` saat designer save

### 2. Widget polling tidak auto-refresh
- **File:** `app/Filament/Resources/UmkmResource/Widgets/SummaryStatsWidget.php:17`
- **Fix:** Ubah `$pollingInterval` dari `null` ke `'30s'`

### 3. Error 500 saat create UMKM (kota_id null)
- **File:** `app/Filament/Resources/UmkmResource.php:173`
- **Penyebab:** Field `kota_id` tidak punya `->required()`
- **Fix:** Tambah `->required()` pada Select kota_id

### 4. Tombol Approve/Reject error 500 di popup detail
- **File:** `app/Filament/Resources/UmkmResource.php:927-960`
- **Penyebab:** Filament v3 tidak support nested modal (infolist action di dalam ViewAction modal)
- **Fix:** Approve tanpa `requiresConfirmation()`, Reject pakai `prompt()` JS native

### 5. Dua tombol "Lihat" di UMKM Terbranding
- **File:** `app/Filament/Resources/UmkmTerbrandingResource.php:122`
- **Fix:** Hapus `->visible()` condition dari ViewAction, tambah label eksplisit

### 6. Halaman tetap di form setelah create/edit User
- **File:** `app/Filament/Resources/UserResource/Pages/CreateUser.php:14-16`
- **File:** `app/Filament/Resources/UserResource/Pages/EditUser.php:20-22`
- **Fix:** Tambah `getRedirectUrl()` return ke index

### 7. Tombol Next tidak disabled saat area < 1.5 M²
- **File:** `app/Filament/Resources/UmkmResource.php:449-466`
- **Fix:** Tambah `->afterValidation()` di Wizard Step "Ukuran Panel" yang throw ValidationException

### 8. KPI "UMKM Perlu di-Design" tampil 0 padahal ada data
- **File:** `app/Filament/Resources/UmkmResource/Widgets/SummaryStatsWidget.php:183`
- **Penyebab:** Query hanya cek `STATUS_APPROVED`, tidak include `STATUS_MENUNGGU_DIDESAIN`
- **Fix:** Tambah `Umkm::STATUS_MENUNGGU_DIDESAIN` ke `whereIn`

### 9. KPI "UMKM Perlu di-Branding" (team pasang) tampil 0
- **File:** `app/Filament/Resources/UmkmResource/Widgets/SummaryStatsWidget.php:58`
- **Penyebab:** Query pakai `STATUS_DESIGN_APPROVED` tapi table pakai `STATUS_WAITING_INSTALLATION`
- **Fix:** Ganti ke `STATUS_WAITING_INSTALLATION`

### 10. Backup Management error (gagal dump database)
- **File:** `app/Filament/Pages/BackupPage.php`
- **Penyebab:** `mysqldump` tidak tersedia di container PHP Alpine
- **Fix:** Rewrite tanpa mysqldump — gunakan Laravel Excel export + JSON + file copy per UMKM

### 11. Tombol "Buat" muncul di Pemasangan Stiker
- **File:** `app/Filament/Resources/UmkmStikerResource.php:23-25`
- **Fix:** Tambah `canCreate(): false`

### 12. KPI filter URL mismatch dengan data
- **File:** `app/Filament/Resources/UmkmResource/Widgets/SummaryStatsWidget.php`
- **Fix:** Sinkronkan semua URL filter dengan count query dan schema DB

---

## Fitur Baru

### 1. Tombol "Lihat Design" di halaman Pemasangan Stiker
- **File:** `app/Filament/Resources/UmkmStikerResource.php:126-175`
- **Fungsi:** Modal preview + download file design (FA + 3 mockup) untuk team printing

### 2. KPI Card "Perlu Di-review Client"
- **File:** `app/Filament/Resources/UmkmResource/Widgets/SummaryStatsWidget.php:130-134`
- **Fungsi:** Tampilkan jumlah UMKM pending, klik redirect ke filter pending

### 3. KPI Card "Design Perlu Di-review" (client only)
- **File:** `app/Filament/Resources/UmkmResource/Widgets/SummaryStatsWidget.php:167-173`
- **Fungsi:** Tampilkan design yang menunggu approval client

### 4. Filter "Approved" di Data UMKM
- **File:** `app/Filament/Resources/UmkmResource.php:687-706`
- **Fungsi:** Custom filter `approved_all` yang query `whereNotIn(['pending', 'rejected'])`

### 5. Image compression otomatis
- **File:** `app/Filament/Resources/UmkmResource.php:483-570`
- **File:** `app/Filament/Resources/UmkmStikerResource.php:58-93`
- **Spec:** Auto-resize max 1200x1200px, max 5MB per foto, video max 30MB

---

## Export Audit (PRD Compliance)

### Excel Export
- **File:** `app/Exports/UmkmExport.php`
- Text wrap enabled (`WithStyles` → `setWrapText(true)`)
- Column width per tipe data (`WithColumnWidths`)
- Header styling (bold, amber background)
- Semua field PRD terisi: profil, rekening, GPS, ukuran panel, foto URL, design, stiker, personalia
- File path → full URL (`url('storage/' . $path)`)
- `Approved By` → nama user (bukan ID)
- Tanggal → format `d-m-Y H:i`

### PDF Export
- **File:** `resources/views/exports/umkm-pdf.blade.php`
- Word wrap via CSS `word-wrap: break-word`
- Field: koordinat GPS, desainer, team pasang, tanggal pasang
- Status styling per workflow state

### Export Filter (PRD 5.2)
- **File:** `app/Filament/Resources/UmkmResource.php:970-1030`
- Per PIC Lapangan, Per Desainer, Per Kota, Raw Data (semua)
- Eager-load: `kota`, `submittedBy`, `umkmDesign`, `approvedBy`

---

## Chart "Progres UMKM Per Kota"
- **File:** `app/Filament/Resources/AdminResource/Widgets/UmkmChartWidget.php`
- Sebelum: hanya 3 status (approved, pending, rejected)
- Sesudah: 6 kategori stacked bar (Pending, Approved, Proses Design, Siap Pasang, Terbranding, Rejected)
- Query optimized: single `selectRaw` per kota

---

## Deployment
- Server: `root@129.212.231.5` (Docker)
- Deploy via: `scp` file → `docker compose up -d --build app`
- Cache clear: `php artisan config:cache && route:cache && view:cache`
- Fix duplicate `AdminPanelProvider.php` di server (`app/Filament/Resources/` — dihapus)
