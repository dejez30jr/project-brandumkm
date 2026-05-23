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
            'Tangerang',
            'Jakarta',
            'Bekasi',
            'Cirebon',
            'Sukabumi',
            'Bandung',
            'Surabaya',
            'Malang',
            'Bali',
            'Jogja',
            'Semarang',
            'Solo',
        ];

        foreach ($kotas as $nama) {
            Kota::updateOrCreate(['nama' => $nama]);
        }
    }
}
