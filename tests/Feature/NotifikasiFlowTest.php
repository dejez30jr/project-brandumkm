<?php

namespace Tests\Feature;

use App\Models\Kota;
use App\Models\Notifikasi;
use App\Models\Umkm;
use App\Models\UmkmDesign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotifikasiFlowTest extends TestCase
{
    use RefreshDatabase;

    private array $r;

    protected function setUp(): void
    {
        parent::setUp();

        User::query()->delete();
        $kota = Kota::create(['nama' => 'Jogja']);
        $this->r = [
            'kota'     => $kota,
            'admin'    => User::factory()->create(['role' => 'admin']),
            'client'   => User::factory()->create(['role' => 'client']),
            'pic'      => User::factory()->create(['role' => 'pic_lapangan', 'kota_id' => $kota->id]),
            'designer' => User::factory()->create(['role' => 'design']),
            'pasang'   => User::factory()->create(['role' => 'team_pasang']),
        ];
    }

    private function makeUmkm(string $noWa = '081'): Umkm
    {
        return Umkm::create([
            'nama_pemilik' => 'Test', 'nama_usaha' => 'Warung',
            'alamat_usaha' => 'Jl. Test', 'no_wa' => $noWa,
            'kota_id' => $this->r['kota']->id, 'submitted_by' => $this->r['pic']->id,
        ]);
    }

    // PRD §5.4: PIC submit → client + admin dapat notif
    public function test_pic_submit_notifies_client_and_admin(): void
    {
        $this->makeUmkm('081');

        $this->assertDatabaseHas('notifikasis', ['user_id' => $this->r['admin']->id, 'tipe' => 'umkm_baru']);
        $this->assertDatabaseHas('notifikasis', ['user_id' => $this->r['client']->id, 'tipe' => 'umkm_baru']);
        $this->assertDatabaseMissing('notifikasis', ['user_id' => $this->r['pic']->id, 'tipe' => 'umkm_baru']);
    }

    // PRD §3.1: PIC dapat lihat alasan reject
    public function test_pic_receives_reject_reason_in_notification(): void
    {
        $umkm = $this->makeUmkm('082');
        Notifikasi::query()->delete();

        $umkm->update(['status' => 'rejected', 'alasan_reject' => 'Terlalu jauh dari Alfamart']);

        $notif = Notifikasi::where('user_id', $this->r['pic']->id)->where('tipe', 'umkm_rejected')->first();
        $this->assertNotNull($notif);
        $this->assertStringContainsString('Terlalu jauh dari Alfamart', $notif->pesan);
    }

    // PRD §5.4: approved → PIC + designer + admin
    public function test_approve_notifies_pic_designer_admin(): void
    {
        $umkm = $this->makeUmkm('083');
        Notifikasi::query()->delete();

        $umkm->update(['status' => 'approved', 'approved_at' => now(), 'approved_by' => $this->r['client']->id]);

        $this->assertDatabaseHas('notifikasis', ['user_id' => $this->r['pic']->id, 'tipe' => 'umkm_approved']);
        $this->assertDatabaseHas('notifikasis', ['user_id' => $this->r['designer']->id, 'tipe' => 'perlu_design']);
        $this->assertDatabaseHas('notifikasis', ['user_id' => $this->r['admin']->id, 'tipe' => 'umkm_approved']);
    }

    // PRD §5.4: design baru → client + admin
    public function test_new_design_notifies_client_and_admin(): void
    {
        $umkm = Umkm::withoutEvents(fn () => $this->makeUmkm('084'));
        Notifikasi::query()->delete();

        UmkmDesign::create([
            'umkm_id' => $umkm->id, 'designer_id' => $this->r['designer']->id,
            'file_path' => 'design.png',
        ]);

        $this->assertDatabaseHas('notifikasis', ['user_id' => $this->r['admin']->id, 'tipe' => 'design_baru']);
        $this->assertDatabaseHas('notifikasis', ['user_id' => $this->r['client']->id, 'tipe' => 'design_baru']);
    }

    // PRD §5.4: revisi → hanya designer aslinya
    public function test_revision_notifies_only_original_designer(): void
    {
        $umkm = Umkm::withoutEvents(fn () => $this->makeUmkm('085'));
        $design = UmkmDesign::withoutEvents(fn () => UmkmDesign::create([
            'umkm_id' => $umkm->id, 'designer_id' => $this->r['designer']->id, 'file_path' => 'design.png',
        ]));
        Notifikasi::query()->delete();

        $design->update(['status' => 'revision_needed', 'catatan_revisi' => 'Warna kurang cerah']);

        $notif = Notifikasi::where('user_id', $this->r['designer']->id)->where('tipe', 'perlu_revisi')->first();
        $this->assertNotNull($notif);
        $this->assertStringContainsString('Warna kurang cerah', $notif->pesan);
        $this->assertDatabaseMissing('notifikasis', ['user_id' => $this->r['client']->id, 'tipe' => 'perlu_revisi']);
    }

    // PRD §5.4: revisi di-submit → client + admin
    public function test_revised_notifies_client_and_admin(): void
    {
        $umkm = Umkm::withoutEvents(fn () => $this->makeUmkm('086'));
        $design = UmkmDesign::withoutEvents(fn () => UmkmDesign::create([
            'umkm_id' => $umkm->id, 'designer_id' => $this->r['designer']->id,
            'file_path' => 'design.png', 'status' => 'revision_needed',
        ]));
        Notifikasi::query()->delete();

        $design->update(['status' => 'revised', 'file_path' => 'design-v2.png']);

        $this->assertDatabaseHas('notifikasis', ['user_id' => $this->r['admin']->id, 'tipe' => 'revised']);
        $this->assertDatabaseHas('notifikasis', ['user_id' => $this->r['client']->id, 'tipe' => 'revised']);
    }

    // PRD §5.4: design approved → designer + team_pasang + admin
    public function test_design_approved_notifies_designer_pasang_admin(): void
    {
        $umkm = Umkm::withoutEvents(fn () => $this->makeUmkm('087'));
        $design = UmkmDesign::withoutEvents(fn () => UmkmDesign::create([
            'umkm_id' => $umkm->id, 'designer_id' => $this->r['designer']->id, 'file_path' => 'design.png',
        ]));
        Notifikasi::query()->delete();

        $design->update(['status' => 'approved', 'approved_at' => now(), 'approved_by' => $this->r['client']->id]);

        $this->assertDatabaseHas('notifikasis', ['user_id' => $this->r['designer']->id, 'tipe' => 'design_approved']);
        $this->assertDatabaseHas('notifikasis', ['user_id' => $this->r['pasang']->id, 'tipe' => 'siap_pasang']);
        $this->assertDatabaseHas('notifikasis', ['user_id' => $this->r['admin']->id, 'tipe' => 'design_approved']);
    }

    // Semua notifikasi default is_read = false
    public function test_notifications_default_unread(): void
    {
        $this->makeUmkm('088');
        Notifikasi::all()->each(fn ($n) => $this->assertFalse($n->is_read));
    }
}
