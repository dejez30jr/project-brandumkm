<?php

namespace App\Filament\Resources\UmkmStikerResource\Pages;

use App\Filament\Resources\UmkmStikerResource;
use Filament\Resources\Pages\EditRecord;

class EditUmkmStiker extends EditRecord
{
    protected static string $resource = UmkmStikerResource::class;

    public function getTitle(): string
    {
        return 'Upload Dokumentasi';
    }
}