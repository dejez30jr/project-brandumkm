<?php

namespace App\Filament\Resources\UmkmStikerResource\Pages;

use App\Filament\Resources\UmkmStikerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUmkmStiker extends CreateRecord
{
    protected static string $resource = UmkmStikerResource::class;

    public static function canCreateAnother(): bool
    {
        return false;
    }

    protected function getCreateFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateFormAction()
            ->label('Submit');
    }

    protected function getCancelFormAction(): \Filament\Actions\Action
    {
        return parent::getCancelFormAction()
            ->label('Cancel');
    }
}
