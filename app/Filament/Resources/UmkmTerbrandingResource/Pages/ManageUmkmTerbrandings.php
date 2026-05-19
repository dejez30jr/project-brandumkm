<?php

namespace App\Filament\Resources\UmkmTerbrandingResource\Pages;

use App\Filament\Resources\UmkmTerbrandingResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageUmkmTerbrandings extends ManageRecords
{
    protected static string $resource = UmkmTerbrandingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
