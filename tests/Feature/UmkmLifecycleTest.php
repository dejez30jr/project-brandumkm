<?php

namespace Tests\Feature;

use App\Models\Kota;
use App\Models\Notifikasi;
use App\Models\Umkm;
use App\Models\UmkmDesign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UmkmLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private function clearNotifikasi(): void
    {
        DB::table('notifikasi_user')->delete();
        Notifikasi::query()->delete();
    }

    public function test_full_lifecycle_pending_to_branded(): void
    {
        // Clear existing role-based users to avoid count issues
        User::whereIn('role', ['admin', 'client', 'design', 'team_pasang'])->delete();

        // Setup users
        $kota = Kota::create(['nama' => 'Kota Lifecycle']);
        $admin = User::factory()->create(['role' => 'admin']);
        $client = User::factory()->create(['role' => 'client']);
        $pic = User::factory()->create(['role' => 'pic_lapangan', 'kota_id' => $kota->id]);
        $designer = User::factory()->create(['role' => 'design']);
        $teamPasang = User::factory()->create(['role' => 'team_pasang']);

        $this->clearNotifikasi();

        // Step 1: Create UMKM (status = pending)
        $this->actingAs($pic);
        $umkm = Umkm::create([
            'nama_pemilik' => 'Budi',
            'nama_usaha' => 'Warung Budi',
            'alamat_usaha' => 'Jl. Merdeka 1',
            'no_wa' => '08123456789',
            'kota_id' => $kota->id,
            'status' => 'pending',
            'depan_atas_w' => 100,
            'depan_atas_h' => 200, // 2.0 m2
        ]);

        $this->assertEquals('pending', $umkm->status);
        $this->assertEquals($pic->id, $umkm->submitted_by);
        $this->assertEquals('2.00', $umkm->total_area_branding);
        $this->assertTrue((bool) $umkm->memenuhi_kriteria);

        // Verify notification sent to admin & client
        $createNotifs = Notifikasi::where('tipe', 'umkm_baru')->get();
        $this->assertCount(2, $createNotifs);

        // Step 2: Approve UMKM (status = approved)
        $this->clearNotifikasi();
        $umkm->status = 'approved';
        $umkm->approved_by = $admin->id;
        $umkm->approved_at = now();
        $umkm->save();

        $umkm->refresh();
        $this->assertEquals('approved', $umkm->status);

        // Verify notifications to designer + submitter
        $approveNotifs = Notifikasi::all();
        $this->assertCount(2, $approveNotifs);

        // Step 3: Designer submits design (status = design_review)
        $this->clearNotifikasi();
        $design = UmkmDesign::create([
            'umkm_id' => $umkm->id,
            'designer_id' => $designer->id,
            'file_path' => 'designs/warung-budi-v1.png',
            'status' => 'pending',
        ]);

        $umkm->refresh();
        $this->assertEquals('design_review', $umkm->status);

        // Verify notification to admin & client
        $designNotifs = Notifikasi::where('tipe', 'design_baru')->get();
        $this->assertCount(2, $designNotifs);

        // Step 4: Design approved
        $this->clearNotifikasi();
        $design->status = 'approved';
        $design->approved_by = $client->id;
        $design->approved_at = now();
        $design->save();

        // Verify notification to team_pasang
        $approvedDesignNotifs = Notifikasi::where('tipe', 'siap_pasang')->get();
        $this->assertCount(1, $approvedDesignNotifs);
        $this->assertEquals($teamPasang->id, $approvedDesignNotifs->first()->user_id);

        // Step 5: Team pasang uploads stiker photos → status = branded
        Umkm::withoutEvents(function () use ($umkm) {
            $umkm->update(['status' => 'design_approved']);
        });
        $umkm->refresh();
        $this->assertEquals('design_approved', $umkm->status);

        Umkm::withoutEvents(function () use ($umkm) {
            $umkm->update([
                'stiker_tampak_depan' => 'stiker/depan.jpg',
                'stiker_tampak_kanan' => 'stiker/kanan.jpg',
                'stiker_tampak_kiri' => 'stiker/kiri.jpg',
                'foto_wide' => 'stiker/wide.jpg',
                'status' => 'branded',
            ]);
        });

        $umkm->refresh();
        $this->assertEquals('branded', $umkm->status);
    }
}
