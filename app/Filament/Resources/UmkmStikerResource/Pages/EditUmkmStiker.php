<?php

namespace App\Filament\Resources\UmkmStikerResource\Pages;

use App\Filament\Resources\UmkmStikerResource;
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
}
