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

    // akses role design, client, admin
    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role, ['design', 'client', 'admin']);
    }

    // ====================================================================
    // DATA FILTER: Khusus Akun Design Hanya Bisa Melihat Data Sendiri
    // ====================================================================
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        // Jika user yang login memiliki role 'design', batasi datanya murni miliknya saja
        if ($user?->role === 'design') {
            $query->where('designer_id', $user->id);
        }

        // Jika role-nya Admin, PIC Lapangan, atau Client, mereka tetap bisa melihat seluruh data
        return $query;
    }

    // ====================================================================
    // Badge notifikasi
    // ====================================================================
public static function getNavigationBadge(): ?string
{
    $user = auth()->user(); 

    if ($user?->role === 'design') {
        // Designer: hanya hitung revision_needed miliknya sendiri
        $count = UmkmDesign::where('status', 'revision_needed')
            ->where('designer_id', $user->id)
            ->count();
    } else {
        // Reviewer (client/admin): yang harus di-review
        $count = UmkmDesign::whereIn('status', ['pending', 'revised'])->count();
    }

    return $count > 0 ? (string) $count : null;
}

// ====================================================================
// Tooltip untuk badge notifikasi
// ====================================================================
public static function getNavigationBadgeColor(): ?string
{
    $user = auth()->user(); 
    if (!$user) return null;

    if ($user->role === 'design') {
        $count = UmkmDesign::where('status', 'revision_needed')
            ->where('designer_id', $user->id)
            ->count();
        return $count > 0 ? 'danger' : null;
    } else {
        $hasDanger = UmkmDesign::where('status', 'revision_needed')->exists();
        if ($hasDanger) return 'danger';
        $hasWarning = UmkmDesign::where('status', 'pending')->exists();
        if ($hasWarning) return 'warning';
        return null;
    }
}

// ====================================================================
// Warna badge notifikasi
// ====================================================================
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
    ->options(\App\Models\Kota::orderBy('nama')->pluck('nama', 'id'))
    ->searchable()
    ->preload()
    ->live()
    ->dehydrated(false) // TIDAK disimpan ke tabel umkm_designs
    ->afterStateUpdated(fn (Forms\Set $set) => $set('umkm_id', null))
    ->afterStateHydrated(function (Forms\Set $set, ?\App\Models\UmkmDesign $record) {
        // 1. KONDISI EDIT: Auto-isi kota saat edit data yang sudah ada
        if ($record && $record->umkm) {
            $set('kota_id', $record->umkm->kota_id);
            return; // hentikan eksekusi jika sudah dalam mode edit
        }

        // 2. KONDISI CREATE (DARI NOTIFIKASI): Ambil parameter 'umkm' dari URL
        $umkmIdFromUrl = request()->query('umkm');
        if ($umkmIdFromUrl) {
            $umkm = \App\Models\Umkm::find($umkmIdFromUrl);
            if ($umkm && $umkm->kota_id) {
                // Set otomatis kota si UMKM tersebut ke dropdown filter kota ini
                $set('kota_id', $umkm->kota_id);
            }
        }
    }),

                        // ========== UMKM (dependent ke kota_id) ==========
                        Forms\Components\Select::make('umkm_id')
                            ->label('UMKM')
                            ->relationship('umkm', 'nama_usaha')
                            ->placeholder(fn (Forms\Get $get) =>
                                $get('kota_id') ? 'Pilih UMKM' : 'Pilih UMKM terlebih dahulu'
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
    // ====================================================================
    //  VALIDASI: Mencegah Penginputan Ganda untuk UMKM yang Sama
    // ====================================================================
    ->unique(
        table: 'umkm_designs',
        column: 'umkm_id',
        ignorable: fn ($record) => $record, // Mengabaikan id data ini sendiri sewaktu Edit Mode
        modifyRuleUsing: function (\Illuminate\Validation\Rules\Unique $rule, Forms\Get $get) {
            // Izinkan submit baru jika record yang ada statusnya 'revision_needed'
            return $rule->where(function ($query) {
                $query->where('status', '!=', 'revision_needed');
            });
        }
    )
    // Custom pesan error agar informatif bagi Team Design
    ->validationMessages([
        'unique' => 'UMKM ini sudah memiliki data desain. Mohon pilih UMKM lain atau edit data yang sudah ada.',
    ])
                            ->default(request()->query('umkm'))
                            ->disabled(fn (Forms\Get $get) => ! $get('kota_id'))
                            ->live(),

                        Forms\Components\FileUpload::make('file_path')
                            ->label('File Design final')
                            ->directory('umkm-designs')    ->required(),

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

    // ... Batas akhir method form() kamu ...

    public static function infolist(\Filament\Infolists\Infolist $infolist): \Filament\Infolists\Infolist
    {
        return $infolist
            ->schema([
                // PANGGIL MODAL POPUP DI SINI (Selalu stanby di halaman detail view)
                \Filament\Infolists\Components\ViewEntry::make('image_lightbox')
                    ->view('filament.infolists.components.image-lightbox')
                    ->columnSpanFull(),

                \Filament\Infolists\Components\Section::make('Informasi UMKM & Versi Design')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('umkm.nama_usaha')->label('Nama Usaha'),
                        \Filament\Infolists\Components\TextEntry::make('versi')->label('Versi Design')->badge(),
                        \Filament\Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'approved' => 'success',
                                'revision_needed' => 'danger',
                                'revised' => 'info',
                                default => 'gray',
                            }),
                        \Filament\Infolists\Components\TextEntry::make('catatan_revisi')
                            ->label('Catatan Revisi')
                            ->columnSpanFull()
                            ->visible(fn ($record) => !empty($record->catatan_revisi)),
                    ])->columns(3),

                \Filament\Infolists\Components\Section::make('Berkas Gambar Hasil Design')
                    ->schema([
                        \Filament\Infolists\Components\ImageEntry::make('file_path')
                            ->label('File Design Final')
                            ->height(200)
                            ->extraAttributes(fn ($record) => [
                                'class' => 'cursor-pointer hover:scale-105 transition duration-300 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700',
                                'x-on:click' => '$dispatch("open-preview-modal", { src: "' . asset('storage/' . $record->file_path) . '" })',
                            ]),

                        \Filament\Infolists\Components\ImageEntry::make('gerobak_depan')
                            ->label('Mockup Gerobak Depan')
                            ->height(200)
                            ->extraAttributes(fn ($record) => [
                                'class' => 'cursor-pointer hover:scale-105 transition duration-300 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700',
                                'x-on:click' => '$dispatch("open-preview-modal", { src: "' . asset('storage/' . $record->gerobak_depan) . '" })',
                            ]),

                        \Filament\Infolists\Components\ImageEntry::make('gerobak_kiri')
                            ->label('Mockup Gerobak Kiri')
                            ->height(200)
                            ->extraAttributes(fn ($record) => [
                                'class' => 'cursor-pointer hover:scale-105 transition duration-300 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700',
                                'x-on:click' => '$dispatch("open-preview-modal", { src: "' . asset('storage/' . $record->gerobak_kiri) . '" })',
                            ]),

                        \Filament\Infolists\Components\ImageEntry::make('gerobak_kanan')
                            ->label('Mockup Gerobak Kanan')
                            ->height(200)
                            ->extraAttributes(fn ($record) => [
                                'class' => 'cursor-pointer hover:scale-105 transition duration-300 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700',
                                'x-on:click' => '$dispatch("open-preview-modal", { src: "' . asset('storage/' . $record->gerobak_kanan) . '" })',
                            ]),
                    ])->columns(2),
            ]);
    }


    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUmkmDesigns::route('/'),
            'create' => Pages\CreateUmkmDesign::route('/create'),
              'edit'   => Pages\EditUmkmDesign::route('/{record}/edit'),
        ];
    }
}