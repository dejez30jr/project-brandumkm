<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UmkmStikerResource\Pages;
use App\Models\Kota;
use App\Models\Umkm; // Kita gunakan model Umkm jika relasinya menyatu di tabel tersebut
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Tables\Filters\SelectFilter;

class UmkmStikerResource extends Resource
{
    // Sesuaikan dengan nama Model utama Anda, jika kolom tersebut ada di tabel umkms, gunakan Umkm::class
    protected static ?string $model = Umkm::class; 

    protected static ?string $navigationLabel = 'Pemasangan Stiker';
    
    protected static ?string $slug = 'pemasangan-stiker';
            protected static ?string $label = 'UMKM';
    protected static ?string $pluralLabel = 'Upload Dokumentasi Stiker UMKM';
    protected static ?string $navigationIcon = 'heroicon-o-photo';

    public static function canAccess(): bool 
    {
        // Memastikan hanya admin dan team_pasang yang bisa mengakses menu ini
        return in_array(auth()->user()?->role, ['team_pasang']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi UMKM')
                    ->schema([
                     Forms\Components\Section::make('Informasi UMKM')
    ->schema([

        // SELECT KOTA
        Forms\Components\Select::make('kota_id')
            ->label('Pilih Kota')
            ->options(Kota::pluck('nama', 'id'))
            ->searchable()
            ->preload()
            ->live()
            ->required(),

        // SELECT UMKM
      Forms\Components\Select::make('umkm_id')
    ->label('Pilih UMKM')
    ->options(function (callable $get) {

        $kotaId = $get('kota_id');

        if (!$kotaId) {
            return [];
        }

        return Umkm::where('status', 'approved')
            ->where('kota_id', $kotaId)
            ->get()
            ->mapWithKeys(function ($umkm) {

                return [
                    $umkm->id => $umkm->nama_pemilik . ' - ' . $umkm->nama_usaha
                ];
            });
    })
    ->searchable()
    ->preload()
    ->live()
    ->afterStateUpdated(function ($state, callable $set) {

        $umkm = Umkm::find($state);

        if ($umkm) {
            $set('nama_usaha', $umkm->nama_usaha);
            $set('nama_pemilik', $umkm->nama_pemilik);
        }
    })
    ->required(),
        // AUTO FILL
        Forms\Components\TextInput::make('nama_usaha')
            ->label('Nama UMKM')
            ->disabled(),

        Forms\Components\TextInput::make('nama_pemilik')
            ->label('Pemilik')
            ->disabled(),

    ])->columns(2),
                    ])->columns(2),

                Forms\Components\Section::make('Upload Dokumentasi Pemasangan Stiker')
                    ->description('Silakan unggah foto bukti stiker yang telah terpasang di gerobak.')
                    ->schema([
                        Forms\Components\FileUpload::make('stiker_tampak_depan')
                            ->label('Stiker Tampak Depan')
                            ->image()
                            ->directory('umkm-stikers')
                            ->required(),

                        Forms\Components\FileUpload::make('stiker_tampak_kanan')
                            ->label('Stiker Tampak Kanan')
                            ->image()
                            ->directory('umkm-stikers')
                            ->required(),

                        Forms\Components\FileUpload::make('stiker_tampak_kiri')
                            ->label('Stiker Tampak Kiri')
                            ->image()
                            ->directory('umkm-stikers')
                            ->required(),

                        Forms\Components\FileUpload::make('foto_wide')
                            ->label('Foto Wide (Keseluruhan)')
                            ->image()
                            ->directory('umkm-stikers')
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = auth()->user();

        return $table
            ->modifyQueryUsing(function (Builder $query) use ($user) {
                // ====================================================================
                // FILTER UTAMA: Hanya tarik data UMKM yang sudah di-APPROVE oleh Admin
                // ====================================================================
           $query->where('status', 'approved')

    // tampilkan hanya jika design ada
    ->where(function ($q) {

        $q->whereNotNull('design_final')
          ->orWhereNotNull('design_gerobak_depan')
          ->orWhereNotNull('design_gerobak_kiri')
          ->orWhereNotNull('design_gerobak_kanan');
    })

    // SEMBUNYIKAN jika dokumentasi stiker sudah lengkap
    ->where(function ($q) {

        $q->whereNull('stiker_tampak_depan')
          ->orWhereNull('stiker_tampak_kanan')
          ->orWhereNull('stiker_tampak_kiri')
          ->orWhereNull('foto_wide');
    })


    // Filter wilayah kota_id milik team_pasang
    // ->when($user?->kota_id, function ($q) use ($user) {
    //     $q->where('kota_id', $user->kota_id);
    // })

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
                    // pr action view popup untuk liat  detail file yang mau di print atau di download
                   Tables\Actions\ViewAction::make('view_design')
    ->label('View Design')
    ->icon('heroicon-o-eye')
    ->color('info')
    ->slideOver()
    ->modalHeading('Preview Design Gerobak')
    ->modalWidth('7xl')

    ->infolist([

        // LIGHTBOX MODAL
        \Filament\Infolists\Components\ViewEntry::make('image_lightbox')
            ->view('filament.infolists.components.image-lightbox')
            ->columnSpanFull(),

        // SECTION DESIGN
        \Filament\Infolists\Components\Section::make('Design Gerobak')
            ->description('Preview design gerobak UMKM.')
            ->icon('heroicon-o-paint-brush')
            ->schema([

                \Filament\Infolists\Components\ImageEntry::make('design_final')
                    ->label('Design Final')
                    ->height(250)
                      ->getStateUsing(fn ($record) => asset('storage/' . $record->design_final))
                    ->columnSpanFull()
                    ->extraAttributes(fn ($record) => [
                        'class' => 'cursor-pointer hover:scale-105 transition duration-300 rounded-lg overflow-hidden',
                        'x-on:click' => '$dispatch("open-preview-modal", { src: "' . asset('storage/' . $record->design_final) . '" })',
                    ])
                    ->visible(fn ($record) => !empty($record->design_final)),

                \Filament\Infolists\Components\ImageEntry::make('design_gerobak_depan')
                    ->label('Gerobak Tampak Depan')
                    ->height(200)
                      ->getStateUsing(fn ($record) => asset('storage/' . $record->design_gerobak_depan))
                    ->extraAttributes(fn ($record) => [
                        'class' => 'cursor-pointer hover:scale-105 transition duration-300 rounded-lg overflow-hidden',
                        'x-on:click' => '$dispatch("open-preview-modal", { src: "' . asset('storage/' . $record->design_gerobak_depan) . '" })',
                    ])
                    ->visible(fn ($record) => !empty($record->design_gerobak_depan)),

                \Filament\Infolists\Components\ImageEntry::make('design_gerobak_kiri')
                    ->label('Gerobak Tampak Kiri')
                    ->height(200)
                      ->getStateUsing(fn ($record) => asset('storage/' . $record->design_gerobak_depan))
                    ->extraAttributes(fn ($record) => [
                        'class' => 'cursor-pointer hover:scale-105 transition duration-300 rounded-lg overflow-hidden',
                        'x-on:click' => '$dispatch("open-preview-modal", { src: "' . asset('storage/' . $record->design_gerobak_kiri) . '" })',
                    ])
                    ->visible(fn ($record) => !empty($record->design_gerobak_kiri)),

                \Filament\Infolists\Components\ImageEntry::make('design_gerobak_kanan')
                    ->label('Gerobak Tampak Kanan')
                    ->height(200)
                      ->getStateUsing(fn ($record) => asset('storage/' . $record->design_gerobak_depan))
                    ->extraAttributes(fn ($record) => [
                        'class' => 'cursor-pointer hover:scale-105 transition duration-300 rounded-lg overflow-hidden',
                        'x-on:click' => '$dispatch("open-preview-modal", { src: "' . asset('storage/' . $record->design_gerobak_kanan) . '" })',
                    ])
                    ->visible(fn ($record) => !empty($record->design_gerobak_kanan)),

            ])
            ->columns(2)
            ->collapsible()
            ->visible(fn ($record) =>
                !empty($record->design_final) ||
                !empty($record->design_gerobak_depan) ||
                !empty($record->design_gerobak_kiri) ||
                !empty($record->design_gerobak_kanan)
            ),

        // ACTION DOWNLOAD
        \Filament\Infolists\Components\Actions::make([

            \Filament\Infolists\Components\Actions\Action::make('download_design_final')
                ->label('Download Design Final')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn ($record) => asset('storage/' . $record->design_final))
                ->openUrlInNewTab()
                ->visible(fn ($record) => !empty($record->design_final)),

        ])->fullWidth(),

    ]),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUmkmStikers::route('/'),
            'edit' => Pages\EditUmkmStiker::route('/{record}/edit'),
            'create' => Pages\CreateUmkmStiker::route('/create'),
        ];
    }
}