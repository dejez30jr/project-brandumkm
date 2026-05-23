<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UmkmStikerResource\Pages;
use App\Models\Kota;
use App\Models\Umkm;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UmkmStikerResource extends Resource
{
    protected static ?string $model = Umkm::class;
    protected static ?string $navigationLabel = 'Pemasangan Stiker';
    protected static ?string $slug = 'pemasangan-stiker';
    protected static ?string $navigationIcon = 'heroicon-o-photo';

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role, ['team_pasang']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi UMKM')
                    ->schema([
                        Forms\Components\TextInput::make('nama_usaha')
                            ->disabled()
                            ->label('Nama UMKM'),
                        Forms\Components\TextInput::make('nama_pemilik')
                            ->disabled()
                            ->label('Pemilik'),
                    ])->columns(2),

                Forms\Components\Section::make('Upload Dokumentasi Pemasangan Stiker')
                    ->description('Silakan unggah foto bukti stiker yang telah terpasang di gerobak.')
                    ->schema([
                        Forms\Components\FileUpload::make('stiker_tampak_depan')
                            ->label('Stiker Tampak Depan')
                            ->image()
                            ->directory(fn ($record) => 'umkm/' . ($record?->kota_id ?: 'temp') . '/stiker')
                            ->required(),

                        Forms\Components\FileUpload::make('stiker_tampak_kanan')
                            ->label('Stiker Tampak Kanan')
                            ->image()
                            ->directory(fn ($record) => 'umkm/' . ($record?->kota_id ?: 'temp') . '/stiker')
                            ->required(),

                        Forms\Components\FileUpload::make('stiker_tampak_kiri')
                            ->label('Stiker Tampak Kiri')
                            ->image()
                            ->directory(fn ($record) => 'umkm/' . ($record?->kota_id ?: 'temp') . '/stiker')
                            ->required(),

                        Forms\Components\FileUpload::make('foto_wide')
                            ->label('Foto Wide (Keseluruhan)')
                            ->image()
                            ->directory(fn ($record) => 'umkm/' . ($record?->kota_id ?: 'temp') . '/stiker')
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = auth()->user();

        return $table
            ->modifyQueryUsing(function (Builder $query) use ($user) {
                $query->where('status', Umkm::STATUS_DESIGN_APPROVED)
                      ->where(function ($q) {
                          $q->whereNull('stiker_tampak_depan')
                            ->orWhereNull('stiker_tampak_kanan')
                            ->orWhereNull('stiker_tampak_kiri')
                            ->orWhereNull('foto_wide');
                      })
                      ->latest();
            })
            ->columns([
                Tables\Columns\TextColumn::make('nama_usaha')
                    ->label('Nama UMKM')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('kota.nama')
                    ->label('Kota')
                    ->searchable(),

                Tables\Columns\TextColumn::make('nama_pemilik')
                    ->label('Pemilik'),

                Tables\Columns\TextColumn::make('status_pasang')
                    ->label('Status Pasang')
                    ->badge()
                    ->getStateUsing(fn () => 'BELUM LENGKAP')
                    ->color('warning'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kota_id')
                    ->label('Kota')
                    ->options(Kota::pluck('nama', 'id')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Upload Dokumentasi')
                    ->icon('heroicon-m-arrow-up-tray')
                    ->color('success'),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUmkmStikers::route('/'),
            'edit' => Pages\EditUmkmStiker::route('/{record}/edit'),
        ];
    }
}
