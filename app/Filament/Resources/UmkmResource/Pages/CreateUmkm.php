<?php

namespace App\Filament\Resources\UmkmResource\Pages;

use App\Filament\Resources\UmkmResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUmkm extends CreateRecord {
    protected static string $resource = UmkmResource::class;

    public static function canCreateAnother(): bool {
        return false;
    }
}