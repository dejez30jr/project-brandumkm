<?php

namespace Tests\Unit\Observers;

use App\Models\Kota;
use App\Models\Notifikasi;
use App\Models\Umkm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UmkmObserverTest extends TestCase
{
    use RefreshDatabase;

    private function setup5Roles(): array
    {
        User::query()->delete();
        $kota = Kota::create(['nama' => 'Kota ' . uniqid()]);
        return [
            'kota'      => $kota,
            'admin'     => User::factory()->create(['role' => 'admin']),
            'client'    => User::factory()->create(['role' => 'client']),
            'pic'       => User::factory()->create(['role' => 'pic_lapangan', 'kota_id' => $kota->id]),
            'designer'  => User::factory()->create(['role' => 'design']),
            'pasang'    => User::factory()->create(['role' => 'team_pasang']),
        ];
    }

    private function makeUmkm(array $roles, array $overrides = []): Umkm
    {
        return Umkm::create(array_merge([
            'nama_pemilik' => 'Owner', 'nama_usaha' => 'Usaha',
            'alamat_usaha' => 'Jl. Test', 'no_wa' => '08' . rand(100000000, 999999999),
            'kota_id' => $roles['kota']->id, 'submitted_by' => $roles['pic']->id,
            'status' => 'pending',
        ], $overrides));
    }

    // PRD §5.4: PIC submit → client + admin dapat notif umkm_baru
    public function test_created_notifies_client_and_admin(): void
    {
        $r = $this->setup5Roles();
        Notifikasi::query()->delete();

        $umkm = $this->makeUmkm($r);

        $this->assertDatabaseHas('notifikasis', ['user_id' => $r['client']->id, 'tipe' => 'umkm_baru']);
        $this->assertDatabaseHas('notifikasis', ['user_id' => $r['admin']->id, 'tipe' => 'umkm_baru']);
        $this->assertDatabaseMissing('notifikasis', ['user_id' => $r['pic']->id, 'tipe' => 'umkm_baru']);
        $this->assertDatabaseMissing('notifikasis', ['user_id' => $r['designer']->id, 'tipe' => 'umkm_baru']);
    }

    // PRD §4: approved → otomatis berubah ke menunggu_didesain
    public function test_approved_auto_transitions_to_menunggu_didesain(): void
    {
        $r = $this->setup5Roles();
        $umkm = $this->makeUmkm($r);

        $umkm->update(['status' => 'approved']);
        $umkm->refresh();

        $this->assertEquals(Umkm::STATUS_MENUNGGU_DIDESAIN, $umkm->status);
    }

    // PRD §5.4: approved → designer + PIC + admin dapat notif
    public function test_approved_notifies_designer_pic_and_admin(): void
    {
        $r = $this->setup5Roles();
        $umkm = $this->makeUmkm($r);
        Notifikasi::query()->delete();

        $umkm->update(['status' => 'approved']);

        $this->assertDatabaseHas('notifikasis', ['user_id' => $r['designer']->id, 'tipe' => 'perlu_design']);
        $this->assertDatabaseHas('notifikasis', ['user_id' => $r['pic']->id, 'tipe' => 'umkm_approved']);
        $this->assertDatabaseHas('notifikasis', ['user_id' => $r['admin']->id, 'tipe' => 'umkm_approved']);
    }

    // PRD §5.4: rejected → PIC + admin dapat notif
    public function test_rejected_notifies_pic_and_admin(): void
    {
        $r = $this->setup5Roles();
        $umkm = $this->makeUmkm($r);
        Notifikasi::query()->delete();

        $umkm->update(['status' => 'rejected', 'alasan_reject' => 'Terlalu jauh']);

        $this->assertDatabaseHas('notifikasis', ['user_id' => $r['pic']->id, 'tipe' => 'umkm_rejected']);
        $this->assertDatabaseHas('notifikasis', ['user_id' => $r['admin']->id, 'tipe' => 'umkm_rejected']);
        $this->assertDatabaseMissing('notifikasis', ['user_id' => $r['client']->id, 'tipe' => 'umkm_rejected']);
    }

    // PRD §3.1: alasan reject tampil di notifikasi PIC
    public function test_rejected_notification_contains_reason(): void
    {
        $r = $this->setup5Roles();
        $umkm = $this->makeUmkm($r);
        Notifikasi::query()->delete();

        $umkm->update(['status' => 'rejected', 'alasan_reject' => 'Lokasi tidak strategis']);

        $notif = Notifikasi::where('user_id', $r['pic']->id)->where('tipe', 'umkm_rejected')->first();
        $this->assertNotNull($notif);
        $this->assertStringContainsString('Lokasi tidak strategis', $notif->pesan);
    }
}
