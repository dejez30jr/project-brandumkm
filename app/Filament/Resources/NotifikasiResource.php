<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotifikasiResource\Pages;
use App\Filament\Resources\UmkmDesignResource; 
use App\Filament\Resources\UmkmResource;
use App\Models\Notifikasi;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Session;

class NotifikasiResource extends Resource
{
    protected static ?string $model = Notifikasi::class;

    protected static ?string $navigationLabel = 'Notifikasi';
    protected static ?string $label = 'Notifikasi';
    protected static ?string $pluralLabel = 'Notifikasi';

    protected static ?string $navigationGroup = 'System';
    protected static ?string $navigationIcon = null; 

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
        
    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        
        $unreadCount = Notifikasi::whereDoesntHave('users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->count();

        return $unreadCount > 0 ? '●' : null;
    }

    protected static ?string $navigationBadgeColor = 'danger';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        // 1. Ambil ID terakhir yang sudah dilihat user dari session (Disinkronkan pakai huruf i)
        $lastSeenId = Session::get('notifikasi_last_seen_id', 0);

        // 2. Ambil ID terbaru yang ada di database saat ini
        $latestRecord = $table->getQuery()->max('id'); 

        // 3. Simpan ID terbaru ke session untuk request/refresh berikutnya
        if ($latestRecord) {
            Session::put('notifikasi_last_seen_id', $latestRecord);
        }

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('judul')
                    ->searchable()
                    ->icon(function ($record) use ($lastSeenId) {
                        $isToday = Carbon::parse($record->created_at)->isToday();
                        $isNewData = $record->id > $lastSeenId;

                        return ($isToday && $isNewData) ? 'heroicon-m-sparkles' : null;
                    })
                    ->iconColor('success'),

                Tables\Columns\TextColumn::make('pesan'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordUrl(function (Notifikasi $record) {
                // Logika redirect berdasarkan judul
                return match ($record->judul) {
                    'UMKM Baru Masuk' => UmkmResource::getUrl('index'),
                    'Design Baru Upload' => UmkmDesignResource::getUrl('index'),
                    
                    // ========== SEKARANG INI BISA DIKLIK ==========
                    // Pastikan teks & emoji di bawah ini SAMA PERSIS dengan yang kita buat di model UmkmDesign.php
                    'Desain Telah Direvisi 🎨' => UmkmDesignResource::getUrl('index'),
                    
                    default => null,
                };
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotifikasis::route('/'),
        ];
    }
}