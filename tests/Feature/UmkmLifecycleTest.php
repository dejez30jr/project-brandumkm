<?php

namespace Tests\Feature;

use App\Models\Kota;
use App\Models\Notifikasi;
use App\Models\Umkm;
use App\Models\UmkmDesign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UmkmLifecycleTest extends TestCase
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

    // PRD §4 full workflow: pending → menunggu_didesain → design_review → waiting_installation → terbranding_final
    public function test_full_prd_workflow(): void
    {
        $r = $this->r;

        // Step 1: PIC submit → pending
        $this->actingAs($r['pic']);
        $umkm = Umkm::create([
            'nama_pemilik' => 'Budi', 'nama_usaha' => 'Warung Budi',
            'alamat_usaha' => 'Jl. Merdeka', 'no_wa' => '081234567',
            'kota_id' => $r['kota']->id, 'status' => 'pending',
            'depan_atas_w' => 100, 'depan_atas_h' => 200, // 2.0 m²
        ]);

        $this->assertEquals('pending', $umkm->status);
        $this->assertEquals($r['pic']->id, $umkm->submitted_by);
        $this->assertTrue((bool) $umkm->memenuhi_kriteria);

        // Step 2: Client approve → approved → auto menunggu_didesain
        Notifikasi::query()->delete();
        $umkm->update(['status' => 'approved', 'approved_by' => $r['client']->id, 'approved_at' => now()]);
        $umkm->refresh();
        $this->assertEquals(Umkm::STATUS_MENUNGGU_DIDESAIN, $umkm->status);
        $this->assertDatabaseHas('notifikasis', ['user_id' => $r['pic']->id, 'tipe' => 'umkm_approved']);
        $this->assertDatabaseHas('notifikasis', ['user_id' => $r['designer']->id, 'tipe' => 'perlu_design']);

        // Step 3: Designer upload design → design_review
        Notifikasi::query()->delete();
        $design = UmkmDesign::create([
            'umkm_id' => $umkm->id, 'designer_id' => $r['designer']->id,
            'nama_desainer' => 'Andi', 'file_path' => 'fa.png',
            'gerobak_depan' => 'depan.png', 'gerobak_kiri' => 'kiri.png', 'gerobak_kanan' => 'kanan.png',
        ]);
        $umkm->refresh();
        $this->assertEquals(Umkm::STATUS_DESIGN_REVIEW, $umkm->status);
        $this->assertDatabaseHas('notifikasis', ['user_id' => $r['client']->id, 'tipe' => 'design_baru']);

        // Step 4: Client minta revisi → revision_needed
        Notifikasi::query()->delete();
        $design->update(['status' => 'revision_needed', 'catatan_revisi' => 'Warna kurang cerah']);
        $this->assertDatabaseHas('notifikasis', ['user_id' => $r['designer']->id, 'tipe' => 'perlu_revisi']);

        // Step 5: Designer re-upload → revised → design_review
        Notifikasi::query()->delete();
        $design->file_path = 'fa-v2.png';
        $design->status = 'revised';
        $design->save();
        $umkm->refresh();
        $this->assertEquals(Umkm::STATUS_DESIGN_REVIEW, $umkm->status);
        $this->assertDatabaseHas('notifikasis', ['user_id' => $r['client']->id, 'tipe' => 'revised']);

        // Step 6: Client approve design → waiting_installation
        Notifikasi::query()->delete();
        $design->update(['status' => 'approved', 'approved_at' => now(), 'approved_by' => $r['client']->id]);
        $umkm->refresh();
        $this->assertEquals(Umkm::STATUS_WAITING_INSTALLATION, $umkm->status);
        $this->assertDatabaseHas('notifikasis', ['user_id' => $r['designer']->id, 'tipe' => 'design_approved']);
        $this->assertDatabaseHas('notifikasis', ['user_id' => $r['pasang']->id, 'tipe' => 'siap_pasang']);

        // Step 7: Team pasang upload 4 foto → terbranding_final
        $umkm->update([
            'stiker_tampak_depan' => 'stiker/depan.jpg',
            'stiker_tampak_kanan' => 'stiker/kanan.jpg',
            'stiker_tampak_kiri'  => 'stiker/kiri.jpg',
            'foto_wide'           => 'stiker/wide.jpg',
            'tanggal_pasang'      => now()->toDateString(),
            'nama_team_pasang'    => $r['pasang']->name,
            'status'              => Umkm::STATUS_TERBRANDING_FINAL,
        ]);
        $umkm->refresh();
        $this->assertEquals(Umkm::STATUS_TERBRANDING_FINAL, $umkm->status);
        $this->assertNotNull($umkm->stiker_tampak_depan);
        $this->assertNotNull($umkm->nama_team_pasang);
    }

    // PRD §3.1: UMKM dengan area < 1.5m² tidak memenuhi kriteria
    public function test_umkm_below_minimum_area_not_eligible(): void
    {
        $this->actingAs($this->r['pic']);
        $umkm = Umkm::create([
            'nama_pemilik' => 'Kecil', 'nama_usaha' => 'Warung Kecil',
            'alamat_usaha' => 'Jl. Kecil', 'no_wa' => '089999',
            'kota_id' => $this->r['kota']->id,
            'depan_atas_w' => 50, 'depan_atas_h' => 50, // 0.25 m²
        ]);

        $this->assertFalse((bool) $umkm->memenuhi_kriteria);
        $this->assertEquals('0.25', $umkm->total_area_branding);
    }

    // PRD §3.5: Admin view-only — tidak bisa approve/reject (hanya client)
    public function test_only_client_role_can_approve(): void
    {
        // Verifikasi bahwa approve action hanya visible untuk isClient()
        $this->assertTrue($this->r['client']->isClient());
        $this->assertFalse($this->r['admin']->isClient());
        $this->assertFalse($this->r['pic']->isClient());
    }

    // PRD §5.1: Log Personalia tersimpan di UMKM Terbranding
    public function test_log_personalia_stored_on_terbranding(): void
    {
        $r = $this->r;
        $umkm = Umkm::withoutEvents(fn () => Umkm::create([
            'nama_pemilik' => 'Owner', 'nama_usaha' => 'Warung',
            'alamat_usaha' => 'Jl. Test', 'no_wa' => '081111',
            'kota_id' => $r['kota']->id, 'submitted_by' => $r['pic']->id,
        ]));

        $design = UmkmDesign::withoutEvents(fn () => UmkmDesign::create([
            'umkm_id' => $umkm->id, 'designer_id' => $r['designer']->id,
            'nama_desainer' => 'Andi Desainer', 'file_path' => 'fa.png',
        ]));

        Umkm::withoutEvents(fn () => $umkm->update([
            'nama_team_pasang' => 'Budi Pasang',
            'tanggal_pasang' => now()->toDateString(),
            'status' => Umkm::STATUS_TERBRANDING_FINAL,
        ]));

        $umkm->refresh();
        $this->assertEquals($r['pic']->id, $umkm->submitted_by);
        $this->assertEquals('Andi Desainer', $umkm->umkmDesign->nama_desainer);
        $this->assertEquals('Budi Pasang', $umkm->nama_team_pasang);
    }
}
