<?php

namespace Tests\Unit\Observers;

use App\Models\Kota;
use App\Models\Notifikasi;
use App\Models\Umkm;
use App\Models\UmkmDesign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UmkmDesignObserverTest extends TestCase
{
    use RefreshDatabase;

    private function clearNotifikasi(): void
    {
        DB::table('notifikasi_user')->delete();
        Notifikasi::query()->delete();
    }

    private function setupUmkmAndDesigner(): array
    {
        $kota = Kota::create(['nama' => 'Kota DO ' . uniqid()]);
        $pic = User::factory()->create(['role' => 'pic_lapangan', 'kota_id' => $kota->id]);
        $designer = User::factory()->create(['role' => 'design']);

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

        return [$umkm, $designer, $kota];
    }

    public function test_created_updates_umkm_status_to_design_review(): void
    {
        [$umkm, $designer] = $this->setupUmkmAndDesigner();

        UmkmDesign::create([
            'umkm_id' => $umkm->id,
            'designer_id' => $designer->id,
            'file_path' => 'designs/test.png',
            'status' => 'pending',
        ]);

        $umkm->refresh();
        $this->assertEquals('design_review', $umkm->status);
    }

    public function test_created_sends_notification(): void
    {
        User::whereIn('role', ['admin', 'client'])->delete();

        [$umkm, $designer] = $this->setupUmkmAndDesigner();
        $admin = User::factory()->create(['role' => 'admin']);
        $client = User::factory()->create(['role' => 'client']);

        $this->clearNotifikasi();

        UmkmDesign::create([
            'umkm_id' => $umkm->id,
            'designer_id' => $designer->id,
            'file_path' => 'designs/test.png',
            'status' => 'pending',
        ]);

        $notifs = Notifikasi::where('tipe', 'design_baru')->get();
        $this->assertCount(2, $notifs);
        $recipientIds = $notifs->pluck('user_id')->toArray();
        $this->assertContains($admin->id, $recipientIds);
        $this->assertContains($client->id, $recipientIds);
    }

    public function test_updating_resets_status_when_only_file_changes(): void
    {
        [$umkm, $designer] = $this->setupUmkmAndDesigner();

        $design = UmkmDesign::withoutEvents(function () use ($umkm, $designer) {
            return UmkmDesign::create([
                'umkm_id' => $umkm->id,
                'designer_id' => $designer->id,
                'file_path' => 'designs/v1.png',
                'status' => 'revision_needed',
                'catatan_revisi' => 'Perlu perbaikan',
            ]);
        });

        // Change only file_path — observer should reset status to pending
        $design->file_path = 'designs/v2.png';
        $design->save();

        $design->refresh();
        $this->assertEquals('pending', $design->status);
        $this->assertNull($design->catatan_revisi);
    }

    public function test_updating_keeps_status_when_status_explicitly_set(): void
    {
        [$umkm, $designer] = $this->setupUmkmAndDesigner();

        $design = UmkmDesign::withoutEvents(function () use ($umkm, $designer) {
            return UmkmDesign::create([
                'umkm_id' => $umkm->id,
                'designer_id' => $designer->id,
                'file_path' => 'designs/v1.png',
                'status' => 'pending',
            ]);
        });

        // Change both file_path AND status — observer should NOT reset
        $design->file_path = 'designs/v2.png';
        $design->status = 'revised';
        $design->save();

        $design->refresh();
        $this->assertEquals('revised', $design->status);
    }

    public function test_updated_sends_revision_needed_notification(): void
    {
        [$umkm, $designer] = $this->setupUmkmAndDesigner();

        $design = UmkmDesign::withoutEvents(function () use ($umkm, $designer) {
            return UmkmDesign::create([
                'umkm_id' => $umkm->id,
                'designer_id' => $designer->id,
                'file_path' => 'designs/v1.png',
                'status' => 'pending',
            ]);
        });

        $this->clearNotifikasi();

        $design->status = 'revision_needed';
        $design->catatan_revisi = 'Warna kurang cerah';
        $design->save();

        $notif = Notifikasi::where('tipe', 'perlu_revisi')->first();
        $this->assertNotNull($notif);
        $this->assertEquals($designer->id, $notif->user_id);
    }

    public function test_updated_sends_revised_notification(): void
    {
        User::whereIn('role', ['admin', 'client'])->delete();

        [$umkm, $designer] = $this->setupUmkmAndDesigner();
        $admin = User::factory()->create(['role' => 'admin']);
        $client = User::factory()->create(['role' => 'client']);

        $design = UmkmDesign::withoutEvents(function () use ($umkm, $designer) {
            return UmkmDesign::create([
                'umkm_id' => $umkm->id,
                'designer_id' => $designer->id,
                'file_path' => 'designs/v1.png',
                'status' => 'revision_needed',
            ]);
        });

        $this->clearNotifikasi();

        $design->status = 'revised';
        $design->save();

        $notifs = Notifikasi::where('tipe', 'revised')->get();
        $this->assertCount(2, $notifs);
        $recipientIds = $notifs->pluck('user_id')->toArray();
        $this->assertContains($admin->id, $recipientIds);
        $this->assertContains($client->id, $recipientIds);
    }

    public function test_updated_sends_approved_notification_to_team_pasang(): void
    {
        User::where('role', 'team_pasang')->delete();

        [$umkm, $designer, $kota] = $this->setupUmkmAndDesigner();
        $teamPasang = User::factory()->create(['role' => 'team_pasang']);

        $design = UmkmDesign::withoutEvents(function () use ($umkm, $designer) {
            return UmkmDesign::create([
                'umkm_id' => $umkm->id,
                'designer_id' => $designer->id,
                'file_path' => 'designs/v1.png',
                'status' => 'pending',
            ]);
        });

        $this->clearNotifikasi();

        $design->status = 'approved';
        $design->save();

        $notifs = Notifikasi::where('tipe', 'siap_pasang')->get();
        $this->assertCount(1, $notifs);
        $this->assertEquals($teamPasang->id, $notifs->first()->user_id);
    }
}
