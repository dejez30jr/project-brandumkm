<?php

namespace Tests\Unit\Observers;

use App\Models\Kota;
use App\Models\Notifikasi;
use App\Models\Umkm;
use App\Models\UmkmDesign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UmkmDesignObserverTest extends TestCase
{
    use RefreshDatabase;

    private function setupRoles(): array
    {
        User::query()->delete();
        $kota = Kota::create(['nama' => 'Kota ' . uniqid()]);
        $roles = [
            'kota'     => $kota,
            'admin'    => User::factory()->create(['role' => 'admin']),
            'client'   => User::factory()->create(['role' => 'client']),
            'pic'      => User::factory()->create(['role' => 'pic_lapangan', 'kota_id' => $kota->id]),
            'designer' => User::factory()->create(['role' => 'design']),
            'pasang'   => User::factory()->create(['role' => 'team_pasang']),
        ];

        $roles['umkm'] = Umkm::withoutEvents(fn () => Umkm::create([
            'nama_pemilik' => 'Owner', 'nama_usaha' => 'Usaha',
            'alamat_usaha' => 'Jl. Test', 'no_wa' => '08' . rand(100000000, 999999999),
            'kota_id' => $kota->id, 'submitted_by' => $roles['pic']->id, 'status' => 'approved',
        ]));

        return $roles;
    }

    private function makeDesign(array $r, array $overrides = []): UmkmDesign
    {
        return UmkmDesign::withoutEvents(fn () => UmkmDesign::create(array_merge([
            'umkm_id' => $r['umkm']->id, 'designer_id' => $r['designer']->id,
            'file_path' => 'design.png', 'status' => 'pending',
        ], $overrides)));
    }

    // PRD §4: design created → umkm status = design_review
    public function test_created_sets_umkm_to_design_review(): void
    {
        $r = $this->setupRoles();

        UmkmDesign::create([
            'umkm_id' => $r['umkm']->id, 'designer_id' => $r['designer']->id,
            'file_path' => 'design.png', 'status' => 'pending',
        ]);

        $r['umkm']->refresh();
        $this->assertEquals(Umkm::STATUS_DESIGN_REVIEW, $r['umkm']->status);
    }

    // PRD §5.4: design created → client + admin dapat notif design_baru
    public function test_created_notifies_client_and_admin(): void
    {
        $r = $this->setupRoles();
        Notifikasi::query()->delete();

        UmkmDesign::create([
            'umkm_id' => $r['umkm']->id, 'designer_id' => $r['designer']->id,
            'file_path' => 'design.png', 'status' => 'pending',
        ]);

        $this->assertDatabaseHas('notifikasis', ['user_id' => $r['client']->id, 'tipe' => 'design_baru']);
        $this->assertDatabaseHas('notifikasis', ['user_id' => $r['admin']->id, 'tipe' => 'design_baru']);
        $this->assertDatabaseMissing('notifikasis', ['user_id' => $r['designer']->id, 'tipe' => 'design_baru']);
    }

    // PRD §5.4: revision_needed → hanya designer aslinya yang dapat notif
    public function test_revision_needed_notifies_only_original_designer(): void
    {
        $r = $this->setupRoles();
        $otherDesigner = User::factory()->create(['role' => 'design']);
        $design = $this->makeDesign($r);
        Notifikasi::query()->delete();

        $design->update(['status' => 'revision_needed', 'catatan_revisi' => 'Warna salah']);

        $this->assertDatabaseHas('notifikasis', ['user_id' => $r['designer']->id, 'tipe' => 'perlu_revisi']);
        $this->assertDatabaseMissing('notifikasis', ['user_id' => $otherDesigner->id, 'tipe' => 'perlu_revisi']);
    }

    // PRD §5.4: revised → client + admin dapat notif
    public function test_revised_notifies_client_and_admin(): void
    {
        $r = $this->setupRoles();
        $design = $this->makeDesign($r, ['status' => 'revision_needed']);
        Notifikasi::query()->delete();

        $design->update(['status' => 'revised']);

        $this->assertDatabaseHas('notifikasis', ['user_id' => $r['client']->id, 'tipe' => 'revised']);
        $this->assertDatabaseHas('notifikasis', ['user_id' => $r['admin']->id, 'tipe' => 'revised']);
    }

    // PRD §5.4: design approved → designer aslinya + team_pasang + admin dapat notif
    public function test_approved_notifies_designer_team_pasang_and_admin(): void
    {
        $r = $this->setupRoles();
        $design = $this->makeDesign($r);
        Notifikasi::query()->delete();

        $design->update(['status' => 'approved', 'approved_at' => now(), 'approved_by' => $r['client']->id]);

        $this->assertDatabaseHas('notifikasis', ['user_id' => $r['designer']->id, 'tipe' => 'design_approved']);
        $this->assertDatabaseHas('notifikasis', ['user_id' => $r['pasang']->id, 'tipe' => 'siap_pasang']);
        $this->assertDatabaseHas('notifikasis', ['user_id' => $r['admin']->id, 'tipe' => 'design_approved']);
    }

    // PRD §4: design approved → umkm status = waiting_installation
    public function test_approved_sets_umkm_to_waiting_installation(): void
    {
        $r = $this->setupRoles();
        $design = $this->makeDesign($r);

        $design->update(['status' => 'approved', 'approved_at' => now(), 'approved_by' => $r['client']->id]);

        $r['umkm']->refresh();
        $this->assertEquals(Umkm::STATUS_WAITING_INSTALLATION, $r['umkm']->status);
    }

    // Observer: file_path berubah tanpa explicit status → reset ke pending
    public function test_file_change_without_status_resets_to_pending(): void
    {
        $r = $this->setupRoles();
        $design = $this->makeDesign($r, ['status' => 'revision_needed', 'catatan_revisi' => 'Perbaiki']);

        $design->file_path = 'design-v2.png';
        $design->save();

        $design->refresh();
        $this->assertEquals('pending', $design->status);
        $this->assertNull($design->catatan_revisi);
    }

    // Observer: file_path + status berubah → status tidak di-reset
    public function test_file_and_status_change_keeps_explicit_status(): void
    {
        $r = $this->setupRoles();
        $design = $this->makeDesign($r, ['status' => 'revision_needed']);

        $design->file_path = 'design-v2.png';
        $design->status = 'revised';
        $design->save();

        $design->refresh();
        $this->assertEquals('revised', $design->status);
    }
}
