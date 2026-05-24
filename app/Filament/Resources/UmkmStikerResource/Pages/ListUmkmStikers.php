<?php

namespace App\Filament\Resources\UmkmStikerResource\Pages;

use App\Filament\Resources\UmkmStikerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUmkmStikers extends ListRecords
{
    protected static string $resource = UmkmStikerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Dokumentasi'),
        ];
    }
}