<?php

namespace App\Services;

use App\Models\Notifikasi;
use App\Models\User;
use App\Models\Umkm;
use App\Models\UmkmDesign;

class NotifikasiService
{
    private static function notifyAdmin(string $judul, string $pesan, string $tipe, string $notifiableType, int $notifiableId): void
    {
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            Notifikasi::create([
                'user_id' => $admin->id,
                'judul' => '[LOG] ' . $judul,
                'pesan' => $pesan,
                'tipe' => $tipe,
                'notifiable_type' => $notifiableType,
                'notifiable_id' => $notifiableId,
            ]);
        }
    }

    public static function notifyNewUmkm(Umkm $umkm): void
    {
        $pesan = "UMKM baru: {$umkm->nama_usaha} dari {$umkm->kota->nama} perlu direview.";

        $clients = User::where('role', 'client')->get();
        foreach ($clients as $client) {
            Notifikasi::create([
                'user_id' => $client->id,
                'judul' => 'UMKM Baru Masuk',
                'pesan' => $pesan,
                'tipe' => 'umkm_baru',
                'notifiable_type' => Umkm::class,
                'notifiable_id' => $umkm->id,
            ]);
        }

        self::notifyAdmin('UMKM Baru Masuk', $pesan, 'umkm_baru', Umkm::class, $umkm->id);
    }

    public static function notifyUmkmApproved(Umkm $umkm): void
    {
        $designers = User::where('role', 'design')->get();
        foreach ($designers as $designer) {
            Notifikasi::create([
                'user_id' => $designer->id,
                'judul' => 'UMKM Perlu Design',
                'pesan' => "UMKM: {$umkm->nama_usaha} sudah diapprove dan perlu dibuatkan design.",
                'tipe' => 'perlu_design',
                'notifiable_type' => Umkm::class,
                'notifiable_id' => $umkm->id,
            ]);
        }

        Notifikasi::create([
            'user_id' => $umkm->submitted_by,
            'judul' => 'UMKM Anda Disetujui ✅',
            'pesan' => "UMKM {$umkm->nama_usaha} telah disetujui oleh client.",
            'tipe' => 'umkm_approved',
            'notifiable_type' => Umkm::class,
            'notifiable_id' => $umkm->id,
        ]);

        self::notifyAdmin('UMKM Disetujui', "UMKM {$umkm->nama_usaha} disetujui client.", 'umkm_approved', Umkm::class, $umkm->id);
    }

    public static function notifyUmkmRejected(Umkm $umkm): void
    {
        Notifikasi::create([
            'user_id' => $umkm->submitted_by,
            'judul' => 'UMKM Anda Ditolak ❌',
            'pesan' => "UMKM {$umkm->nama_usaha} ditolak. Alasan: {$umkm->alasan_reject}",
            'tipe' => 'umkm_rejected',
            'notifiable_type' => Umkm::class,
            'notifiable_id' => $umkm->id,
        ]);

        self::notifyAdmin('UMKM Ditolak', "UMKM {$umkm->nama_usaha} ditolak. Alasan: {$umkm->alasan_reject}", 'umkm_rejected', Umkm::class, $umkm->id);
    }

    public static function notifyNewDesign(UmkmDesign $design): void
    {
        $pesan = "Design baru untuk {$design->umkm->nama_usaha} perlu direview.";

        $clients = User::where('role', 'client')->get();
        foreach ($clients as $client) {
            Notifikasi::create([
                'user_id' => $client->id,
                'judul' => 'Design Baru Upload',
                'pesan' => $pesan,
                'tipe' => 'design_baru',
                'notifiable_type' => UmkmDesign::class,
                'notifiable_id' => $design->id,
            ]);
        }

        self::notifyAdmin('Design Baru Upload', $pesan, 'design_baru', UmkmDesign::class, $design->id);
    }

    public static function notifyDesignRevision(UmkmDesign $design): void
    {
        Notifikasi::create([
            'user_id' => $design->designer_id,
            'judul' => 'Design Perlu Revisi',
            'pesan' => "Design untuk {$design->umkm->nama_usaha} perlu direvisi. Catatan: {$design->catatan_revisi}",
            'tipe' => 'perlu_revisi',
            'notifiable_type' => UmkmDesign::class,
            'notifiable_id' => $design->id,
        ]);

        self::notifyAdmin('Design Perlu Revisi', "Design {$design->umkm->nama_usaha} diminta revisi.", 'perlu_revisi', UmkmDesign::class, $design->id);
    }

    public static function notifyDesignRevised(UmkmDesign $design): void
    {
        $pesan = "Tim Desain telah memperbaiki desain untuk {$design->umkm->nama_usaha}. Silakan cek kembali untuk di-review.";

        $clients = User::where('role', 'client')->get();
        foreach ($clients as $client) {
            Notifikasi::create([
                'user_id' => $client->id,
                'judul' => 'Desain Telah Direvisi 🎨',
                'pesan' => $pesan,
                'tipe' => 'revised',
                'notifiable_type' => UmkmDesign::class,
                'notifiable_id' => $design->id,
            ]);
        }

        self::notifyAdmin('Desain Direvisi', $pesan, 'revised', UmkmDesign::class, $design->id);
    }

    public static function notifyDesignApproved(UmkmDesign $design): void
    {
        // Notif ke designer aslinya
        Notifikasi::create([
            'user_id' => $design->designer_id,
            'judul' => 'Design Anda Disetujui ✅',
            'pesan' => "Design untuk {$design->umkm->nama_usaha} telah disetujui oleh client.",
            'tipe' => 'design_approved',
            'notifiable_type' => UmkmDesign::class,
            'notifiable_id' => $design->id,
        ]);

        self::notifyAdmin('Design Disetujui', "Design {$design->umkm->nama_usaha} disetujui client.", 'design_approved', UmkmDesign::class, $design->id);
    }

    public static function notifyTeamPasangDesignApproved(UmkmDesign $design): void
    {
        $pesan = "Design untuk {$design->umkm->nama_usaha} ({$design->umkm->kota->nama}) telah disetujui. Siap untuk pemasangan stiker.";

        $users = User::where('role', 'team_pasang')->get();
        foreach ($users as $user) {
            Notifikasi::create([
                'user_id' => $user->id,
                'judul' => 'UMKM Siap Pasang Stiker 🎯',
                'pesan' => $pesan,
                'tipe' => 'siap_pasang',
                'notifiable_type' => Umkm::class,
                'notifiable_id' => $design->umkm->id,
            ]);
        }

        self::notifyAdmin('UMKM Siap Pasang', $pesan, 'siap_pasang', Umkm::class, $design->umkm->id);
    }
}
