<?php

namespace App\Filament\Resources\UmkmResource\Pages;

use App\Filament\Resources\UmkmResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateUmkm extends CreateRecord
{
    protected static string $resource = UmkmResource::class;

    public static function canCreateAnother(): bool
    {
        return false;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // total_area_branding belum dihitung saat ini (dihitung di model saving event)
        // Hitung manual dari raw dimension fields
        $m2 = fn(?float $w, ?float $h): float => ($w && $h) ? round(($w * $h) / 10000, 2) : 0;

        $total = $m2($data['depan_atas_w'] ?? null, $data['depan_atas_h'] ?? null)
               + $m2($data['depan_tengah_w'] ?? null, $data['depan_tengah_h'] ?? null)
               + $m2($data['depan_bawah_w'] ?? null, $data['depan_bawah_h'] ?? null)
               + $m2($data['kanan_atas_w'] ?? null, $data['kanan_atas_h'] ?? null)
               + $m2($data['kanan_tengah_w'] ?? null, $data['kanan_tengah_h'] ?? null)
               + $m2($data['kanan_bawah_w'] ?? null, $data['kanan_bawah_h'] ?? null)
               + $m2($data['kiri_atas_w'] ?? null, $data['kiri_atas_h'] ?? null)
               + $m2($data['kiri_tengah_w'] ?? null, $data['kiri_tengah_h'] ?? null)
               + $m2($data['kiri_bawah_w'] ?? null, $data['kiri_bawah_h'] ?? null);

        if ($total < 1.5) {
            Notification::make()
                ->title('Tidak Dapat Diajukan ❌')
                ->body("Total area branding {$total} M² kurang dari minimum 1.5 M². Mohon periksa kembali ukuran panel.")
                ->danger()
                ->persistent()
                ->send();

            $this->halt();
        }

        return $data;
    }
}
