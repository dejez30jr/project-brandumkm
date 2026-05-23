<?php

namespace Tests\Unit\Services;

use App\Models\Kota;
use App\Models\Notifikasi;
use App\Models\Umkm;
use App\Models\UmkmDesign;
use App\Models\User;
use App\Services\NotifikasiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NotifikasiServiceTest extends TestCase
{
    use RefreshDatabase;

    private Kota $kota;
    private User $pic;
    private Umkm $umkm;

    private function clearNotifikasi(): void
    {
        DB::table('notifikasi_user')->delete();
        Notifikasi::query()->delete();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->kota = Kota::create(['nama' => 'Kota Service']);
        $this->pic = User::factory()->create(['role' => 'pic_lapangan', 'kota_id' => $this->kota->id]);

        $this->umkm = Umkm::withoutEvents(function () {
            return Umkm::create([
                'nama_pemilik' => 'Owner',
                'nama_usaha' => 'Usaha Service',
                'alamat_usaha' => 'Jl. Test',
                'no_wa' => '081234',
                'kota_id' => $this->kota->id,
                'submitted_by' => $this->pic->id,
                'status' => 'pending',
            ]);
        });
    }

    public function test_notify_new_umkm_sends_to_admin_and_client(): void
    {
        User::whereIn('role', ['admin', 'client'])->delete();

        $admin = User::factory()->create(['role' => 'admin']);
        $client = User::factory()->create(['role' => 'client']);
        User::factory()->create(['role' => 'design']);

        $this->clearNotifikasi();
        NotifikasiService::notifyNewUmkm($this->umkm);

        $notifs = Notifikasi::where('tipe', 'umkm_baru')->get();
        $this->assertCount(2, $notifs);
        $recipientIds = $notifs->pluck('user_id')->toArray();
        $this->assertContains($admin->id, $recipientIds);
        $this->assertContains($client->id, $recipientIds);
    }

    public function test_notify_umkm_approved_sends_to_designers_and_submitter(): void
    {
        User::where('role', 'design')->delete();

        $designer = User::factory()->create(['role' => 'design']);

        $this->clearNotifikasi();
        NotifikasiService::notifyUmkmApproved($this->umkm);

        $notifs = Notifikasi::all();
        $this->assertCount(2, $notifs); // 1 designer + 1 submitter

        $recipientIds = $notifs->pluck('user_id')->toArray();
        $this->assertContains($designer->id, $recipientIds);
        $this->assertContains($this->pic->id, $recipientIds);
    }

    public function test_notify_umkm_rejected_sends_to_submitter(): void
    {
        $this->umkm->alasan_reject = 'Tidak sesuai';

        $this->clearNotifikasi();
        NotifikasiService::notifyUmkmRejected($this->umkm);

        $notifs = Notifikasi::where('tipe', 'umkm_rejected')->get();
        $this->assertCount(1, $notifs);
        $this->assertEquals($this->pic->id, $notifs->first()->user_id);
    }

    public function test_notify_new_design_sends_to_admin_and_client(): void
    {
        User::whereIn('role', ['admin', 'client'])->delete();

        $admin = User::factory()->create(['role' => 'admin']);
        $client = User::factory()->create(['role' => 'client']);
        $designer = User::factory()->create(['role' => 'design']);

        $design = UmkmDesign::withoutEvents(function () use ($designer) {
            return UmkmDesign::create([
                'umkm_id' => $this->umkm->id,
                'designer_id' => $designer->id,
                'file_path' => 'designs/test.png',
                'status' => 'pending',
            ]);
        });

        $this->clearNotifikasi();
        NotifikasiService::notifyNewDesign($design);

        $notifs = Notifikasi::where('tipe', 'design_baru')->get();
        $this->assertCount(2, $notifs);
        $recipientIds = $notifs->pluck('user_id')->toArray();
        $this->assertContains($admin->id, $recipientIds);
        $this->assertContains($client->id, $recipientIds);
    }

    public function test_notify_design_revision_sends_to_designer(): void
    {
        $designer = User::factory()->create(['role' => 'design']);

        $design = UmkmDesign::withoutEvents(function () use ($designer) {
            return UmkmDesign::create([
                'umkm_id' => $this->umkm->id,
                'designer_id' => $designer->id,
                'file_path' => 'designs/test.png',
                'status' => 'revision_needed',
                'catatan_revisi' => 'Warna salah',
            ]);
        });

        $this->clearNotifikasi();
        NotifikasiService::notifyDesignRevision($design);

        $notifs = Notifikasi::where('tipe', 'perlu_revisi')->get();
        $this->assertCount(1, $notifs);
        $this->assertEquals($designer->id, $notifs->first()->user_id);
    }

    public function test_notify_design_revised_sends_to_admin_and_client(): void
    {
        User::whereIn('role', ['admin', 'client'])->delete();

        $admin = User::factory()->create(['role' => 'admin']);
        $client = User::factory()->create(['role' => 'client']);
        $designer = User::factory()->create(['role' => 'design']);

        $design = UmkmDesign::withoutEvents(function () use ($designer) {
            return UmkmDesign::create([
                'umkm_id' => $this->umkm->id,
                'designer_id' => $designer->id,
                'file_path' => 'designs/test.png',
                'status' => 'revised',
            ]);
        });

        $this->clearNotifikasi();
        NotifikasiService::notifyDesignRevised($design);

        $notifs = Notifikasi::where('tipe', 'revised')->get();
        $this->assertCount(2, $notifs);
        $recipientIds = $notifs->pluck('user_id')->toArray();
        $this->assertContains($admin->id, $recipientIds);
        $this->assertContains($client->id, $recipientIds);
    }

    public function test_notify_team_pasang_design_approved(): void
    {
        User::where('role', 'team_pasang')->delete();

        $teamPasang1 = User::factory()->create(['role' => 'team_pasang']);
        $teamPasang2 = User::factory()->create(['role' => 'team_pasang']);
        $designer = User::factory()->create(['role' => 'design']);

        $design = UmkmDesign::withoutEvents(function () use ($designer) {
            return UmkmDesign::create([
                'umkm_id' => $this->umkm->id,
                'designer_id' => $designer->id,
                'file_path' => 'designs/test.png',
                'status' => 'approved',
            ]);
        });

        $this->clearNotifikasi();
        NotifikasiService::notifyTeamPasangDesignApproved($design);

        $notifs = Notifikasi::where('tipe', 'siap_pasang')->get();
        $this->assertCount(2, $notifs);
        $recipientIds = $notifs->pluck('user_id')->toArray();
        $this->assertContains($teamPasang1->id, $recipientIds);
        $this->assertContains($teamPasang2->id, $recipientIds);
    }
}
