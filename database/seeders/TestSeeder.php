<?php

namespace Database\Seeders;

use App\Models\Kota;
use App\Models\Umkm;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan kota sudah ada
        $this->call(KotaSeeder::class);

        $kotaJogja = Kota::where('nama', 'Jogja')->first();
        $kotaBandung = Kota::where('nama', 'Bandung')->first();

        // Buat user untuk setiap role
        $users = [
            ['name' => 'Admin Test', 'email' => 'admin@test.com', 'role' => 'admin', 'kota_id' => null],
            ['name' => 'Client Test', 'email' => 'client@test.com', 'role' => 'client', 'kota_id' => null],
            ['name' => 'PIC Jogja', 'email' => 'pic.jogja@test.com', 'role' => 'pic_lapangan', 'kota_id' => $kotaJogja->id],
            ['name' => 'PIC Bandung', 'email' => 'pic.bandung@test.com', 'role' => 'pic_lapangan', 'kota_id' => $kotaBandung->id],
            ['name' => 'Designer 1', 'email' => 'design@test.com', 'role' => 'design', 'kota_id' => null],
            ['name' => 'Team Pasang', 'email' => 'pasang@test.com', 'role' => 'team_pasang', 'kota_id' => null],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                array_merge($data, ['password' => Hash::make('password'), 'is_active' => true])
            );
        }

        // Sample UMKM
        $picJogja = User::where('email', 'pic.jogja@test.com')->first();

        Umkm::updateOrCreate(
            ['no_wa' => '083159618881'],
            [
                'nama_pemilik' => 'Sutirah',
                'nama_usaha' => 'Aneka Gorengan',
                'alamat_usaha' => 'Jl. Wijoseno No.149, Ngebel, Tamantirto, Kec. Kasihan, Kabupaten Bantul, DIY 55183',
                'jam_buka' => '14:00',
                'jam_tutup' => '21:00',
                'request_text' => 'Aneka Gorengan UMY',
                'catatan' => 'Lokasi sebelah Alfamart dan area Universitas Muhammadiyah Yogyakarta UMY',
                'latitude' => -7.79530000,
                'longitude' => 110.32410000,
                'sharelock_url' => 'https://www.google.com/maps?q=-7.7953,110.3241',
                'depan_atas_w' => 163,
                'depan_atas_h' => 20,
                'depan_bawah_w' => 163,
                'depan_bawah_h' => 62,
                'kiri_atas_w' => 54,
                'kiri_atas_h' => 20,
                'kiri_bawah_w' => 54,
                'kiri_bawah_h' => 62,
                'kanan_atas_w' => 54,
                'kanan_atas_h' => 20,
                'kanan_bawah_w' => 49,
                'kanan_bawah_h' => 58,
                'status' => 'pending',
                'kota_id' => $kotaJogja->id,
                'submitted_by' => $picJogja->id,
            ]
        );
    }
}
