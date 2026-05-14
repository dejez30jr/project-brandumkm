<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UmkmDesignResource\Pages;
use App\Models\UmkmDesign;
use App\Models\Umkm;
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
        
        // Untuk designer: tampilkan yang perlu dikerjakan
        if ($user?->role === 'design') {
            $count = UmkmDesign::whereIn('status', ['pending', 'revision_needed'])->count();
        } 
        // Untuk admin/client: tampilkan yang pending review
        else {
            $count = UmkmDesign::where('status', 'pending')->count();
        }
        
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = UmkmDesign::whereIn('status', ['pending', 'revision_needed'])->count();
        
        if ($count > 10) return 'danger';    // Merah
        if ($count > 5) return 'warning';    // Kuning
        return 'success';                     // Hijau
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Design menunggu review';
    }

    public static function canEdit($record): bool
{
    $user = auth()->user();

    // hanya role 'design' yang boleh edit
    return $user->role === 'design';
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
                        Forms\Components\Select::make('umkm_id')
                            ->label('UMKM')
                            ->options(
    Umkm::where('status', 'approved')
        ->get()
        ->mapWithKeys(fn ($umkm) => [
            $umkm->id => $umkm->nama_usaha . ' - ' . $umkm->nama_pemilik
        ])
)
                            ->searchable()
                            ->required(),
                        Forms\Components\FileUpload::make('file_path')
                            ->label('File Design')
                            ->directory('umkm-designs')
                            ->image()
                            ->required(),
                        Forms\Components\Hidden::make('designer_id')
                            ->default(auth()->id()),
                        Forms\Components\Hidden::make('versi')
                            ->default(function (Forms\Get $get) {
                                $umkmId = $get('umkm_id');
                                if (!$umkmId) return 1;
                                
                                $lastVersion = UmkmDesign::where('umkm_id', $umkmId)
                                    ->max('versi');
                                
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
                    ->label('Kota'),
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
                        'danger' => 'revision_needed',
                    ]),
                Tables\Columns\TextColumn::make('catatan_revisi')
                    ->label('Catatan')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->catatan_revisi),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Upload')
                    ->dateTime('d M Y'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'revision_needed' => 'Perlu Revisi',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                      // Ubah EditAction visibility - hanya design
Tables\Actions\EditAction::make()
    ->visible(fn () => auth()->user()?->isDesign()),

// Ubah DeleteAction visibility - hanya design
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
                        $record->status === 'pending' && 
                        (auth()->user()->isPicLapangan() || auth()->user()->isClient())
                    )
                    ->action(function (UmkmDesign $record) {
                        $record->update([
                            'status' => 'approved',
                            'approved_at' => now(),
                            'approved_by' => auth()->id(),
                        ]);
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
                        $record->status === 'pending' && 
                        (auth()->user()->isPicLapangan() || auth()->user()->isClient())
                    )
                    ->action(function (UmkmDesign $record, array $data) {
                        $record->update([
                            'status' => 'revision_needed',
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
            'index' => Pages\ListUmkmDesigns::route('/'),
            'create' => Pages\CreateUmkmDesign::route('/create'),
            // 'edit' => Pages\EditUmkmDesign::route('/edit'),
        ];
    }
}