<?php

namespace App\Observers;

use App\Jobs\OptimizeUmkmPhoto;
use App\Models\Umkm;
use App\Services\NotifikasiService;

class UmkmObserver
{
    private const PHOTO_FIELDS = [
        'foto_depan', 'foto_kanan', 'foto_kiri',
        'foto_plang_alfamart', 'foto_tampak_jauh',
    ];

    public function created(Umkm $umkm): void
    {
        NotifikasiService::notifyNewUmkm($umkm);
        $this->dispatchPhotoOptimization($umkm, self::PHOTO_FIELDS);
    }

    public function updated(Umkm $umkm): void
    {
        if ($umkm->wasChanged('status')) {
            if ($umkm->status === 'approved') {
                // PRD: kirim notifikasi dulu saat status approved, lalu ubah ke menunggu_didesain
                NotifikasiService::notifyUmkmApproved($umkm);
                $umkm->updateQuietly(['status' => Umkm::STATUS_MENUNGGU_DIDESAIN]);
            } elseif ($umkm->status === 'rejected') {
                NotifikasiService::notifyUmkmRejected($umkm);
            }
        }

        $changedPhotos = array_filter(self::PHOTO_FIELDS, fn ($f) => $umkm->wasChanged($f));
        $this->dispatchPhotoOptimization($umkm, $changedPhotos);
    }

    private function dispatchPhotoOptimization(Umkm $umkm, array $fields): void
    {
        foreach ($fields as $field) {
            if (!empty($umkm->{$field})) {
                OptimizeUmkmPhoto::dispatch($umkm->{$field});
            }
        }
    }
}
