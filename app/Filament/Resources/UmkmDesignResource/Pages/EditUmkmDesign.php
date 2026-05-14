<?php

namespace App\Filament\Resources\UmkmDesignResource\Pages;

use App\Filament\Resources\UmkmDesignResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUmkmDesign extends EditRecord
{
    protected static string $resource = UmkmDesignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}



