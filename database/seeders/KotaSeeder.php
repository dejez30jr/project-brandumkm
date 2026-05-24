<?php

namespace Database\Seeders;

use App\Models\Kota;
use Illuminate\Database\Seeder;

class KotaSeeder extends Seeder
{
    public function run(): void
    {
        $kotas = [
            'Bogor',
            'Depok',
            'Jakarta',
            'Tangerang',
            'Bekasi',
            'Sukabumi',
            'Bandung',
            'Cilegon',
            'Surabaya',
            'Malang',
            'Semarang',
            'Solo',
            'Jogja',
            'Bali',
        ];

        foreach ($kotas as $nama) {
            Kota::updateOrCreate(['nama' => $nama]);
        }
    }
}
