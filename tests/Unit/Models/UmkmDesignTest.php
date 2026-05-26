<?php

namespace Tests\Unit\Models;

use App\Models\Kota;
use App\Models\Umkm;
use App\Models\UmkmDesign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UmkmDesignTest extends TestCase
{
    use RefreshDatabase;

    private function createDesign(): UmkmDesign
    {
        $kota = Kota::create(['nama' => 'Kota Design ' . uniqid()]);
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

        return UmkmDesign::withoutEvents(function () use ($umkm, $designer) {
            return UmkmDesign::create([
                'umkm_id' => $umkm->id,
                'designer_id' => $designer->id,
                'file_path' => 'designs/test.png',
                'status' => 'pending',
            ]);
        });
    }

    public function test_umkm_relation(): void
    {
        $design = $this->createDesign();
        $this->assertInstanceOf(Umkm::class, $design->umkm);
    }

    public function test_designer_relation(): void
    {
        $design = $this->createDesign();
        $this->assertInstanceOf(User::class, $design->designer);
        $this->assertEquals('design', $design->designer->role);
    }

    public function test_approved_by_relation(): void
    {
        $approver = User::factory()->create(['role' => 'admin']);
        $design = $this->createDesign();
        $design->update(['approved_by' => $approver->id, 'status' => 'approved']);

        $design->refresh();
        $this->assertInstanceOf(User::class, $design->approvedBy);
        $this->assertEquals($approver->id, $design->approvedBy->id);
    }
}
