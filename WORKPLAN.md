# WORKPLAN — Perbaikan Sistem UMKM Branding Gerobak

| | |
|---|---|
| **Project** | UMKM Branding Gerobak |
| **Client** | Hanz Management |
| **Branch** | `fix/audit-and-refactor` |
| **Mulai** | 22 Mei 2026 |
| **Deadline** | 26 Mei 2026 |
| **Total Isu** | 32 item |

---

## Hari 1 — 22 Mei 2026
### Bug Kritis (P0)

| # | File | Masalah | Fix |
|---|---|---|---|
| 1 | `app/Models/Umkm.php` | Model `UmkmStiker` tidak ada → fatal error | Buat model atau hapus relasi |
| 2 | `UmkmResource.php` | `getTableQuery()` referensi kolom `client_id` tidak ada → Error 500 | Hapus/perbaiki method |
| 3 | `UmkmDesign.php` | Notifikasi hardcode `user_id = 5` | Ganti dengan query dinamis |
| 4 | `BackupPage.php` | Potensi shell injection | Sanitasi input |

---

## Hari 2 — 23 Mei 2026
### GPS Android & Bug Fungsionalitas (P1)

#### A. GPS Android
| # | Task | Keterangan |
|---|---|---|
| 9 | Verifikasi GPS setelah SSL aktif | Test di browser dan WebIntoApp |
| 10 | Fallback input manual koordinat | Untuk device yang GPS-nya masih gagal |

#### B. Bug Fungsionalitas (P1)
| # | File | Masalah | Fix |
|---|---|---|---|
| 11 | `UserResource.php` | Tambah kota baru tidak tersimpan | `createOptionUsing` harus return ID bukan string |
| 12 | `UmkmResource.php` | Validasi `no_wa` duplikat tidak jalan | Kolom yang dicek salah (`nama_pemilik` → `no_wa`) |
| 13 | `UmkmDesignResource.php` | Designer tidak bisa submit design baru | Unique validation harus ignore `revision_needed` |
| 14 | `UmkmStikerResource.php` | Import namespace salah | `use Tables\Filters\...` → `use Filament\Tables\Filters\...` |
| 15 | `UmkmDesignResource.php` | Badge color return angka bukan string warna | Return `'danger'` / `'warning'` |
| 16 | `UmkmTerbrandingResource.php` | Tombol approve/reject tidak pernah muncul | Kondisi `status === 'pending'` tidak akan pernah true di resource ini |
| 17 | `UmkmDesign.php` + Observer | Notifikasi ganda — `booted()` dan Observer keduanya kirim notif | Pilih satu, hapus yang lain |
| 18 | `Notifikasi.php` | Relasi `umkm_id` referensi kolom yang tidak ada | Gunakan `notifiable_id` + `notifiable_type` |
| 19 | `NotifikasiResource.php` | Filter `pic_lapangan` tidak konsisten antara badge dan table query | Sinkronkan |
| 20 | `UmkmDesignResource.php` | Badge jumlah tidak filter per `designer_id` | Tambahkan `->where('designer_id', auth()->id())` |

---

## Hari 3 — 24 Mei 2026
### Tambah Field Baru & Optimasi Performa

#### A. Field Baru (Kebutuhan Operasional)
| # | Field | Tabel | Keterangan |
|---|---|---|---|
| 21 | `jam_buka`, `jam_tutup` | `umkms` | Jam operasional UMKM |
| 22 | `request_text` | `umkms` | Teks branding yang diminta pemilik |
| 23 | `catatan` | `umkms` | Catatan tambahan PIC lapangan |
| 24 | `foto_5` | `umkms` | Foto ke-5 (client minta min 5 foto) |

#### B. Optimasi Performa
| # | Target | Masalah | Fix |
|---|---|---|---|
| 25 | Migration baru | Tidak ada index DB untuk query berat | Index pada `status`, `kota_id`, `submitted_by`, `designer_id` |
| 26 | `SummaryStatsWidget` | Widget polling DB setiap 5 detik | Matikan polling atau tambahkan cache |
| 27 | `SummaryStatsWidget` | Query N+1 — setiap card query terpisah | Gunakan `withCount()` dan batch query |

---

## Hari 4 — 25 Mei 2026
### Testing End-to-End & Deployment

#### A. Testing Per Role
| # | Role | Skenario |
|---|---|---|
| 28 | PIC Lapangan | Submit UMKM, GPS otomatis, upload 5 foto + video |
| 29 | Client | Approve/reject UMKM, approve/revisi design |
| 30 | Team Design | Upload design, terima notif revisi, upload ulang |
| 31 | Team Pasang | Lihat daftar UMKM siap branding, upload foto stiker |
| 32 | Admin | Buat akun semua role, monitoring semua kota |

#### B. Deployment
| # | Task |
|---|---|
| 33 | `php artisan migrate --force` |
| 34 | `php artisan config:cache && route:cache && view:cache` |
| 35 | Simulasi concurrent — beberapa PIC submit bersamaan |
| 36 | Test upload foto + video di Android (WebIntoApp) |
| 37 | Verifikasi SSL aktif dan GPS berfungsi di Android |

---

## Backlog Infrastruktur (Dikerjakan Setelah Bug Fix Selesai)

| # | Task | Keterangan |
|---|---|---|
| I1 | Setup VPS | Install Nginx, PHP 8.2, MySQL 8.0 |
| I2 | Setup SSL (Let's Encrypt) | Wajib agar GPS bisa jalan di WebView |
| I3 | Migrasi SQLite → MySQL | SQLite tidak support concurrent write |
| I4 | Deploy codebase ke production | `storage:link`, permission folder, `.env` production |

---

## Backlog Fitur (Pasca Go-Live)

| # | Item | Prioritas |
|---|---|---|
| B1 | Setup Redis untuk cache & queue | Medium |
| B2 | `AfterBranding` — ada model & tabel tapi belum ada UI | Medium |
| B3 | Notifikasi ke PIC saat UMKM acc/reject | Medium |
| B4 | Notifikasi ke Team Pasang saat design approved | Medium |
| B5 | Dashboard monitoring per kota untuk Admin | Low |
| B6 | Refactor duplikasi kode infolist (~200 baris di 3 file) | Low |
| B7 | PWA manifest sebagai alternatif WebIntoApp | Low |

---

## Progress

| Hari | Fokus | Status |
|---|---|---|
| Hari 1 | Infrastruktur + 4 bug kritis | ✅ Selesai |
| Hari 2 | GPS + 10 bug fungsionalitas | ✅ Selesai |
| Hari 3 | Field baru + optimasi | ✅ Selesai |
| Hari 4 | Testing + deployment | ⏳ Pending |
