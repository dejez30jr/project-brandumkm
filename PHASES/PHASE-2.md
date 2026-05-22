# PHASE 2 — Bug Fungsionalitas (P1)
**Tanggal:** 23 Mei 2026  
**Prasyarat:** PHASE 1 selesai  
**Target:** 10 bug yang menyebabkan fitur tidak berfungsi  
**Status:** ✅ Selesai

---

## Daftar Task

### TASK-05 — GPS Android (Fallback Manual)
| | |
|---|---|
| **File** | `app/Filament/Resources/UmkmResource.php` |
| **Dampak** | PIC lapangan tidak bisa mengisi koordinat lokasi UMKM |
| **Root Cause** | `navigator.geolocation` butuh HTTPS. Di localhost bisa jalan, tapi di production (HTTP) mati. Di WebIntoApp juga butuh permission yang benar. |
| **Fix** | Tambahkan fallback: jika GPS gagal, tampilkan input manual latitude/longitude yang bisa diedit |
| **Status** | ✅ Selesai |

**File yang diubah:**
- `app/Filament/Resources/UmkmResource.php` — ubah field `latitude`, `longitude`, `sharelock_url` dari `->readOnly()` menjadi editable jika GPS gagal

---

### TASK-06 — Tambah Kota Baru di UserResource Tidak Tersimpan
| | |
|---|---|
| **File** | `app/Filament/Resources/UserResource.php` |
| **Dampak** | Admin tidak bisa tambah kota baru saat buat akun user |
| **Root Cause** | `createOptionUsing` return `$data['name']` (string) bukan ID. Seharusnya buat record `Kota` baru dan return ID-nya. |
| **Fix** | Ganti return value ke `Kota::create(['nama' => $data['name']])->id` |
| **Status** | ✅ Selesai |

**File yang diubah:**
- `app/Filament/Resources/UserResource.php`

---

### TASK-07 — Validasi `no_wa` Duplikat Tidak Berfungsi
| | |
|---|---|
| **File** | `app/Filament/Resources/UmkmResource.php` |
| **Dampak** | Nomor WhatsApp yang sama bisa diinput berkali-kali |
| **Root Cause** | `->unique(table: Umkm::class, column: 'nama_pemilik', ...)` — kolom yang dicek adalah `nama_pemilik`, bukan `no_wa` |
| **Fix** | Ganti `column: 'nama_pemilik'` menjadi `column: 'no_wa'` |
| **Status** | ✅ Selesai |

**File yang diubah:**
- `app/Filament/Resources/UmkmResource.php`

---

### TASK-08 — Designer Tidak Bisa Submit Design Baru
| | |
|---|---|
| **File** | `app/Filament/Resources/UmkmDesignResource.php` |
| **Dampak** | Designer tidak bisa upload design untuk UMKM yang pernah di-revisi |
| **Root Cause** | Validasi `->unique('umkm_designs', 'umkm_id')` memblokir semua submission untuk UMKM yang sudah punya record design, termasuk yang statusnya `revision_needed` |
| **Fix** | Tambahkan kondisi ignore: skip validasi jika record yang ada statusnya `revision_needed` |
| **Status** | ✅ Selesai |

**File yang diubah:**
- `app/Filament/Resources/UmkmDesignResource.php`

---

### TASK-09 — Import Namespace Salah di UmkmStikerResource
| | |
|---|---|
| **File** | `app/Filament/Resources/UmkmStikerResource.php` |
| **Dampak** | Error saat halaman Pemasangan Stiker dibuka |
| **Root Cause** | `use Tables\Filters\SelectFilter;` — namespace tidak lengkap |
| **Fix** | Ganti menjadi `use Filament\Tables\Filters\SelectFilter;` atau hapus (sudah ada `use Filament\Tables\Tables;`) |
| **Status** | ✅ Selesai |

**File yang diubah:**
- `app/Filament/Resources/UmkmStikerResource.php`

---

### TASK-10 — Badge Color UmkmDesignResource Return Angka
| | |
|---|---|
| **File** | `app/Filament/Resources/UmkmDesignResource.php` |
| **Dampak** | Badge warna di sidebar tidak muncul / error |
| **Root Cause** | `getNavigationBadgeColor()` return `(string) $count` (angka) bukan nama warna |
| **Fix** | Return `'danger'` jika ada revision_needed, `'warning'` jika ada pending, `null` jika kosong |
| **Status** | ✅ Selesai |

**File yang diubah:**
- `app/Filament/Resources/UmkmDesignResource.php`

---

### TASK-11 — Tombol Approve/Reject di UmkmTerbrandingResource Tidak Muncul
| | |
|---|---|
| **File** | `app/Filament/Resources/UmkmTerbrandingResource.php` |
| **Dampak** | Tombol approve/reject tidak pernah tampil di halaman UMKM Terbranding |
| **Root Cause** | Kondisi `->visible(fn ($record) => $record->status === 'pending')` tidak akan pernah true karena resource ini hanya query data dengan `status = 'approved'` |
| **Fix** | Hapus action approve/reject dari resource ini — tidak relevan di halaman arsip |
| **Status** | ✅ Selesai |

**File yang diubah:**
- `app/Filament/Resources/UmkmTerbrandingResource.php`

---

### TASK-12 — Notifikasi Ganda (Observer + booted() Konflik)
| | |
|---|---|
| **File** | `app/Models/UmkmDesign.php`, `app/Observers/UmkmDesignObserver.php` |
| **Dampak** | Notifikasi terkirim dua kali untuk event yang sama |
| **Root Cause** | `UmkmDesign::booted()` dan `UmkmDesignObserver::updated()` keduanya mengirim notifikasi saat status berubah |
| **Fix** | Hapus logika notifikasi dari `UmkmDesign::booted()`, biarkan hanya di Observer. Ini juga menyelesaikan TASK-03 (hardcode user_id). |
| **Status** | ✅ Selesai |

**File yang diubah:**
- `app/Models/UmkmDesign.php` — hapus seluruh blok notifikasi di `booted()`
- `app/Observers/UmkmDesignObserver.php` — pastikan sudah handle semua event yang dibutuhkan
- `app/Services/NotifikasiService.php` — tambah `notifyDesignRevised()`

---

### TASK-13 — Relasi `umkm_id` di Model Notifikasi Tidak Valid
| | |
|---|---|
| **File** | `app/Models/Notifikasi.php` |
| **Dampak** | Error saat relasi `umkm()` dipanggil |
| **Root Cause** | `belongsTo(Umkm::class, 'umkm_id')` tapi kolom `umkm_id` tidak ada di tabel `notifikasis`. Tabel sudah pakai polymorphic (`notifiable_id` + `notifiable_type`). |
| **Fix** | Hapus method `umkm()` dari model `Notifikasi`. Gunakan `notifiable()` yang sudah ada. |
| **Status** | ✅ Selesai |

**File yang diubah:**
- `app/Models/Notifikasi.php`

---

### TASK-14 — Filter Notifikasi `pic_lapangan` Tidak Konsisten
| | |
|---|---|
| **File** | `app/Filament/Resources/NotifikasiResource.php` |
| **Dampak** | Badge notifikasi dan isi tabel tidak sinkron untuk role `pic_lapangan` |
| **Root Cause** | Badge filter pakai `'Design Perlu Revisi'` tapi table query pakai `'UMKM Baru Masuk'` |
| **Fix** | Sinkronkan — `pic_lapangan` seharusnya menerima notifikasi `'Design Perlu Revisi'` dan `'Design Perlu Revisi ⚠️'` di badge maupun tabel |
| **Status** | ✅ Selesai |

**File yang diubah:**
- `app/Filament/Resources/NotifikasiResource.php`

---

### TASK-15 — Badge Design Tidak Filter per `designer_id`
| | |
|---|---|
| **File** | `app/Filament/Resources/UmkmDesignResource.php` |
| **Dampak** | Angka badge di sidebar tidak akurat — menampilkan total semua designer, bukan milik designer yang login |
| **Root Cause** | `getNavigationBadge()` untuk role `design` tidak tambahkan `->where('designer_id', $user->id)` |
| **Fix** | Tambahkan filter `designer_id` pada query badge untuk role `design` |
| **Status** | ✅ Selesai |

**File yang diubah:**
- `app/Filament/Resources/UmkmDesignResource.php`

---

## Checklist

- [x] TASK-05 — GPS fallback manual
- [x] TASK-06 — Tambah kota tidak tersimpan
- [x] TASK-07 — Validasi no_wa salah kolom
- [x] TASK-08 — Designer tidak bisa submit design
- [x] TASK-09 — Namespace salah UmkmStikerResource
- [x] TASK-10 — Badge color return angka
- [x] TASK-11 — Tombol approve/reject tidak muncul
- [x] TASK-12 — Notifikasi ganda
- [x] TASK-13 — Relasi umkm_id tidak valid
- [x] TASK-14 — Filter notifikasi pic_lapangan tidak sinkron
- [x] TASK-15 — Badge design tidak filter per designer

## Verifikasi Selesai

- [x] Admin bisa tambah kota baru saat buat akun user
- [x] Input no_wa yang sama ditolak dengan pesan error
- [x] Designer bisa upload design untuk UMKM yang pernah direvisi
- [x] Halaman Pemasangan Stiker bisa dibuka tanpa error
- [x] Badge warna di sidebar Design UMKM muncul dengan benar
- [x] Halaman UMKM Terbranding tidak ada tombol approve/reject yang tidak relevan
- [x] Notifikasi hanya terkirim sekali per event
- [x] Badge notifikasi dan isi tabel sinkron untuk semua role
