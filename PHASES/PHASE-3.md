# PHASE 3 — Field Baru & Optimasi Performa
**Tanggal:** 23 Mei 2026  
**Prasyarat:** PHASE 1 & 2 selesai  
**Target:** 4 field baru + 3 optimasi performa  
**Status:** ✅ Selesai

---

## Daftar Task

### TASK-16 — Tambah Field `jam_buka` & `jam_tutup`
| | |
|---|---|
| **Tabel** | `umkms` |
| **Tipe** | `time` nullable |
| **Kebutuhan** | Client minta data jam operasional UMKM dicatat |
| **Status** | ✅ Selesai |

**File yang diubah:**
- `database/migrations/` — buat migration baru `add_operational_fields_to_umkms_table`
- `app/Models/Umkm.php` — tambah ke `$fillable`
- `app/Filament/Resources/UmkmResource.php` — tambah field di Step 1 (Data Pemilik)

---

### TASK-17 — Tambah Field `request_text`
| | |
|---|---|
| **Tabel** | `umkms` |
| **Tipe** | `text` nullable |
| **Kebutuhan** | Teks branding yang diminta pemilik UMKM (contoh: "Aneka Gorengan UMY") |
| **Status** | ✅ Selesai |

**File yang diubah:**
- `database/migrations/` — masuk ke migration yang sama dengan TASK-16
- `app/Models/Umkm.php` — tambah ke `$fillable`
- `app/Filament/Resources/UmkmResource.php` — tambah field di Step 1

---

### TASK-18 — Tambah Field `catatan`
| | |
|---|---|
| **Tabel** | `umkms` |
| **Tipe** | `text` nullable |
| **Kebutuhan** | Catatan tambahan dari PIC lapangan (contoh: "Lokasi sebelah Alfamart, area UMY") |
| **Status** | ✅ Selesai |

**File yang diubah:**
- `database/migrations/` — masuk ke migration yang sama dengan TASK-16
- `app/Models/Umkm.php` — tambah ke `$fillable`
- `app/Filament/Resources/UmkmResource.php` — tambah field di Step 5 (Foto) atau Step baru

---

### TASK-19 — Tambah Foto ke-5
| | |
|---|---|
| **Tabel** | `umkms` |
| **Field** | `foto_tampak_jauh` (string, nullable) |
| **Kebutuhan** | Client minta minimal 5 foto per UMKM. Saat ini hanya 4 (depan, kanan, kiri, plang alfamart). |
| **Status** | ✅ Selesai |

**File yang diubah:**
- `database/migrations/` — masuk ke migration yang sama dengan TASK-16
- `app/Models/Umkm.php` — tambah ke `$fillable`
- `app/Filament/Resources/UmkmResource.php` — tambah FileUpload di Step 5

---

### TASK-20 — Tambah Index Database
| | |
|---|---|
| **Tabel** | `umkms`, `umkm_designs` |
| **Kebutuhan** | Dengan 2000+ data, query tanpa index akan lambat |
| **Status** | ✅ Selesai |

**Index yang ditambahkan:**

| Tabel | Kolom | Alasan |
|---|---|---|
| `umkms` | `status` | Filter status pending/approved/rejected |
| `umkms` | `kota_id` | Filter per kota |
| `umkms` | `submitted_by` | Filter data milik PIC |
| `umkm_designs` | `designer_id` | Filter design milik designer |
| `umkm_designs` | `status` | Filter status design |
| `notifikasis` | `user_id` | Query notifikasi per user |

**File yang diubah:**
- `database/migrations/` — buat migration baru `add_indexes_to_tables`

---

### TASK-21 — Matikan Polling Widget atau Tambah Cache
| | |
|---|---|
| **File** | `app/Filament/Resources/UmkmResource/Widgets/SummaryStatsWidget.php` |
| **Dampak** | Widget polling DB setiap 5 detik — dengan banyak user aktif, ini membebani server |
| **Root Cause** | Filament widget default polling aktif |
| **Fix** | Tambahkan `protected static ?string $pollingInterval = null;` untuk matikan polling, atau set ke interval yang wajar (misal `'60s'`) |
| **Status** | ✅ Selesai |

**File yang diubah:**
- `app/Filament/Resources/UmkmResource/Widgets/SummaryStatsWidget.php`
- Widget lain yang relevan

---

### TASK-22 — Perbaiki Query N+1 di SummaryStatsWidget
| | |
|---|---|
| **File** | `app/Filament/Resources/UmkmResource/Widgets/SummaryStatsWidget.php` |
| **Dampak** | Setiap card di dashboard query ke DB sendiri-sendiri — total bisa 10+ query per load |
| **Root Cause** | Setiap `Stat::make()` punya query `Umkm::where(...)->count()` masing-masing |
| **Fix** | Batch query di awal method `getStats()`, simpan hasilnya ke variabel, gunakan di setiap card |
| **Status** | ✅ Selesai |

**Contoh pendekatan:**
```php
// Sebelum: setiap card query sendiri
Umkm::where('status', 'pending')->count()
Umkm::where('status', 'approved')->count()
Umkm::where('status', 'rejected')->count()

// Sesudah: satu query, ambil semua sekaligus
$counts = Umkm::selectRaw("
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
    COUNT(*) as total
")->first();
```

**File yang diubah:**
- `app/Filament/Resources/UmkmResource/Widgets/SummaryStatsWidget.php`

---

## Checklist

- [x] TASK-16 — Field jam_buka & jam_tutup
- [x] TASK-17 — Field request_text
- [x] TASK-18 — Field catatan
- [x] TASK-19 — Foto ke-5
- [x] TASK-20 — Index database
- [x] TASK-21 — Matikan polling widget
- [x] TASK-22 — Perbaiki query N+1

## Verifikasi Selesai

- [x] Form UMKM menampilkan field jam operasional, request text, catatan, dan foto ke-5
- [x] Migration berjalan tanpa error: `php artisan migrate`
- [x] Query di halaman Data UMKM tidak ada N+1 (cek dengan `debugbar` atau `telescope`)
- [x] Dashboard tidak polling setiap 5 detik
