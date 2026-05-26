<?php

namespace Tests\Unit\Models;

use App\Models\Kota;
use App\Models\Umkm;
use App\Models\UmkmDesign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UmkmTest extends TestCase
{
    use RefreshDatabase;

    private function makeUmkm(array $overrides = []): Umkm
    {
        $kota = Kota::create(['nama' => 'Kota ' . uniqid()]);
        $user = User::factory()->create(['role' => 'pic_lapangan', 'kota_id' => $kota->id]);
        // Use create WITH events so saving callback runs (calculates M2)
        $this->actingAs($user);
        return Umkm::create(array_merge([
            'nama_pemilik' => 'Owner', 'nama_usaha' => 'Usaha',
            'alamat_usaha' => 'Jl. Test', 'no_wa' => '08' . rand(100000000, 999999999),
            'kota_id' => $kota->id, 'status' => 'pending',
        ], $overrides));
    }

    // PRD §4: Status constants sesuai workflow
    public function test_all_prd_status_constants_defined(): void
    {
        $this->assertEquals('pending', Umkm::STATUS_PENDING);
        $this->assertEquals('approved', Umkm::STATUS_APPROVED);
        $this->assertEquals('rejected', Umkm::STATUS_REJECTED);
        $this->assertEquals('menunggu_didesain', Umkm::STATUS_MENUNGGU_DIDESAIN);
        $this->assertEquals('design_review', Umkm::STATUS_DESIGN_REVIEW);
        $this->assertEquals('design_approved', Umkm::STATUS_DESIGN_APPROVED);
        $this->assertEquals('waiting_installation', Umkm::STATUS_WAITING_INSTALLATION);
        $this->assertEquals('revision_needed', Umkm::STATUS_REVISION_NEEDED);
        $this->assertEquals('branded', Umkm::STATUS_BRANDED);
        $this->assertEquals('terbranding_final', Umkm::STATUS_TERBRANDING_FINAL);
    }

    // PRD §3.1: Validasi 1.5m² — memenuhi_kriteria true jika >= 1.5
    public function test_memenuhi_kriteria_true_when_area_gte_1_5(): void
    {
        $umkm = $this->makeUmkm(['depan_atas_w' => 100, 'depan_atas_h' => 150]); // 1.5 m²
        $this->assertTrue((bool) $umkm->memenuhi_kriteria);
        $this->assertEquals('1.50', $umkm->total_area_branding);
    }

    // PRD §3.1: Validasi 1.5m² — memenuhi_kriteria false jika < 1.5
    public function test_memenuhi_kriteria_false_when_area_lt_1_5(): void
    {
        $umkm = $this->makeUmkm(['depan_atas_w' => 100, 'depan_atas_h' => 100]); // 1.0 m²
        $this->assertFalse((bool) $umkm->memenuhi_kriteria);
    }

    public function test_m2_calculation_correct(): void
    {
        $umkm = $this->makeUmkm(['depan_atas_w' => 163, 'depan_atas_h' => 20]); // 163*20/10000 = 0.326
        $this->assertEquals('0.33', $umkm->depan_panel_atas_m2);
    }

    public function test_m2_zero_when_null_dimension(): void
    {
        $umkm = $this->makeUmkm(['depan_atas_w' => null, 'depan_atas_h' => 100]);
        $this->assertEquals('0.00', $umkm->depan_panel_atas_m2);
    }

    public function test_total_area_sums_all_panels(): void
    {
        $umkm = $this->makeUmkm([
            'depan_atas_w' => 100, 'depan_atas_h' => 100,   // 1.0
            'kanan_atas_w' => 100, 'kanan_atas_h' => 100,   // 1.0
            'kiri_atas_w'  => 100, 'kiri_atas_h'  => 100,   // 1.0
        ]);
        $this->assertEquals('3.00', $umkm->total_area_branding);
    }

    // PRD §3.1: submitted_by otomatis dari auth
    public function test_submitted_by_set_from_auth(): void
    {
        $kota = Kota::create(['nama' => 'Kota Auth']);
        $user = User::factory()->create(['role' => 'pic_lapangan', 'kota_id' => $kota->id]);
        $this->actingAs($user);

        $umkm = Umkm::create([
            'nama_pemilik' => 'Owner', 'nama_usaha' => 'Usaha',
            'alamat_usaha' => 'Jl. Test', 'no_wa' => '081111',
            'kota_id' => $kota->id, 'status' => 'pending',
        ]);

        $this->assertEquals($user->id, $umkm->submitted_by);
    }

    public function test_kota_relation(): void
    {
        $umkm = $this->makeUmkm();
        $this->assertInstanceOf(Kota::class, $umkm->kota);
    }

    public function test_submitted_by_relation(): void
    {
        $umkm = $this->makeUmkm();
        $this->assertInstanceOf(User::class, $umkm->submittedBy);
    }

    public function test_designs_has_many(): void
    {
        $umkm = $this->makeUmkm();
        $designer = User::factory()->create(['role' => 'design']);

        UmkmDesign::withoutEvents(fn () => UmkmDesign::create([
            'umkm_id' => $umkm->id, 'designer_id' => $designer->id,
            'file_path' => 'design.png', 'status' => 'pending',
        ]));

        $this->assertCount(1, $umkm->designs);
    }

    public function test_umkm_design_returns_latest(): void
    {
        $umkm = $this->makeUmkm();
        $designer = User::factory()->create(['role' => 'design']);

        UmkmDesign::withoutEvents(function () use ($umkm, $designer) {
            UmkmDesign::create(['umkm_id' => $umkm->id, 'designer_id' => $designer->id, 'file_path' => 'v1.png', 'status' => 'pending', 'versi' => 1]);
            UmkmDesign::create(['umkm_id' => $umkm->id, 'designer_id' => $designer->id, 'file_path' => 'v2.png', 'status' => 'pending', 'versi' => 2]);
        });

        $this->assertEquals('v2.png', $umkm->umkmDesign->file_path);
    }
}
