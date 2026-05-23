<?php

namespace Tests\Unit\Observers;

use App\Models\Kota;
use App\Models\Notifikasi;
use App\Models\Umkm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UmkmObserverTest extends TestCase
{
    use RefreshDatabase;

    private function clearNotifikasi(): void
    {
        DB::table('notifikasi_user')->delete();
        Notifikasi::query()->delete();
    }

    private function createUmkmWithObserver(array $overrides = []): Umkm
    {
        $kota = Kota::create(['nama' => 'Kota Obs ' . uniqid()]);
        $pic = User::factory()->create(['role' => 'pic_lapangan', 'kota_id' => $kota->id]);

        return Umkm::create(array_merge([
            'nama_pemilik' => 'Owner',
            'nama_usaha' => 'Usaha Test',
            'alamat_usaha' => 'Jl. Test',
            'no_wa' => '081234',
            'kota_id' => $kota->id,
            'submitted_by' => $pic->id,
            'status' => 'pending',
        ], $overrides));
    }

    public function test_created_sends_notification_to_admin_and_client(): void
    {
        // Clear any pre-existing users with admin/client roles
        User::whereIn('role', ['admin', 'client'])->delete();

        $admin = User::factory()->create(['role' => 'admin']);
        $client = User::factory()->create(['role' => 'client']);
        User::factory()->create(['role' => 'design']); // should NOT receive

        $this->clearNotifikasi();

        $umkm = $this->createUmkmWithObserver();

        $notifs = Notifikasi::where('notifiable_type', Umkm::class)
            ->where('notifiable_id', $umkm->id)
            ->where('tipe', 'umkm_baru')
            ->get();

        $this->assertCount(2, $notifs);
        $recipientIds = $notifs->pluck('user_id')->toArray();
        $this->assertContains($admin->id, $recipientIds);
        $this->assertContains($client->id, $recipientIds);
    }

    public function test_updated_approved_sends_notification_to_designers_and_submitter(): void
    {
        User::whereIn('role', ['admin', 'client', 'design'])->delete();

        $designer = User::factory()->create(['role' => 'design']);

        $umkm = $this->createUmkmWithObserver();
        $submitterId = $umkm->submitted_by;

        $this->clearNotifikasi();

        $umkm->status = 'approved';
        $umkm->save();

        $notifs = Notifikasi::all();

        // 1 for designer + 1 for submitter
        $this->assertCount(2, $notifs);
        $recipientIds = $notifs->pluck('user_id')->toArray();
        $this->assertContains($designer->id, $recipientIds);
        $this->assertContains($submitterId, $recipientIds);
    }

    public function test_updated_rejected_sends_notification_to_submitter(): void
    {
        User::whereIn('role', ['admin', 'client'])->delete();

        $umkm = $this->createUmkmWithObserver();
        $submitterId = $umkm->submitted_by;

        $this->clearNotifikasi();

        $umkm->status = 'rejected';
        $umkm->alasan_reject = 'Tidak memenuhi syarat';
        $umkm->save();

        $notifs = Notifikasi::all();
        $this->assertCount(1, $notifs);
        $this->assertEquals($submitterId, $notifs->first()->user_id);
        $this->assertEquals('umkm_rejected', $notifs->first()->tipe);
    }
}
