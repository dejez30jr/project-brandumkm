<?php

namespace Tests\Unit\Models;

use App\Models\Kota;
use App\Models\Notifikasi;
use App\Models\Umkm;
use App\Models\UmkmDesign;
use App\Models\User;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_admin(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->isClient());
    }

    public function test_is_client(): void
    {
        $user = User::factory()->create(['role' => 'client']);
        $this->assertTrue($user->isClient());
        $this->assertFalse($user->isAdmin());
    }

    public function test_is_design(): void
    {
        $user = User::factory()->create(['role' => 'design']);
        $this->assertTrue($user->isDesign());
        $this->assertFalse($user->isAdmin());
    }

    public function test_is_pic_lapangan(): void
    {
        $user = User::factory()->create(['role' => 'pic_lapangan']);
        $this->assertTrue($user->isPicLapangan());
        $this->assertFalse($user->isAdmin());
    }

    public function test_is_team_pasang(): void
    {
        $user = User::factory()->create(['role' => 'team_pasang']);
        $this->assertTrue($user->isTeamPasang());
        $this->assertFalse($user->isAdmin());
    }

    public function test_can_access_panel_when_active(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $panel = $this->createMock(Panel::class);
        $this->assertTrue($user->canAccessPanel($panel));
    }

    public function test_cannot_access_panel_when_inactive(): void
    {
        $user = User::factory()->create(['is_active' => false]);
        $panel = $this->createMock(Panel::class);
        $this->assertFalse($user->canAccessPanel($panel));
    }

    public function test_kota_relation(): void
    {
        $kota = Kota::create(['nama' => 'Kota User']);
        $user = User::factory()->create(['kota_id' => $kota->id]);
        $this->assertInstanceOf(Kota::class, $user->kota);
        $this->assertEquals($kota->id, $user->kota->id);
    }

    public function test_submitted_umkms_relation(): void
    {
        $kota = Kota::create(['nama' => 'Kota Rel']);
        $user = User::factory()->create(['role' => 'pic_lapangan', 'kota_id' => $kota->id]);

        Umkm::withoutEvents(function () use ($kota, $user) {
            Umkm::create([
                'nama_pemilik' => 'Owner',
                'nama_usaha' => 'Usaha',
                'alamat_usaha' => 'Jl. Test',
                'no_wa' => '081234',
                'kota_id' => $kota->id,
                'submitted_by' => $user->id,
                'status' => 'pending',
            ]);
        });

        $this->assertCount(1, $user->submittedUmkms);
    }

    public function test_designs_relation(): void
    {
        $kota = Kota::create(['nama' => 'Kota Design']);
        $designer = User::factory()->create(['role' => 'design']);
        $pic = User::factory()->create(['role' => 'pic_lapangan', 'kota_id' => $kota->id]);

        $umkm = Umkm::withoutEvents(function () use ($kota, $pic) {
            return Umkm::create([
                'nama_pemilik' => 'Owner',
                'nama_usaha' => 'Usaha',
                'alamat_usaha' => 'Jl. Test',
                'no_wa' => '081234',
                'kota_id' => $kota->id,
                'submitted_by' => $pic->id,
                'status' => 'approved',
            ]);
        });

        UmkmDesign::withoutEvents(function () use ($umkm, $designer) {
            UmkmDesign::create([
                'umkm_id' => $umkm->id,
                'designer_id' => $designer->id,
                'file_path' => 'designs/test.png',
                'status' => 'pending',
            ]);
        });

        $this->assertCount(1, $designer->designs);
    }

    public function test_notifikasis_relation(): void
    {
        $kota = Kota::create(['nama' => 'Kota Notif']);
        $user = User::factory()->create(['role' => 'admin']);
        $pic = User::factory()->create(['role' => 'pic_lapangan', 'kota_id' => $kota->id]);

        $umkm = Umkm::withoutEvents(function () use ($kota, $pic) {
            return Umkm::create([
                'nama_pemilik' => 'Owner',
                'nama_usaha' => 'Usaha',
                'alamat_usaha' => 'Jl. Test',
                'no_wa' => '081234',
                'kota_id' => $kota->id,
                'submitted_by' => $pic->id,
                'status' => 'pending',
            ]);
        });

        Notifikasi::create([
            'user_id' => $user->id,
            'judul' => 'Test',
            'pesan' => 'Test pesan',
            'tipe' => 'test',
            'notifiable_type' => Umkm::class,
            'notifiable_id' => $umkm->id,
        ]);

        $this->assertCount(1, $user->notifikasis);
    }

    public function test_read_notifications_relation(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $kota = Kota::create(['nama' => 'Kota RN']);
        $pic = User::factory()->create(['role' => 'pic_lapangan', 'kota_id' => $kota->id]);

        $umkm = Umkm::withoutEvents(function () use ($kota, $pic) {
            return Umkm::create([
                'nama_pemilik' => 'Owner',
                'nama_usaha' => 'Usaha',
                'alamat_usaha' => 'Jl. Test',
                'no_wa' => '081234',
                'kota_id' => $kota->id,
                'submitted_by' => $pic->id,
                'status' => 'pending',
            ]);
        });

        $notif = Notifikasi::create([
            'user_id' => $user->id,
            'judul' => 'Test',
            'pesan' => 'Test',
            'tipe' => 'test',
            'notifiable_type' => Umkm::class,
            'notifiable_id' => $umkm->id,
        ]);

        $user->readNotifications()->attach($notif->id, ['read_at' => now()]);
        $this->assertCount(1, $user->readNotifications);
    }
}
