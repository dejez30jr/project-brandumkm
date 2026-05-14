<?php

namespace App\Observers;

use App\Models\UmkmDesign;
use App\Services\NotifikasiService;

class UmkmDesignObserver
{
    public function created(UmkmDesign $design): void
    {
        NotifikasiService::notifyNewDesign($design);
    }

    public function updating(UmkmDesign $design): void
    {
        // Jika file_path berubah (designer upload gambar baru), reset status ke pending
        if ($design->isDirty('file_path')) {
            $design->status = 'pending';
            $design->catatan_revisi = null; // Clear catatan revisi lama
        }
    }

    public function updated(UmkmDesign $design): void
    {
        // Notifikasi jika status berubah ke revision_needed
        if ($design->isDirty('status') && $design->status === 'revision_needed') {
            NotifikasiService::notifyDesignRevision($design);
        }
    }
}