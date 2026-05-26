<?php

namespace App\Filament\Resources\UmkmStikerResource\Pages;

use App\Filament\Resources\UmkmStikerResource;
use App\Models\Umkm;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUmkmStiker extends EditRecord
{
    protected static string $resource = UmkmStikerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $record = $this->record;

        // Jika semua foto stiker sudah lengkap, update status ke branded
        if ($record->stiker_tampak_depan
            && $record->stiker_tampak_kanan
            && $record->stiker_tampak_kiri
            && $record->foto_wide
        ) {
            $record->update(['status' => Umkm::STATUS_BRANDED]);
        }
    }
}
