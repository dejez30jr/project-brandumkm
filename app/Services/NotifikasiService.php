<?php

namespace App\Services;

use App\Models\Notifikasi;
use App\Models\User;
use App\Models\Umkm;
use App\Models\UmkmDesign;

class NotifikasiService
{
    public static function notifyNewUmkm(Umkm $umkm): void
    {
        // Notify Admin & Client
        $users = User::whereIn('role', ['admin', 'client'])->get();
        
        foreach ($users as $user) {
            Notifikasi::create([
                'user_id' => $user->id,
                'judul' => 'UMKM Baru Masuk',
                'pesan' => "UMKM baru: {$umkm->nama_usaha} dari {$umkm->kota->nama} perlu direview.",
                'tipe' => 'umkm_baru',
                'notifiable_type' => Umkm::class,
                'notifiable_id' => $umkm->id,
            ]);
        }
    }

    public static function notifyUmkmApproved(Umkm $umkm): void
    {
        // Notify Team Design
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

        // Notify PIC bahwa UMKM-nya di-acc
        Notifikasi::create([
            'user_id' => $umkm->submitted_by,
            'judul' => 'UMKM Anda Disetujui ✅',
            'pesan' => "UMKM {$umkm->nama_usaha} telah disetujui oleh client.",
            'tipe' => 'umkm_approved',
            'notifiable_type' => Umkm::class,
            'notifiable_id' => $umkm->id,
        ]);
    }

    public static function notifyUmkmRejected(Umkm $umkm): void
    {
        // Notify PIC bahwa UMKM-nya di-reject
        Notifikasi::create([
            'user_id' => $umkm->submitted_by,
            'judul' => 'UMKM Anda Ditolak ❌',
            'pesan' => "UMKM {$umkm->nama_usaha} ditolak. Alasan: {$umkm->alasan_reject}",
            'tipe' => 'umkm_rejected',
            'notifiable_type' => Umkm::class,
            'notifiable_id' => $umkm->id,
        ]);
    }

    public static function notifyNewDesign(UmkmDesign $design): void
    {
        // Notify Admin & Client
        $users = User::whereIn('role', ['admin', 'client'])->get();
        
        foreach ($users as $user) {
            Notifikasi::create([
                'user_id' => $user->id,
                'judul' => 'Design Baru Upload',
                'pesan' => "Design baru untuk {$design->umkm->nama_usaha} perlu direview.",
                'tipe' => 'design_baru',
                'notifiable_type' => UmkmDesign::class,
                'notifiable_id' => $design->id,
            ]);
        }
    }

    public static function notifyDesignRevision(UmkmDesign $design): void
    {
        // Notify Designer
        Notifikasi::create([
            'user_id' => $design->designer_id,
            'judul' => 'Design Perlu Revisi',
            'pesan' => "Design untuk {$design->umkm->nama_usaha} perlu direvisi. Catatan: {$design->catatan_revisi}",
            'tipe' => 'perlu_revisi',
            'notifiable_type' => UmkmDesign::class,
            'notifiable_id' => $design->id,
        ]);
    }

    public static function notifyDesignRevised(UmkmDesign $design): void
    {
        // Notify Admin & Client saat designer sudah selesai revisi
        $users = User::whereIn('role', ['admin', 'client'])->get();

        foreach ($users as $user) {
            Notifikasi::create([
                'user_id' => $user->id,
                'judul' => 'Desain Telah Direvisi 🎨',
                'pesan' => "Tim Desain telah memperbaiki desain untuk {$design->umkm->nama_usaha}. Silakan cek kembali untuk di-review.",
                'tipe' => 'revised',
                'notifiable_type' => UmkmDesign::class,
                'notifiable_id' => $design->id,
            ]);
        }
    }
}