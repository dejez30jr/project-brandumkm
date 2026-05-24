<?php

namespace Database\Seeders;

use App\Models\Kota;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $jogja = Kota::where('nama', 'Jogja')->first();
        $bandung = Kota::where('nama', 'Bandung')->first();

        $users = [
            ['name' => 'Super Admin', 'email' => 'admin@test.com', 'role' => 'admin', 'kota_id' => null],
            ['name' => 'Client HM', 'email' => 'client@test.com', 'role' => 'client', 'kota_id' => null],
            ['name' => 'PIC Jogja', 'email' => 'pic.jogja@test.com', 'role' => 'pic_lapangan', 'kota_id' => $jogja?->id],
            ['name' => 'PIC Bandung', 'email' => 'pic.bandung@test.com', 'role' => 'pic_lapangan', 'kota_id' => $bandung?->id],
            ['name' => 'Designer 1', 'email' => 'design@test.com', 'role' => 'design', 'kota_id' => null],
            ['name' => 'Team Pasang', 'email' => 'pasang@test.com', 'role' => 'team_pasang', 'kota_id' => null],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => Hash::make('password'),
                    'role' => $user['role'],
                    'kota_id' => $user['kota_id'],
                    'is_active' => true,
                ]
            );
        }
    }
}
