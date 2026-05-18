<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\UmkmDesignResource;
use App\Filament\Resources\UmkmResource;
use App\Models\Umkm;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Support\Htmlable;

class UmkmPerluDesignTableWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    protected function getTableHeading(): string|Htmlable
    {
        return new \Illuminate\Support\HtmlString('
            <div id="tabel-antrean-design" style="scroll-margin-top: 20px;">
                Daftar Antrean UMKM Perlu di-Design
            </div>
        ');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Umkm::query()
                    ->where('status', 'approved')
                    ->where(function ($query) {
                        $query->whereNull('design_final')
                              ->orWhereNull('design_gerobak_depan')
                              ->orWhereNull('design_gerobak_kiri')
                              ->orWhereNull('design_gerobak_kanan');
                    })
            )
            ->columns([
                Tables\Columns\TextColumn::make('nama_usaha')
                    ->label('Nama UMKM')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('nama_pemilik')
                    ->label('Pemilik')
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status UMKM')
                    ->badge()
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Masuk')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('proses_design')
                    ->label('Proses Design')
                    ->icon('heroicon-m-paint-brush')
                    ->color('warning')
                    ->url(fn (Umkm $record): string => UmkmDesignResource::getUrl('create', ['record' =>  $record->umkmDesign->id ?? null, 'umkm' => $record->id])),
            ]);
    }

    public static function canView(): bool
    {
        $userRole = auth()->user()?->role;
        return in_array($userRole, ['design', 'admin']);
    }
}