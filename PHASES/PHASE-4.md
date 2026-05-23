# PHASE 4 — Testing End-to-End & Deployment
**Tanggal:** 23 Mei 2026  
**Prasyarat:** PHASE 1, 2, 3 selesai  
**Target:** Semua role berfungsi normal, siap go-live 26 Mei  
**Status:** ⏳ Pending

---

## A. Testing Per Role

### Role: PIC Lapangan
| # | Skenario | Expected | Status |
|---|---|---|---|
| T-01 | Login sebagai PIC Lapangan | Masuk ke dashboard, menu terbatas (hanya Data UMKM) | ⬜ |
| T-02 | Buka form tambah UMKM | Form wizard 5 step muncul | ⬜ |
| T-03 | GPS otomatis di Step Lokasi | Koordinat terisi otomatis, peta preview muncul | ⬜ |
| T-04 | GPS gagal → input manual | Field latitude/longitude bisa diedit manual | ⬜ |
| T-05 | Upload 5 foto + video | Semua file terupload, preview muncul | ⬜ |
| T-06 | Submit UMKM | Data tersimpan, status `pending`, notifikasi terkirim ke admin & client | ⬜ |
| T-07 | Lihat history pengajuan | Hanya data milik PIC yang login yang tampil | ⬜ |
| T-08 | Edit UMKM status pending | Bisa diedit | ⬜ |
| T-09 | Edit UMKM status approved | Tidak bisa diedit (tombol edit tidak muncul) | ⬜ |
| T-10 | Lihat notifikasi | Hanya notifikasi relevan untuk PIC (design perlu revisi) | ⬜ |

---

### Role: Client
| # | Skenario | Expected | Status |
|---|---|---|---|
| T-11 | Login sebagai Client | Masuk ke dashboard, lihat semua data UMKM semua kota | ⬜ |
| T-12 | Approve UMKM | Status berubah ke `approved`, notifikasi terkirim ke team design | ⬜ |
| T-13 | Reject UMKM dengan alasan | Status berubah ke `rejected`, alasan tersimpan dan tampil di detail | ⬜ |
| T-14 | Lihat design yang diupload | Halaman Design UMKM menampilkan semua design | ⬜ |
| T-15 | Approve design | Status design `approved`, file design tersalin ke data UMKM | ⬜ |
| T-16 | Minta revisi design | Status design `revision_needed`, catatan tersimpan, notifikasi ke designer | ⬜ |
| T-17 | Lihat notifikasi | Hanya notifikasi relevan untuk client (UMKM baru, design baru, design direvisi) | ⬜ |

---

### Role: Team Design
| # | Skenario | Expected | Status |
|---|---|---|---|
| T-18 | Login sebagai Designer | Masuk ke dashboard, lihat antrean UMKM perlu design | ⬜ |
| T-19 | Klik notifikasi "UMKM Perlu Design" | Redirect ke form create design dengan UMKM sudah terisi | ⬜ |
| T-20 | Upload design baru | Form tersimpan, notifikasi terkirim ke admin & client | ⬜ |
| T-21 | Terima notifikasi revisi | Notifikasi muncul, klik redirect ke form edit design | ⬜ |
| T-22 | Upload revisi design | Status berubah ke `revised`, notifikasi terkirim ke client | ⬜ |
| T-23 | Lihat history design sendiri | Hanya design milik designer yang login yang tampil | ⬜ |
| T-24 | Badge sidebar akurat | Angka badge sesuai jumlah design yang perlu dikerjakan milik designer ini | ⬜ |

---

### Role: Team Pasang
| # | Skenario | Expected | Status |
|---|---|---|---|
| T-25 | Login sebagai Team Pasang | Masuk ke dashboard, lihat UMKM yang siap dibranding | ⬜ |
| T-26 | Lihat daftar UMKM siap branding | Hanya UMKM yang design-nya sudah approved dan stiker belum dipasang | ⬜ |
| T-27 | Upload 4 foto stiker | Foto tersimpan, UMKM pindah ke halaman Terbranding | ⬜ |
| T-28 | Lihat notifikasi | Hanya notifikasi "UMKM Perlu Branding" | ⬜ |

---

### Role: Admin
| # | Skenario | Expected | Status |
|---|---|---|---|
| T-29 | Login sebagai Admin | Masuk ke dashboard, lihat semua data semua kota | ⬜ |
| T-30 | Buat akun PIC Lapangan dengan kota | Akun tersimpan, kota baru bisa ditambahkan | ⬜ |
| T-31 | Buat akun Designer | Akun tersimpan | ⬜ |
| T-32 | Buat akun Team Pasang | Akun tersimpan | ⬜ |
| T-33 | Nonaktifkan akun user | `is_active = false`, user tidak bisa login | ⬜ |
| T-34 | Monitoring semua data UMKM | Bisa lihat semua data tanpa filter kota | ⬜ |
| T-35 | Export Excel | File Excel terdownload sesuai filter | ⬜ |
| T-36 | Export PDF | File PDF terdownload sesuai filter | ⬜ |

---

## B. Testing Performa & Stabilitas

| # | Skenario | Expected | Status |
|---|---|---|---|
| T-37 | 3 PIC submit UMKM bersamaan | Semua data tersimpan, tidak ada data corrupt | ⬜ |
| T-38 | Upload foto ukuran besar (5MB) | Upload berhasil, tidak timeout | ⬜ |
| T-39 | Upload video 15MB | Upload berhasil, tidak timeout | ⬜ |
| T-40 | Buka dashboard dengan 100+ data | Halaman load < 3 detik | ⬜ |

---

## C. Deployment

| # | Task | Command / Keterangan |
|---|---|---|
| D-01 | Jalankan semua migration | `php artisan migrate --force` |
| D-02 | Clear dan rebuild cache | `php artisan config:cache && php artisan route:cache && php artisan view:cache` |
| D-03 | Pastikan storage link aktif | `php artisan storage:link` |
| D-04 | Set permission folder storage | `chmod -R 775 storage bootstrap/cache` |
| D-05 | Verifikasi tidak ada error di log | Cek `storage/logs/laravel.log` |

---

## Checklist Final Sebelum Go-Live

- [ ] Semua test role lulus (T-01 s/d T-40)
- [ ] Tidak ada error di `storage/logs/laravel.log`
- [ ] GPS berfungsi di browser (localhost/HTTPS)
- [ ] Upload foto dan video berfungsi
- [ ] Notifikasi terkirim ke role yang tepat
- [ ] Export Excel dan PDF berfungsi
- [ ] Semua migration sudah dijalankan
