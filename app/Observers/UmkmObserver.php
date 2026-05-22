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
        if ($umkm->isDirty('status')) {
            if ($umkm->status === 'approved') {
                NotifikasiService::notifyUmkmApproved($umkm);
            } elseif ($umkm->status === 'rejected') {
                NotifikasiService::notifyUmkmRejected($umkm);
            }
        }
    }
    
}