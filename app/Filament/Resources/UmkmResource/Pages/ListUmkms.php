<?php

namespace App\Filament\Resources\UmkmResource\Pages;

use App\Filament\Resources\UmkmResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUmkms extends \Filament\Resources\Pages\ListRecords
{
    protected static string $resource = UmkmResource::class;

    public $data; // tambahkan kalau memang mau dipakai di Blade
}

