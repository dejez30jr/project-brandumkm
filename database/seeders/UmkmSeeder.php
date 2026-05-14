<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Umkm;
use Illuminate\Support\Facades\DB;

class UmkmSeeder extends Seeder
{
    public function run(): void
    {
        Umkm::create([
            'nama_pemilik' => 'Budi Santoso',
            'nama_usaha' => 'Martabak Terang Bulan',
            'alamat_usaha' => 'Jl. Raya Bogor No. 123',
            'no_wa' => '081234567890',
            'radius' => '50m',
            'no_rekening' => '1234567890',
            'nama_bank' => 'BCA',
            'atas_nama_rekening' => 'Budi Santoso',
            'latitude' => -6.594,
            'longitude' => 106.789,
            'sharelock_url' => 'https://maps.google.com/?q=-6.594,106.789',

            // Ukuran panel depan
            'depan_panel_atas' => '100x50',
            'depan_panel_atas_m2' => 0.5,
            'depan_panel_tengah' => '80x40',
            'depan_panel_tengah_m2' => 0.32,
            'depan_panel_bawah' => '100x30',
            'depan_panel_bawah_m2' => 0.3,

            // Ukuran panel kanan
            'kanan_panel_atas' => '100x50',
            'kanan_panel_atas_m2' => 0.5,
            'kanan_panel_tengah' => '80x40',
            'kanan_panel_tengah_m2' => 0.32,
            'kanan_panel_bawah' => '100x30',
            'kanan_panel_bawah_m2' => 0.3,

            // Ukuran panel kiri
            'kiri_panel_atas' => '100x50',
            'kiri_panel_atas_m2' => 0.5,
            'kiri_panel_tengah' => '80x40',
            'kiri_panel_tengah_m2' => 0.32,
            'kiri_panel_bawah' => '100x30',
            'kiri_panel_bawah_m2' => 0.3,

            // Total area branding
            'total_area_branding' => 3.06,
            'memenuhi_kriteria' => true,

            // Status
            'status' => 'approved',
            'alasan_reject' => null,
            'approved_at' => now(),
            'approved_by' => 1, // id user admin

            // Relasi
            'kota_id' => 1, // id kota Bogor
            'submitted_by' => 2, // id user PIC Lapangan
        ]);
    }
}
