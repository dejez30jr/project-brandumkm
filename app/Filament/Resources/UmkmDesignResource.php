<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UmkmDesignResource\Pages;
use App\Filament\Resources\UmkmResource;
use App\Models\Kota;
use App\Models\Umkm;
use App\Models\UmkmDesign;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UmkmDesignResource extends Resource
{
    protected static ?string $model = UmkmDesign::class;
    protected static ?string $navigationIcon = 'heroicon-o-paint-brush';
    protected static ?string $navigationGroup = 'Data UMKM';
    protected static ?string $label = 'Design UMKM';
    protected static ?string $pluralLabel = 'Design UMKM';

    // Badge notifikasi
public static function getNavigationBadge(): ?string
{
    $user = auth()->user(); 

    if ($user?->role === 'design') {
        // Designer: yang perlu dia kerjakan = pending tidak relevan untuknya,
        // yang relevan adalah revision_needed (harus diperbaiki)
        $count = UmkmDesign::where('status', 'revision_needed')->count();
    } else {
        // Reviewer (client/pic_lapangan): yang harus di-review
        $count = UmkmDesign::whereIn('status', ['pending', 'revised'])->count();
    }

    return $count > 0 ? (string) $count : null;
}

public static function getNavigationBadgeColor(): ?string
{
    $user = auth()->user();

    if ($user?->role === 'design') {
        return UmkmDesign::where('status', 'revision_needed')->exists() ? 'danger' : null;
    }

    $count = UmkmDesign::whereIn('status', ['pending', 'revised'])->count();

    if ($count > 10) return 'danger';
    if ($count > 5)  return 'warning';
    return 'success';
}

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Design menunggu review';
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->role === 'design';
    }

    public static function canCreate(): bool
    {
        return auth()->user()->role === 'design';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Upload Design')
                    ->schema([

                        // ========== FILTER KOTA (helper, tidak disimpan ke DB) ==========
                        Forms\Components\Select::make('kota_id')
                            ->label('Filter Kota')
                            ->placeholder('Pilih kota terlebih dahulu')
                            ->options(Kota::orderBy('nama')->pluck('nama', 'id'))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->dehydrated(false) // TIDAK disimpan ke tabel umkm_designs
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('umkm_id', null))
                            ->afterStateHydrated(function (Forms\Set $set, ?UmkmDesign $record) {
                                // Auto-isi kota saat edit
                                if ($record && $record->umkm) {
                                    $set('kota_id', $record->umkm->kota_id);
                                }
                            }),

                        // ========== UMKM (dependent ke kota_id) ==========
                        Forms\Components\Select::make('umkm_id')
                            ->label('UMKM')
                            ->placeholder(fn (Forms\Get $get) =>
                                $get('kota_id') ? 'Pilih UMKM' : 'Pilih kota terlebih dahulu'
                            )
                            ->options(function (Forms\Get $get) {
                                $kotaId = $get('kota_id');

                                if (! $kotaId) {
                                    return [];
                                }

                                return Umkm::where('status', 'approved')
                                    ->where('kota_id', $kotaId)
                                    ->get()
                                    ->mapWithKeys(fn ($umkm) => [
                                        $umkm->id => $umkm->nama_usaha . ' - ' . $umkm->nama_pemilik,
                                    ]);
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn (Forms\Get $get) => ! $get('kota_id'))
                            ->live(),

                        Forms\Components\FileUpload::make('file_path')
                            ->label('File Design final')
                            ->directory('umkm-designs')
                            ->image()
                            ->required(),

                        Forms\Components\FileUpload::make('gerobak_depan')
                            ->label('File mockup Design Gerobak Depan')
                            ->directory('umkm-designs')
                            ->image()
                            ->required(),

                        Forms\Components\FileUpload::make('gerobak_kiri')
                            ->label('File mockup Design Gerobak Kiri')
                            ->directory('umkm-designs')
                            ->image()
                            ->required(),

                        Forms\Components\FileUpload::make('gerobak_kanan')
                            ->label('File mockup Design Gerobak Kanan')
                            ->directory('umkm-designs')
                            ->image()
                            ->required(),

                        Forms\Components\Hidden::make('designer_id')
                            ->default(auth()->id()),

                        Forms\Components\Hidden::make('versi')
                            ->default(function (Forms\Get $get) {
                                $umkmId = $get('umkm_id');
                                if (! $umkmId) return 1;

                                $lastVersion = UmkmDesign::where('umkm_id', $umkmId)->max('versi');
                                return ($lastVersion ?? 0) + 1;
                            }),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('umkm.nama_usaha')
                    ->label('UMKM')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('umkm.kota.nama')
                    ->label('Kota')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ImageColumn::make('file_path')
                    ->label('Preview'),
                Tables\Columns\TextColumn::make('versi')
                    ->label('Versi')
                    ->badge(),
                Tables\Columns\TextColumn::make('designer.name')
                    ->label('Designer'),
              Tables\Columns\BadgeColumn::make('status')
    ->colors([
        'warning' => 'pending',
        'success' => 'approved',
        'danger'  => 'revision_needed',
        'info'    => 'revised',        // ← TAMBAH (warna biru)
    ])
    ->formatStateUsing(fn (string $state): string => match ($state) {
        'pending'         => 'Pending',
        'approved'        => 'Approved',
        'revision_needed' => 'Perlu Revisi',
        'revised'         => 'Sudah Direvisi',
        default           => ucfirst($state),
    }),
                Tables\Columns\TextColumn::make('catatan_revisi')
                    ->label('Catatan')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->catatan_revisi),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Upload')
                    ->dateTime('d M Y'),
            ])
            ->filters([
                // ========== FILTER KOTA DI TABLE ==========
                Tables\Filters\SelectFilter::make('kota_id')
                    ->label('Kota')
                    ->options(Kota::orderBy('nama')->pluck('nama', 'id'))
                    ->query(function ($query, array $data) {
                        if (! empty($data['value'])) {
                            $query->whereHas('umkm', fn ($q) => $q->where('kota_id', $data['value']));
                        }
                    })
                    ->searchable()
                    ->preload(),

            Tables\Filters\SelectFilter::make('status')
    ->options([
        'pending'         => 'Pending',
        'approved'        => 'Approved',
        'revision_needed' => 'Perlu Revisi',
        'revised'         => 'Sudah Direvisi',   // ← TAMBAH
    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

               Tables\Actions\EditAction::make()
    ->visible(fn () => auth()->user()?->isDesign())
    ->mutateFormDataUsing(function (array $data): array {
        // Auto ubah status jadi "revised" saat designer save perubahan
        $data['status'] = 'revised';
        return $data;
    })
    ->successNotificationTitle('Design berhasil direvisi & dikirim ke client untuk review ulang'),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()?->isDesign()),

                // Tombol Download
                Tables\Actions\Action::make('download')
                    ->label('Download img')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->action(function (UmkmDesign $record) {
                        $filePath = storage_path('app/public/' . $record->file_path);

                        if (file_exists($filePath)) {
                            return response()->download($filePath);
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('File tidak ditemukan')
                            ->danger()
                            ->send();
                    }),

                // Approve Design
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                        ->visible(fn (UmkmDesign $record) =>
        in_array($record->status, ['pending', 'revised']) &&  // ← TAMBAH 'revised'
        (auth()->user()->isPicLapangan() || auth()->user()->isClient())
    )
                    ->action(function (UmkmDesign $record) {
                        $record->update([
                            'status'      => 'approved',
                            'approved_at' => now(),
                            'approved_by' => auth()->id(),
                        ]);

                        if ($record->umkm) {
                            $record->umkm->update([
                                'design_final'         => $record->file_path,
                                'design_gerobak_depan' => $record->gerobak_depan,
                                'design_gerobak_kiri'  => $record->gerobak_kiri,
                                'design_gerobak_kanan' => $record->gerobak_kanan,
                            ]);
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Design disetujui ✅')
                            ->body('4 file design telah otomatis tersalin ke data UMKM.')
                            ->success()
                            ->send();
                    }),

                // Request Revision
                Tables\Actions\Action::make('revisi')
                    ->label('Minta Revisi')
                    ->icon('heroicon-o-pencil')
                    ->color('warning')
                    ->form([
                        Forms\Components\Textarea::make('catatan_revisi')
                            ->label('Catatan Revisi')
                            ->required(),
                    ])
                    ->visible(fn (UmkmDesign $record) =>
        in_array($record->status, ['pending', 'revised']) &&  // ← TAMBAH 'revised'
        (auth()->user()->isPicLapangan() || auth()->user()->isClient())
    )
                    ->action(function (UmkmDesign $record, array $data) {
                        $record->update([
                            'status'         => 'revision_needed',
                            'catatan_revisi' => $data['catatan_revisi'],
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->isAdmin()),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUmkmDesigns::route('/'),
            'create' => Pages\CreateUmkmDesign::route('/create'),
            // 'edit' => Pages\EditUmkmDesign::route('/edit'),
        ];
    }
}