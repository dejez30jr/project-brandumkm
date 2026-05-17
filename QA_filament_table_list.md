# QA - Filament Table List (UMKM)

Dokumen ini berisi daftar **tabel/list** yang ada di Filament untuk kebutuhan pengujian QA.

> Catatan: yang dimaksud “tabel” adalah halaman/komponen Filament berbasis `->table()` (data grid/listing), termasuk juga halaman List pada resource.

---

## 1) UmkmResource (Data UMKM)
**Lokasi:** `app/Filament/Resources/UmkmResource.php`

### Halaman (Pages)
- **ListUmkms**: `UmkmResource::getPages()['index']`
- **CreateUmkm**: `getPages()['create']`
- **EditUmkm**: `getPages()['edit']`

### Komponen Table (untuk QA)
- Kolom:
  - `nama_usaha`
  - `nama_pemilik`
  - `kota.nama`
  - `total_area_branding`
  - `memenuhi_kriteria` (boolean icon)
  - `status` (badge warna: pending/approved/rejected)
  - `submittedBy.name`
  - `created_at` (datetime)
- Filter:
  - `status` (SelectFilter)
  - `kota_id` (SelectFilter)
  - `memenuhi_kriteria` (TernaryFilter)
- Actions:
  - View
  - Edit (visible role: `admin`, `pic_lapangan`)
  - Approve (visible saat `status=pending` dan user client/pic_lapangan)
  - Reject (visible saat `status=pending` dan user client)
- Bulk actions:
  - Delete (visible role `admin`, `pic_lapangan`)
- Header actions:
  - Create (visible role `admin`, `pic_lapangan`)
  - Export Excel (filter status & kota)
  - Export PDF (filter status & kota)

---

## 2) UmkmDesignResource (Design UMKM)
**Lokasi:** `app/Filament/Resources/UmkmDesignResource.php`

### Halaman (Pages)
- **ListUmkmDesigns**: `UmkmDesignResource::getPages()['index']`
- **CreateUmkmDesign**: `getPages()['create']`

### Komponen Table (untuk QA)
- Kolom:
  - `umkm.nama_usaha`
  - `umkm.kota.nama`
  - `file_path` (ImageColumn preview)
  - `versi` (badge)
  - `designer.name`
  - `status` (badge warna: pending/approved/revision_needed/revised)
  - `catatan_revisi` (limit 30 + tooltip)
  - `created_at` (datetime)
- Filter:
  - `kota_id` (SelectFilter, query via `whereHas('umkm', ...)`)
  - `status` (SelectFilter)
- Actions:
  - View
  - Edit (visible role designer)
  - Delete (visible role designer)
  - Approve (visible bila status `pending`/`revised` dan user `pic_lapangan`/client)
  - Minta Revisi `revisi` (visible bila status `pending`/`revised` dan user `pic_lapangan`/client)

---

## 3) UserResource (Manajemen Pengguna)
**Lokasi:** `app/Filament/Resources/UserResource.php`

### Halaman (Pages)
- ListUsers
- CreateUser
- EditUser

### Komponen Table (untuk QA)
- Kolom:
  - `name`
  - `email`
  - `role` (BadgeColumn)
  - `kota.nama`
  - `is_active` (boolean icon)
  - `created_at`
- Filter:
  - `role`
  - `kota_id`
- Actions:
  - Edit (visible role admin)
  - Delete (visible role admin)
- Bulk actions:
  - Delete bulk (visible role admin)

---

## 4) NotifikasiResource (System - Notifikasi)
**Lokasi:** `app/Filament/Resources/NotifikasiResource.php`

### Halaman (Pages)
- **ListNotifikasis**: `NotifikasiResource::getPages()['index']`

### Catatan penting
- `shouldRegisterNavigation(): false` dan `canCreate(): false`.
  Artinya, tabel notifikasi **tidak tampil sebagai menu**, tapi tetap ada route/halaman untuk list.

### Komponen Table (untuk QA)
- Kolom:
  - `judul` (searchable + icon logic berdasarkan last seen & created_at)
  - `pesan`
  - `created_at` (sortable)
- Sorting default:
  - `created_at desc`
- `recordUrl` (klik baris):
  - map `judul` ke resource:
    - `UMKM Baru Masuk` -> `UmkmResource::getUrl('index')`
    - `Design Baru Upload` -> `UmkmDesignResource::getUrl('index')`
    - `Desain Telah Direvisi 🎨` -> `UmkmDesignResource::getUrl('index')`

---

## Ringkasan Coverage QA
- Tabel/Listing yang harus diuji:
  1. **Umkm table** (filter status/kota, badge status, action approve/reject, export)
  2. **UmkmDesign table** (filter kota/status, badge status, action approve & minta revisi)
  3. **User table** (filter role/kota, permission admin)
  4. **Notifikasi table** (icon indikator “new”, recordUrl routing, akses tanpa menu)

---

## Checklist QA (quick scan)
Untuk tiap table/list di atas, minimal QA cek:
- [ ] Permission visibility (role admin/client/design/pic_lapangan)
- [ ] Filter berfungsi & query sesuai relasi
- [ ] Badge status & label konsisten (mapping value -> tampilan)
- [ ] Action sesuai aturan status (mis. pending hanya untuk approve/revisi yang benar)
- [ ] Export (Excel/PDF) menghasilkan file sesuai filter
- [ ] UI responsiveness (kolom, pagination, search)


