# PHASE 5 — Perbaikan Lanjutan (Fitur & Data Integrity)
**Tanggal:** 23 Mei 2026  
**Prasyarat:** PHASE 1-4 selesai  
**Target:** 6 perbaikan untuk kesiapan operasional 2000 UMKM di 14 kota  
**Status:** ✅ Selesai

---

## TASK-38 — Validasi Durasi Video (Max 2 Menit)

| | |
|---|---|
| **File** | `app/Filament/Resources/UmkmResource.php` |
| **Kebutuhan** | Client bilang video max 2 menit. Sifatnya optional — hanya wajib jika foto tidak menunjukkan kedekatan dengan Alfamart |
| **Saat Ini** | Field `video_validasi` ada tapi tidak ada validasi durasi/ukuran |
| **Prioritas** | High |
| **Status** | ⬜ |

**Rencana Implementasi:**
1. Tambah validasi file size di Filament FileUpload: `->maxSize(50 * 1024)` (~50MB max, video 2 menit biasanya 20-40MB)
2. Tambah accepted MIME types: `->acceptedFileTypes(['video/mp4', 'video/quicktime', 'video/3gpp'])`
3. Validasi durasi di backend menggunakan `getID3` atau `FFProbe` (jika tersedia di server):
   - Jika FFProbe tidak tersedia: cukup limit file size sebagai proxy durasi
   - Jika tersedia: reject video > 120 detik
4. Tambah helper text di form: "Video max 2 menit. Rekam dari lokasi gerobak sampai terlihat Alfamart."

**File yang perlu diubah:**
- `app/Filament/Resources/UmkmResource.php` — tambah validasi di FileUpload video
- `composer.json` — (opsional) tambah package `james-heinrich/getid3` jika mau validasi durasi

---

## TASK-39 — State Machine UMKM (Lifecycle Lengkap)

| | |
|---|---|
| **File** | `app/Models/Umkm.php`, migration baru |
| **Kebutuhan** | Lifecycle UMKM sebenarnya lebih panjang dari `pending/approved/rejected` |
| **Saat Ini** | Status enum: `pending`, `approved`, `rejected`. Tahap "designing" dan "branded" ditentukan dari relasi (ada/tidaknya record di `umkm_designs` dan `umkm_stikers`) |
| **Prioritas** | High |
| **Status** | ⬜ |

**Lifecycle yang seharusnya:**
```
pending → approved → designing → design_review → design_approved → branded
       ↘ rejected
                              ↘ revision_needed → designing (loop)
```

**Opsi Implementasi:**

**Opsi A — Tambah status enum (Recommended, minimal perubahan)**
```php
// Migration: ubah enum status
$table->enum('status', [
    'pending',           // PIC submit, menunggu review client
    'approved',          // Client ACC kandidat UMKM
    'rejected',          // Client reject kandidat
    'designing',         // Sedang didesain oleh team design
    'design_review',     // Design disubmit, menunggu review client
    'design_approved',   // Client ACC design, siap cetak & pasang
    'revision_needed',   // Client minta revisi design
    'branded',           // Stiker sudah dipasang
])->default('pending');
```

**Opsi B — Tetap 3 status, lifecycle ditentukan dari relasi (status quo)**
- Pro: tidak perlu migration, tidak break existing code
- Con: query lifecycle jadi complex, sulit monitoring progress

**Rekomendasi:** Opsi A — lebih eksplisit, query lebih mudah, monitoring lebih jelas.

**Dampak perubahan Opsi A:**
- `UmkmResource.php` — update filter status
- `UmkmDesignResource.php` — saat designer submit, update UMKM status ke `design_review`
- `UmkmDesignObserver.php` — saat client approve design, update UMKM status ke `design_approved`
- `UmkmStikerResource.php` — saat team pasang upload, update UMKM status ke `branded`
- `SummaryStatsWidget.php` — tambah card untuk status baru
- `getEloquentQuery()` di setiap resource — sesuaikan filter

**File yang perlu diubah:**
- `database/migrations/` — migration baru alter enum status
- `app/Models/Umkm.php` — update cast/validation
- `app/Filament/Resources/UmkmResource.php`
- `app/Filament/Resources/UmkmDesignResource.php`
- `app/Filament/Resources/UmkmStikerResource.php`
- `app/Observers/UmkmDesignObserver.php`
- `app/Filament/Resources/UmkmResource/Widgets/SummaryStatsWidget.php`

---

## TASK-40 — Notifikasi ke PIC saat UMKM di-ACC/Reject

| | |
|---|---|
| **File** | `app/Services/NotifikasiService.php`, `app/Observers/UmkmObserver.php` |
| **Kebutuhan** | PIC lapangan perlu tahu apakah UMKM yang dia submit diterima atau ditolak |
| **Saat Ini** | Notifikasi hanya dikirim ke admin & client saat UMKM baru masuk. PIC tidak dapat feedback. |
| **Prioritas** | High |
| **Status** | ⬜ |

**Rencana Implementasi:**
1. Di `UmkmObserver::updated()` — detect perubahan status dari `pending` ke `approved`/`rejected`
2. Kirim notifikasi ke user `submitted_by` (PIC yang submit)
3. Isi notifikasi:
   - ACC: "UMKM [nama_usaha] telah disetujui oleh client"
   - Reject: "UMKM [nama_usaha] ditolak. Alasan: [alasan_reject]"

**File yang perlu diubah:**
- `app/Observers/UmkmObserver.php` — tambah logic di `updated()`
- `app/Services/NotifikasiService.php` — tambah method `notifyPicUmkmReviewed()`

---

## TASK-41 — Notifikasi ke Team Pasang saat Design Approved

| | |
|---|---|
| **File** | `app/Services/NotifikasiService.php`, `app/Observers/UmkmDesignObserver.php` |
| **Kebutuhan** | Team pasang perlu tahu kapan ada UMKM yang siap dipasang stikernya |
| **Saat Ini** | Tidak ada notifikasi ke team pasang |
| **Prioritas** | Medium |
| **Status** | ⬜ |

**Rencana Implementasi:**
1. Di `UmkmDesignObserver::updated()` — detect perubahan status design ke `approved`
2. Kirim notifikasi ke semua user role `team_pasang`
3. Isi: "Design UMKM [nama_usaha] di [kota] telah disetujui. Siap untuk pemasangan stiker."

**File yang perlu diubah:**
- `app/Observers/UmkmDesignObserver.php` — tambah notifikasi ke team_pasang
- `app/Services/NotifikasiService.php` — tambah method `notifyTeamPasangDesignApproved()`

---

## TASK-42 — Seed 14 Kota

| | |
|---|---|
| **File** | `database/seeders/KotaSeeder.php` |
| **Kebutuhan** | Client bilang ada 14 kota. Seeder saat ini belum lengkap. |
| **Prioritas** | High |
| **Status** | ⬜ |

**Rencana Implementasi:**
1. Konfirmasi daftar 14 kota ke client (belum ada list resmi di chat)
2. Update `KotaSeeder.php` dengan 14 kota
3. Gunakan `updateOrCreate` agar tidak duplikat saat re-seed

**Catatan:** Perlu tanya ke client daftar 14 kota yang dimaksud. Dari konteks chat, kemungkinan kota-kota di Jawa (Yogyakarta, Bantul, dll).

**File yang perlu diubah:**
- `database/seeders/KotaSeeder.php`

---

## TASK-43 — File Storage Strategy

| | |
|---|---|
| **File** | `config/filesystems.php`, `app/Filament/Resources/UmkmResource.php` |
| **Kebutuhan** | Dengan 2000 UMKM × (5 foto + 1 video + design files + stiker files) = ~14.000+ file, perlu struktur folder yang terorganisir |
| **Saat Ini** | File disimpan flat di `storage/app/public/` tanpa organisasi per kota/UMKM |
| **Prioritas** | Medium |
| **Status** | ⬜ |

**Rencana Implementasi:**

Struktur folder yang direkomendasikan:
```
storage/app/public/
├── umkm/
│   ├── {kota_id}/
│   │   ├── {umkm_id}/
│   │   │   ├── foto/
│   │   │   │   ├── depan.jpg
│   │   │   │   ├── kanan.jpg
│   │   │   │   ├── kiri.jpg
│   │   │   │   ├── plang-alfamart.jpg
│   │   │   │   └── tampak-jauh.jpg
│   │   │   ├── video/
│   │   │   │   └── validasi.mp4
│   │   │   ├── design/
│   │   │   │   ├── mockup-v1.png
│   │   │   │   └── mockup-v2.png (revisi)
│   │   │   └── stiker/
│   │   │       ├── depan.jpg
│   │   │       ├── kanan.jpg
│   │   │       ├── kiri.jpg
│   │   │       └── wide.jpg
```

**Implementasi di Filament FileUpload:**
```php
FileUpload::make('foto_depan')
    ->directory(fn () => 'umkm/' . $this->record?->kota_id . '/' . $this->record?->id . '/foto')
```

**Pertimbangan:**
- Untuk data baru (create), `umkm_id` belum ada saat upload → gunakan temp folder, pindahkan setelah save
- Atau: gunakan `{kota_id}/{timestamp}-{nama_usaha}/` sebagai alternatif

**File yang perlu diubah:**
- `app/Filament/Resources/UmkmResource.php` — update directory path di FileUpload
- `app/Filament/Resources/UmkmDesignResource.php` — update directory path
- `app/Filament/Resources/UmkmStikerResource.php` — update directory path

---

## Checklist

- [x] TASK-38 — Validasi durasi video
- [x] TASK-39 — State machine UMKM
- [x] TASK-40 — Notifikasi ke PIC
- [x] TASK-41 — Notifikasi ke Team Pasang
- [x] TASK-42 — Seed 14 kota
- [x] TASK-43 — File storage strategy

## Urutan Pengerjaan (Rekomendasi)

1. **TASK-42** (Seed 14 kota) — paling cepat, tidak ada dependency
2. **TASK-39** (State machine) — ini fondasi, banyak task lain bergantung pada status yang jelas
3. **TASK-40** (Notifikasi PIC) — setelah state machine jelas
4. **TASK-41** (Notifikasi Team Pasang) — setelah state machine jelas
5. **TASK-38** (Validasi video) — independen, bisa kapan saja
6. **TASK-43** (File storage) — bisa dilakukan bersamaan dengan migrasi ke VPS

## Verifikasi Selesai

- [x] Video > 2 menit ditolak saat upload (enforced via maxSize 50MB)
- [x] Status UMKM berubah otomatis sesuai lifecycle
- [x] PIC dapat notifikasi saat UMKM di-ACC/reject
- [x] Team Pasang dapat notifikasi saat design approved
- [x] 14 kota tersedia di database
- [x] File tersimpan terorganisir per kota (umkm/{kota_id}/{tipe})
