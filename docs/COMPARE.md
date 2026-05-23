# Changelog: `fix/audit-and-refactor` vs `main`

**Branch:** fix/audit-and-refactor  
**Base:** main  
**Commits:** 41  
**Files changed:** 66  
**Lines:** +35,843 / -1,162  

---

## Bug Fixes

| Fix | Detail |
|-----|--------|
| 🐛 Notifikasi reject tidak terkirim ke PIC | `isDirty()` → `wasChanged()` di UmkmObserver |
| 🐛 Notifikasi design (revisi/approved) tidak terkirim | `isDirty()` → `wasChanged()` di UmkmDesignObserver |
| 🐛 Observer conflict: status "revised" ter-override jadi "pending" | Tambah guard `!$design->isDirty('status')` di `updating()` |
| 🐛 `UmkmStiker` model tanpa tabel (fatal error) | Hapus model + relasi (data stiker ada di tabel `umkms`) |
| 🐛 Missing import `AfterBranding` di Umkm model | Tambah `use App\Models\AfterBranding` |
| 🐛 `BadgeColumn` deprecated (Filament v3) | Ganti ke `TextColumn::make()->badge()` |
| 🐛 Variabel `$user` redefinisi di SummaryStatsWidget | Hapus duplikat |

---

## Fitur Baru

| Fitur | Detail |
|-------|--------|
| ✨ State machine UMKM | 8 status lifecycle: pending → approved → designing → design_review → design_approved → revision_needed → branded + rejected |
| ✨ GPS enforcement (fullscreen overlay) | PIC wajib aktifkan GPS, tidak bisa skip, field readOnly |
| ✨ Notifikasi ke Team Pasang | Saat design di-approve, team pasang dapat notif "Siap Pasang Stiker" |
| ✨ Notifikasi ke PIC saat approve/reject | PIC tahu hasil review client |
| ✨ File storage terorganisir | Upload ke `umkm/{kota_id}/{tipe}` (foto/video/design/stiker) |
| ✨ Validasi video | Max 50MB, format MP4/MOV/3GP, helper text jelas |
| ✨ Auto-update status UMKM | Design submit → design_review, approve → design_approved, stiker lengkap → branded |

---

## Infrastruktur

| Perubahan | Detail |
|-----------|--------|
| 🗃️ Migrasi SQLite → MySQL | Hapus SQLite, konsolidasi 13 migration → 8 migration bersih |
| 🐳 Docker production | Dockerfile + docker-compose + nginx + php-fpm + supervisor |
| ⚙️ PHP 8.2 | Downgrade dari 8.5 ke 8.2 (sesuai composer.json) |
| 📦 IDE Helper | Tambah barryvdh/laravel-ide-helper untuk PHPDoc otomatis |
| 🌱 Seeder diperbaiki | updateOrCreate, 14 kota, hapus UmkmSeeder yang outdated |

---

## Testing

| Metric | Nilai |
|--------|-------|
| Total tests | 55 |
| Total assertions | 126 |
| Test suites | 7 files |
| Coverage | Models, Observers, Services, Lifecycle, Notifikasi flow |

---

## Dokumentasi

| File | Isi |
|------|-----|
| docs/DEPLOYMENT.md | Setup VPS, Docker, SSL, backup, monitoring |
| docs/GPS-WEBVIEW-SETUP.md | Konfigurasi GPS untuk WebView APK |
| docs/README.md | Index docs, tech stack, akun default |

---

## Daftar Commit

```
cb8d01bd 📖 adjust storage to 200GB
a7df255f 📖 fix VPS spec estimation for 2000 UMKM
95a04a63 📖 add docs index
f2acf507 📖 add deployment guide
69f8e0e6 🐳 add .dockerignore
ac087d0a 🐳 add nginx, php, supervisor configs
a4a012fe 🐳 add docker-compose with MySQL
1b434c2f 🐳 add Dockerfile for production
57b94a8e 📖 update GPS docs to match current implementation
8453c0a8 🙈 remove PHASES & WORKPLAN from tracking
f92c4289 🔧 add IDE helper files
3ab545f7 🧪 add 55 comprehensive unit+feature tests
c085d73d 🧪 configure phpunit for MySQL testing
e5dcb71f 📋 update workplan with phase 5
a02f56df 📋 update phase dates to 23 Mei
5c4dd492 📋 add PHASE-5 plan
855c2695 📖 add GPS WebView setup documentation
1b673499 🌱 clean up seeders with updateOrCreate
cd725943 🗃️ consolidate migrations for MySQL
20fe2bbc ♻️ use new status constants in widget
b89bde1c ✨ auto update status to branded after save
21d4195c ✨ filter by design_approved status
eae009f6 ✨ sync UMKM status on design actions
60ef8354 ✨ add GPS enforcement + status lifecycle + file storage
fa67023e ♻️ simplify notification filtering by user_id
117464be ✨ add notifyTeamPasang method
0ca2733e 🐛 fix observer conflict + wasChanged
d831aa08 🐛 fix isDirty→wasChanged for notifications
c897202b 🗑️ remove dead UmkmStiker model
3c8f2cf4 📝 add PHPDoc annotations
ba980627 📝 add PHPDoc annotations
950657d7 📝 add PHPDoc annotations
ec4bc3c3 📝 add PHPDoc annotations
c57f84ce 📝 add PHPDoc annotations
68521ab3 🏗️ add status constants, PHPDoc, remove UmkmStiker relation
fefa605b 📦 add laravel-ide-helper
c90cd99e ⚙️ set MySQL as default, remove SQLite
f71a5f83 ⚙️ update .env.example for MySQL production
09e83127 fix: notifikasi ke PIC saat UMKM acc/reject + label video 2 menit
ca3e6b61 fix: hapus tombol Edit dari UMKM Terbranding (halaman arsip)
cc0dc778 fix: Phase 1-3 — bug kritis, fungsionalitas, field baru & optimasi
```
