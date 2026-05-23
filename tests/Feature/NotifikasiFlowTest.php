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

    private User $admin;
    private User $client;
    private User $pic;
    private User $designer;
    private User $teamPasang;
    private Kota $kota;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kota = Kota::create(['nama' => 'Jogja']);
        $this->admin = User::create(['name' => 'Admin', 'email' => 'admin@t.com', 'password' => bcrypt('pw'), 'role' => 'admin', 'is_active' => true]);
        $this->client = User::create(['name' => 'Client', 'email' => 'client@t.com', 'password' => bcrypt('pw'), 'role' => 'client', 'is_active' => true]);
        $this->pic = User::create(['name' => 'PIC', 'email' => 'pic@t.com', 'password' => bcrypt('pw'), 'role' => 'pic_lapangan', 'kota_id' => $this->kota->id, 'is_active' => true]);
        $this->designer = User::create(['name' => 'Designer', 'email' => 'design@t.com', 'password' => bcrypt('pw'), 'role' => 'design', 'is_active' => true]);
        $this->teamPasang = User::create(['name' => 'Pasang', 'email' => 'pasang@t.com', 'password' => bcrypt('pw'), 'role' => 'team_pasang', 'is_active' => true]);
    }

    public function test_pic_submit_umkm_notifies_admin_and_client(): void
    {
        $umkm = Umkm::create([
            'nama_pemilik' => 'Test', 'nama_usaha' => 'Warung',
            'alamat_usaha' => 'Jl. Test', 'no_wa' => '081',
            'kota_id' => $this->kota->id, 'submitted_by' => $this->pic->id,
        ]);

        $this->assertDatabaseHas('notifikasis', ['user_id' => $this->admin->id, 'tipe' => 'umkm_baru']);
        $this->assertDatabaseHas('notifikasis', ['user_id' => $this->client->id, 'tipe' => 'umkm_baru']);
        $this->assertDatabaseMissing('notifikasis', ['user_id' => $this->pic->id, 'tipe' => 'umkm_baru']);
    }

    public function test_client_reject_umkm_notifies_pic(): void
    {
        $umkm = Umkm::create([
            'nama_pemilik' => 'Test', 'nama_usaha' => 'Warung Reject',
            'alamat_usaha' => 'Jl. Test', 'no_wa' => '082',
            'kota_id' => $this->kota->id, 'submitted_by' => $this->pic->id,
        ]);

        // Client reject
        $umkm->update(['status' => 'rejected', 'alasan_reject' => 'Terlalu jauh dari Alfamart']);

        // PIC HARUS dapat notifikasi reject
        $notif = Notifikasi::where('user_id', $this->pic->id)->where('tipe', 'umkm_rejected')->first();
        $this->assertNotNull($notif, 'Notifikasi reject TIDAK terkirim ke PIC!');
        $this->assertStringContainsString('Terlalu jauh dari Alfamart', $notif->pesan);
        $this->assertStringContainsString('Warung Reject', $notif->pesan);
    }

    public function test_client_approve_umkm_notifies_pic_and_designers(): void
    {
        $umkm = Umkm::create([
            'nama_pemilik' => 'Test', 'nama_usaha' => 'Warung OK',
            'alamat_usaha' => 'Jl. Test', 'no_wa' => '083',
            'kota_id' => $this->kota->id, 'submitted_by' => $this->pic->id,
        ]);

        $umkm->update(['status' => 'approved', 'approved_at' => now(), 'approved_by' => $this->client->id]);

        // PIC dapat notifikasi approved
        $this->assertDatabaseHas('notifikasis', ['user_id' => $this->pic->id, 'tipe' => 'umkm_approved']);
        // Designer dapat notifikasi perlu_design
        $this->assertDatabaseHas('notifikasis', ['user_id' => $this->designer->id, 'tipe' => 'perlu_design']);
    }

    public function test_designer_submit_design_notifies_admin_and_client(): void
    {
        $umkm = Umkm::create([
            'nama_pemilik' => 'Test', 'nama_usaha' => 'Warung',
            'alamat_usaha' => 'Jl. Test', 'no_wa' => '084',
            'kota_id' => $this->kota->id, 'submitted_by' => $this->pic->id,
            'status' => 'approved',
        ]);

        UmkmDesign::create([
            'umkm_id' => $umkm->id, 'designer_id' => $this->designer->id,
            'file_path' => 'design.png', 'versi' => 1,
        ]);

        $this->assertDatabaseHas('notifikasis', ['user_id' => $this->admin->id, 'tipe' => 'design_baru']);
        $this->assertDatabaseHas('notifikasis', ['user_id' => $this->client->id, 'tipe' => 'design_baru']);
    }

    public function test_client_request_revision_notifies_designer(): void
    {
        $umkm = Umkm::create([
            'nama_pemilik' => 'Test', 'nama_usaha' => 'Warung',
            'alamat_usaha' => 'Jl. Test', 'no_wa' => '085',
            'kota_id' => $this->kota->id, 'submitted_by' => $this->pic->id,
            'status' => 'approved',
        ]);

        $design = UmkmDesign::create([
            'umkm_id' => $umkm->id, 'designer_id' => $this->designer->id,
            'file_path' => 'design.png', 'versi' => 1,
        ]);

        $design->update(['status' => 'revision_needed', 'catatan_revisi' => 'Warna kurang cerah']);

        $notif = Notifikasi::where('user_id', $this->designer->id)->where('tipe', 'perlu_revisi')->first();
        $this->assertNotNull($notif, 'Notifikasi revisi TIDAK terkirim ke designer!');
        $this->assertStringContainsString('Warna kurang cerah', $notif->pesan);
    }

    public function test_designer_submit_revision_notifies_admin_and_client(): void
    {
        $umkm = Umkm::create([
            'nama_pemilik' => 'Test', 'nama_usaha' => 'Warung',
            'alamat_usaha' => 'Jl. Test', 'no_wa' => '086',
            'kota_id' => $this->kota->id, 'submitted_by' => $this->pic->id,
            'status' => 'approved',
        ]);

        $design = UmkmDesign::create([
            'umkm_id' => $umkm->id, 'designer_id' => $this->designer->id,
            'file_path' => 'design.png', 'versi' => 1, 'status' => 'revision_needed',
        ]);

        $design->update(['status' => 'revised', 'file_path' => 'design-v2.png']);

        $this->assertDatabaseHas('notifikasis', ['user_id' => $this->admin->id, 'tipe' => 'revised']);
        $this->assertDatabaseHas('notifikasis', ['user_id' => $this->client->id, 'tipe' => 'revised']);
    }

    public function test_design_approved_notifies_team_pasang(): void
    {
        $umkm = Umkm::create([
            'nama_pemilik' => 'Test', 'nama_usaha' => 'Warung',
            'alamat_usaha' => 'Jl. Test', 'no_wa' => '087',
            'kota_id' => $this->kota->id, 'submitted_by' => $this->pic->id,
            'status' => 'approved',
        ]);

        $design = UmkmDesign::create([
            'umkm_id' => $umkm->id, 'designer_id' => $this->designer->id,
            'file_path' => 'design.png', 'versi' => 1,
        ]);

        $design->update(['status' => 'approved', 'approved_at' => now(), 'approved_by' => $this->client->id]);

        $this->assertDatabaseHas('notifikasis', ['user_id' => $this->teamPasang->id, 'tipe' => 'siap_pasang']);
    }

    public function test_each_notification_has_is_read_false_by_default(): void
    {
        Umkm::create([
            'nama_pemilik' => 'Test', 'nama_usaha' => 'Warung',
            'alamat_usaha' => 'Jl. Test', 'no_wa' => '088',
            'kota_id' => $this->kota->id, 'submitted_by' => $this->pic->id,
        ]);

        $notifs = Notifikasi::all();
        foreach ($notifs as $notif) {
            $this->assertFalse($notif->is_read);
        }
    }

    public function test_notification_count_per_role_is_correct(): void
    {
        $umkm = Umkm::create([
            'nama_pemilik' => 'Test', 'nama_usaha' => 'Warung',
            'alamat_usaha' => 'Jl. Test', 'no_wa' => '089',
            'kota_id' => $this->kota->id, 'submitted_by' => $this->pic->id,
        ]);

        // Setelah create: admin=1, client=1, pic=0, designer=0, team_pasang=0
        $this->assertEquals(1, Notifikasi::where('user_id', $this->admin->id)->count());
        $this->assertEquals(1, Notifikasi::where('user_id', $this->client->id)->count());
        $this->assertEquals(0, Notifikasi::where('user_id', $this->pic->id)->count());

        // Approve: designer=1, pic=1
        $umkm->update(['status' => 'approved']);
        $this->assertEquals(1, Notifikasi::where('user_id', $this->designer->id)->count());
        $this->assertEquals(1, Notifikasi::where('user_id', $this->pic->id)->count());
    }
}
