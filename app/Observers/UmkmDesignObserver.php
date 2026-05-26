<?php

namespace App\Observers;

use App\Models\Umkm;
use App\Models\UmkmDesign;
use App\Services\NotifikasiService;

class UmkmDesignObserver
{
    public function created(UmkmDesign $design): void
    {
        if ($design->umkm) {
            $design->umkm->update(['status' => Umkm::STATUS_DESIGN_REVIEW]);
        }

        NotifikasiService::notifyNewDesign($design);
    }

    public function updating(UmkmDesign $design): void
    {
        // Reset ke pending jika file_path berubah DAN status TIDAK sedang di-set secara eksplisit
        if ($design->isDirty('file_path') && !$design->isDirty('status')) {
            $design->status = 'pending';
            $design->catatan_revisi = null;
        }
    }

    public function updated(UmkmDesign $design): void
    {
        if (!$design->wasChanged('status')) {
            return;
        }

        if ($design->status === 'revision_needed') {
            NotifikasiService::notifyDesignRevision($design);
        }

        if ($design->status === 'revised') {
            NotifikasiService::notifyDesignRevised($design);
        }

        if ($design->status === 'approved') {
            if ($design->umkm) {
                $design->umkm->update([
                    'status' => Umkm::STATUS_WAITING_INSTALLATION,
                    'design_final' => $design->file_path,
                    'design_gerobak_depan' => $design->gerobak_depan,
                    'design_gerobak_kiri' => $design->gerobak_kiri,
                    'design_gerobak_kanan' => $design->gerobak_kanan,
                ]);
            }
            NotifikasiService::notifyDesignApproved($design);
            NotifikasiService::notifyTeamPasangDesignApproved($design);
        }
    }
}
