<?php

namespace Tests\Unit\Services;

use App\Models\Kota;
use App\Models\Notifikasi;
use App\Models\Umkm;
use App\Models\UmkmDesign;
use App\Models\User;
use App\Services\NotifikasiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotifikasiServiceTest extends TestCase
{
    use RefreshDatabase;

    private array $r;
    private Umkm $umkm;
    private UmkmDesign $design;

    protected function setUp(): void
    {
        parent::setUp();

        User::query()->delete();
        $kota = Kota::create(['nama' => 'Kota Service']);
        $this->r = [
            'admin'    => User::factory()->create(['role' => 'admin']),
            'client'   => User::factory()->create(['role' => 'client']),
            'pic'      => User::factory()->create(['role' => 'pic_lapangan', 'kota_id' => $kota->id]),
            'designer' => User::factory()->create(['role' => 'design']),
            'pasang'   => User::factory()->create(['role' => 'team_pasang']),
        ];

        $this->umkm = Umkm::withoutEvents(fn () => Umkm::create([
            'nama_pemilik' => 'Owner', 'nama_usaha' => 'Usaha',
            'alamat_usaha' => 'Jl. Test', 'no_wa' => '081234',
            'kota_id' => $kota->id, 'submitted_by' => $this->r['pic']->id, 'status' => 'pending',
        ]));

        $this->design = UmkmDesign::withoutEvents(fn () => UmkmDesign::create([
            'umkm_id' => $this->umkm->id, 'designer_id' => $this->r['designer']->id,
            'file_path' => 'design.png', 'status' => 'pending',
        ]));
    }

    // PRD §5.4: UMKM baru → client + admin
    public function test_notify_new_umkm_sends_to_client_and_admin(): void
    {
        Notifikasi::query()->delete();
        NotifikasiService::notifyNewUmkm($this->umkm);

        $this->assertDatabaseHas('notifikasis', ['user_id' => $this->r['client']->id, 'tipe' => 'umkm_baru']);
        $this->assertDatabaseHas('notifikasis', ['user_id' => $this->r['admin']->id, 'tipe' => 'umkm_baru']);
        $this->assertDatabaseMissing('notifikasis', ['user_id' => $this->r['pic']->id, 'tipe' => 'umkm_baru']);
    }

    // PRD §5.4: UMKM approved → designer (new task) + PIC + admin
    public function test_notify_umkm_approved_sends_to_designer_pic_admin(): void
    {
        Notifikasi::query()->delete();
        NotifikasiService::notifyUmkmApproved($this->umkm);

        $this->assertDatabaseHas('notifikasis', ['user_id' => $this->r['designer']->id, 'tipe' => 'perlu_design']);
        $this->assertDatabaseHas('notifikasis', ['user_id' => $this->r['pic']->id, 'tipe' => 'umkm_approved']);
        $this->assertDatabaseHas('notifikasis', ['user_id' => $this->r['admin']->id, 'tipe' => 'umkm_approved']);
    }

    // PRD §5.4: UMKM rejected → PIC + admin
    public function test_notify_umkm_rejected_sends_to_pic_and_admin(): void
    {
        $this->umkm->alasan_reject = 'Tidak sesuai';
        Notifikasi::query()->delete();
        NotifikasiService::notifyUmkmRejected($this->umkm);

        $this->assertDatabaseHas('notifikasis', ['user_id' => $this->r['pic']->id, 'tipe' => 'umkm_rejected']);
        $this->assertDatabaseHas('notifikasis', ['user_id' => $this->r['admin']->id, 'tipe' => 'umkm_rejected']);
        $this->assertDatabaseMissing('notifikasis', ['user_id' => $this->r['client']->id, 'tipe' => 'umkm_rejected']);
    }

    // PRD §5.4: design baru → client + admin
    public function test_notify_new_design_sends_to_client_and_admin(): void
    {
        Notifikasi::query()->delete();
        NotifikasiService::notifyNewDesign($this->design);

        $this->assertDatabaseHas('notifikasis', ['user_id' => $this->r['client']->id, 'tipe' => 'design_baru']);
        $this->assertDatabaseHas('notifikasis', ['user_id' => $this->r['admin']->id, 'tipe' => 'design_baru']);
        $this->assertDatabaseMissing('notifikasis', ['user_id' => $this->r['designer']->id, 'tipe' => 'design_baru']);
    }

    // PRD §5.4: revisi → hanya designer aslinya
    public function test_notify_design_revision_sends_only_to_original_designer(): void
    {
        $otherDesigner = User::factory()->create(['role' => 'design']);
        $this->design->catatan_revisi = 'Warna salah';
        Notifikasi::query()->delete();
        NotifikasiService::notifyDesignRevision($this->design);

        $this->assertDatabaseHas('notifikasis', ['user_id' => $this->r['designer']->id, 'tipe' => 'perlu_revisi']);
        $this->assertDatabaseMissing('notifikasis', ['user_id' => $otherDesigner->id, 'tipe' => 'perlu_revisi']);
    }

    // PRD §5.4: revisi di-submit → client + admin
    public function test_notify_design_revised_sends_to_client_and_admin(): void
    {
        Notifikasi::query()->delete();
        NotifikasiService::notifyDesignRevised($this->design);

        $this->assertDatabaseHas('notifikasis', ['user_id' => $this->r['client']->id, 'tipe' => 'revised']);
        $this->assertDatabaseHas('notifikasis', ['user_id' => $this->r['admin']->id, 'tipe' => 'revised']);
    }

    // PRD §5.4: design approved → designer aslinya dapat notif
    public function test_notify_design_approved_sends_to_designer(): void
    {
        Notifikasi::query()->delete();
        NotifikasiService::notifyDesignApproved($this->design);

        $this->assertDatabaseHas('notifikasis', ['user_id' => $this->r['designer']->id, 'tipe' => 'design_approved']);
        $this->assertDatabaseHas('notifikasis', ['user_id' => $this->r['admin']->id, 'tipe' => 'design_approved']);
    }

    // PRD §5.4: design approved → team_pasang dapat notif siap_pasang
    public function test_notify_team_pasang_design_approved(): void
    {
        Notifikasi::query()->delete();
        NotifikasiService::notifyTeamPasangDesignApproved($this->design);

        $this->assertDatabaseHas('notifikasis', ['user_id' => $this->r['pasang']->id, 'tipe' => 'siap_pasang']);
        $this->assertDatabaseHas('notifikasis', ['user_id' => $this->r['admin']->id, 'tipe' => 'siap_pasang']);
    }

    // Semua notifikasi default is_read = false
    public function test_all_notifications_default_is_read_false(): void
    {
        Notifikasi::query()->delete();
        NotifikasiService::notifyNewUmkm($this->umkm);

        Notifikasi::all()->each(fn ($n) => $this->assertFalse($n->is_read));
    }
}
