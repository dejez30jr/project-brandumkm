<?php

namespace App\Filament\Resources\UmkmDesignResource\Pages;

use App\Filament\Resources\UmkmDesignResource;
use App\Models\UmkmDesign;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateUmkmDesign extends CreateRecord
{
    protected static string $resource = UmkmDesignResource::class;

    public static function canCreateAnother(): bool
    {
        return false;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Jika UMKM sudah punya design dengan status revision_needed, arahkan ke edit
        if (!empty($data['umkm_id'])) {
            $existing = UmkmDesign::where('umkm_id', $data['umkm_id'])
                ->where('status', 'revision_needed')
                ->latest()
                ->first();

            if ($existing) {
                Notification::make()
                    ->title('UMKM ini sudah punya design yang perlu direvisi')
                    ->body('Silakan edit design yang sudah ada.')
                    ->warning()
                    ->persistent()
                    ->send();

                $this->redirect(UmkmDesignResource::getUrl('edit', ['record' => $existing->id]));
                $this->halt();
            }
        }

        return $data;
    }
}
