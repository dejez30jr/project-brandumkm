<?php

namespace App\Observers;

use App\Models\Umkm;
use App\Services\NotifikasiService;

class UmkmObserver
{
    public function created(Umkm $umkm): void
    {
        NotifikasiService::notifyNewUmkm($umkm);
    }

    public function updated(Umkm $umkm): void
    {
        if ($umkm->wasChanged('status')) {
            if ($umkm->status === 'approved') {
                // PRD: approved → otomatis berubah ke menunggu_didesain
                $umkm->updateQuietly(['status' => Umkm::STATUS_MENUNGGU_DIDESAIN]);
                NotifikasiService::notifyUmkmApproved($umkm);
            } elseif ($umkm->status === 'rejected') {
                NotifikasiService::notifyUmkmRejected($umkm);
            }
        }
    }
}
