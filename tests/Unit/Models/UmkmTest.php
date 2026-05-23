<?php

namespace Tests\Unit\Models;

use App\Models\AfterBranding;
use App\Models\Kota;
use App\Models\Umkm;
use App\Models\UmkmDesign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UmkmTest extends TestCase
{
    use RefreshDatabase;

    private function createUmkm(array $overrides = []): Umkm
    {
        $kota = Kota::create(['nama' => 'Kota Test ' . uniqid()]);
        $user = User::factory()->create(['role' => 'pic_lapangan', 'kota_id' => $kota->id]);

        // Use create WITH events so saving callback runs (calculates M2)
        // but we accept that observer notifications will fire
        return Umkm::create(array_merge([
            'nama_pemilik' => 'Test Owner',
            'nama_usaha' => 'Test Usaha',
            'alamat_usaha' => 'Jl. Test',
            'no_wa' => '08123456789',
            'kota_id' => $kota->id,
            'submitted_by' => $user->id,
            'status' => 'pending',
        ], $overrides));
    }

    public function test_status_constants_defined(): void
    {
        $this->assertEquals('pending', Umkm::STATUS_PENDING);
        $this->assertEquals('approved', Umkm::STATUS_APPROVED);
        $this->assertEquals('rejected', Umkm::STATUS_REJECTED);
        $this->assertEquals('designing', Umkm::STATUS_DESIGNING);
        $this->assertEquals('design_review', Umkm::STATUS_DESIGN_REVIEW);
        $this->assertEquals('design_approved', Umkm::STATUS_DESIGN_APPROVED);
        $this->assertEquals('revision_needed', Umkm::STATUS_REVISION_NEEDED);
        $this->assertEquals('branded', Umkm::STATUS_BRANDED);
        $this->assertCount(8, Umkm::STATUSES);
    }

    public function test_calculate_m2_computes_correctly(): void
    {
        $umkm = $this->createUmkm([
            'depan_atas_w' => 100,
            'depan_atas_h' => 150,
        ]);

        // 100 * 150 / 10000 = 1.5
        $this->assertEquals('1.50', $umkm->depan_panel_atas_m2);
    }

    public function test_calculate_m2_returns_zero_when_null(): void
    {
        $umkm = $this->createUmkm([
            'depan_atas_w' => null,
            'depan_atas_h' => 150,
        ]);

        $this->assertEquals('0.00', $umkm->depan_panel_atas_m2);
    }

    public function test_saving_calculates_all_panels(): void
    {
        $umkm = $this->createUmkm([
            'depan_atas_w' => 100, 'depan_atas_h' => 100,       // 1.0
            'depan_tengah_w' => 50, 'depan_tengah_h' => 50,     // 0.25
            'depan_bawah_w' => 50, 'depan_bawah_h' => 50,       // 0.25
            'kanan_atas_w' => 100, 'kanan_atas_h' => 100,       // 1.0
            'kanan_tengah_w' => null, 'kanan_tengah_h' => null,  // 0
            'kanan_bawah_w' => null, 'kanan_bawah_h' => null,    // 0
            'kiri_atas_w' => null, 'kiri_atas_h' => null,        // 0
            'kiri_tengah_w' => null, 'kiri_tengah_h' => null,    // 0
            'kiri_bawah_w' => null, 'kiri_bawah_h' => null,      // 0
        ]);

        $this->assertEquals('1.00', $umkm->depan_panel_atas_m2);
        $this->assertEquals('0.25', $umkm->depan_panel_tengah_m2);
        $this->assertEquals('2.50', $umkm->total_area_branding);
        $this->assertTrue((bool) $umkm->memenuhi_kriteria);
    }

    public function test_saving_sets_memenuhi_kriteria_false_when_below_threshold(): void
    {
        $umkm = $this->createUmkm([
            'depan_atas_w' => 100, 'depan_atas_h' => 100, // 1.0 m2 < 1.5
        ]);

        $this->assertEquals('1.00', $umkm->total_area_branding);
        $this->assertFalse((bool) $umkm->memenuhi_kriteria);
    }

    public function test_creating_sets_submitted_by_from_auth(): void
    {
        $kota = Kota::create(['nama' => 'Kota Auth']);
        $user = User::factory()->create(['role' => 'pic_lapangan', 'kota_id' => $kota->id]);
        $this->actingAs($user);

        $umkm = Umkm::create([
            'nama_pemilik' => 'Owner',
            'nama_usaha' => 'Usaha',
            'alamat_usaha' => 'Jl. Test',
            'no_wa' => '081234',
            'kota_id' => $kota->id,
            'status' => 'pending',
        ]);

        $this->assertEquals($user->id, $umkm->submitted_by);
    }

    public function test_kota_relation(): void
    {
        $umkm = $this->createUmkm();
        $this->assertInstanceOf(Kota::class, $umkm->kota);
    }

    public function test_submitted_by_relation(): void
    {
        $umkm = $this->createUmkm();
        $this->assertInstanceOf(User::class, $umkm->submittedBy);
    }

    public function test_approved_by_relation(): void
    {
        $kota = Kota::create(['nama' => 'Kota Approve']);
        $approver = User::factory()->create(['role' => 'admin', 'kota_id' => $kota->id]);
        $umkm = $this->createUmkm(['approved_by' => $approver->id]);

        $this->assertInstanceOf(User::class, $umkm->approvedBy);
        $this->assertEquals($approver->id, $umkm->approvedBy->id);
    }

    public function test_designs_relation(): void
    {
        $umkm = $this->createUmkm();
        $designer = User::factory()->create(['role' => 'design']);

        UmkmDesign::withoutEvents(function () use ($umkm, $designer) {
            UmkmDesign::create([
                'umkm_id' => $umkm->id,
                'designer_id' => $designer->id,
                'file_path' => 'designs/test.png',
                'status' => 'pending',
            ]);
        });

        $this->assertCount(1, $umkm->designs);
    }

    public function test_umkm_design_has_one_latest(): void
    {
        $umkm = $this->createUmkm();
        $designer = User::factory()->create(['role' => 'design']);

        UmkmDesign::withoutEvents(function () use ($umkm, $designer) {
            UmkmDesign::create([
                'umkm_id' => $umkm->id,
                'designer_id' => $designer->id,
                'file_path' => 'designs/v1.png',
                'status' => 'pending',
                'versi' => 1,
            ]);
            UmkmDesign::create([
                'umkm_id' => $umkm->id,
                'designer_id' => $designer->id,
                'file_path' => 'designs/v2.png',
                'status' => 'pending',
                'versi' => 2,
            ]);
        });

        $this->assertEquals('designs/v2.png', $umkm->umkmDesign->file_path);
    }

    public function test_after_brandings_relation(): void
    {
        $umkm = $this->createUmkm();
        $user = User::factory()->create(['role' => 'team_pasang']);

        AfterBranding::create([
            'umkm_id' => $umkm->id,
            'file_path' => 'after/test.jpg',
            'uploaded_by' => $user->id,
        ]);

        $this->assertCount(1, $umkm->afterBrandings);
    }

    public function test_latest_approved_design(): void
    {
        $umkm = $this->createUmkm();
        $designer = User::factory()->create(['role' => 'design']);

        UmkmDesign::withoutEvents(function () use ($umkm, $designer) {
            UmkmDesign::create([
                'umkm_id' => $umkm->id,
                'designer_id' => $designer->id,
                'file_path' => 'designs/v1.png',
                'status' => 'approved',
                'versi' => 1,
            ]);
            UmkmDesign::create([
                'umkm_id' => $umkm->id,
                'designer_id' => $designer->id,
                'file_path' => 'designs/v2.png',
                'status' => 'pending',
                'versi' => 2,
            ]);
        });

        $result = $umkm->latestApprovedDesign();
        $this->assertEquals('designs/v1.png', $result->file_path);
    }
}
