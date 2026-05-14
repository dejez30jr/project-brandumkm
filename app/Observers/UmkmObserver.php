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
        // Langsung gunakan $umkm, tidak perlu ambil dari relasi
        if ($umkm->isDirty('status') && $umkm->status === 'approved') {
            NotifikasiService::notifyUmkmApproved($umkm);
        }
    }
}