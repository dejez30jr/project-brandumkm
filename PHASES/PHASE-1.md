# PHASE 1 — Bug Kritis (P0)
**Tanggal:** 22 Mei 2026  
**Target:** 4 bug yang menyebabkan crash / error fatal  
**Status:** ✅ Selesai

---

## Daftar Task

### TASK-01 — Buat Model `UmkmStiker`
| | |
|---|---|
| **File** | `app/Models/Umkm.php`, `app/Models/UmkmStiker.php` |
| **Dampak** | Fatal error saat relasi `umkmStiker()` dipanggil |
| **Root Cause** | `Umkm` model import dan punya relasi ke `UmkmStiker` tapi file model tidak ada |
| **Fix** | Buat `app/Models/UmkmStiker.php` dengan relasi `belongsTo(Umkm::class)` |
| **Status** | ✅ Selesai |

**File yang diubah:**
- `app/Models/UmkmStiker.php` — buat baru
- `app/Models/Umkm.php` — tidak perlu diubah (relasi sudah benar)

---

### TASK-02 — Perbaiki `getTableQuery()` di UmkmResource
| | |
|---|---|
| **File** | `app/Filament/Resources/UmkmResource.php` |
| **Dampak** | Error 500 saat halaman Data UMKM dibuka oleh role `client` |
| **Root Cause** | Method `getTableQuery()` memanggil `parent::getTableQuery()` yang tidak ada di Filament Resource, dan ada kondisi `->where('client_id', auth()->id())` padahal kolom `client_id` tidak ada di tabel `umkms` |
| **Fix** | Hapus method `getTableQuery()` — logika filter client sudah ditangani di `getEloquentQuery()` |
| **Status** | ✅ Selesai |

**File yang diubah:**
- `app/Filament/Resources/UmkmResource.php` — hapus method `getTableQuery()`

---

### TASK-03 — Perbaiki Notifikasi Hardcode `user_id = 5`
| | |
|---|---|
| **File** | `app/Models/UmkmDesign.php` |
| **Dampak** | Notifikasi "Desain Telah Direvisi" selalu dikirim ke user ID 5, bukan ke reviewer yang seharusnya |
| **Root Cause** | Di `booted()` method, `$targetUserId = 5` di-hardcode |
| **Fix** | Ganti dengan query ke semua user role `admin` dan `client`, atau pindahkan logika ini ke `NotifikasiService` |
| **Status** | ✅ Selesai |

**File yang diubah:**
- `app/Models/UmkmDesign.php` — hapus `booted()` notifikasi, delegasikan ke `NotifikasiService`
- `app/Services/NotifikasiService.php` — tambah method `notifyDesignRevised()`

**Catatan:** Ini juga menyelesaikan konflik dengan `UmkmDesignObserver` (notifikasi ganda — lihat PHASE 2 TASK-07).

---

### TASK-04 — Sanitasi Input di BackupPage
| | |
|---|---|
| **File** | `app/Filament/Pages/BackupPage.php` |
| **Dampak** | Potensi shell injection jika input tidak disanitasi sebelum dieksekusi sebagai shell command |
| **Root Cause** | Input dari form diteruskan langsung ke shell command tanpa validasi/escaping |
| **Fix** | Gunakan `escapeshellarg()` pada semua input yang masuk ke shell command, atau batasi opsi dengan whitelist |
| **Status** | ✅ Selesai |

**File yang diubah:**
- `app/Filament/Pages/BackupPage.php`

---

## Checklist

- [x] TASK-01 — Model `UmkmStiker`
- [x] TASK-02 — `getTableQuery()` error
- [x] TASK-03 — Notifikasi hardcode user_id
- [x] TASK-04 — BackupPage shell injection

## Verifikasi Selesai

- [x] Buka halaman Data UMKM sebagai role `client` → tidak error
- [x] Buka halaman Data UMKM sebagai role `pic_lapangan` → tidak error
- [x] Designer update design → notifikasi terkirim ke semua admin & client, bukan hanya user ID 5
- [x] Tidak ada fatal error di log (`storage/logs/laravel.log`)
