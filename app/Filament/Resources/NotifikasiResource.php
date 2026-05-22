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
use Illuminate\Database\Eloquent\Builder; // JANGAN LUPA IMPORT INI

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
        if (!$user) return null;
        
        // ====================================================================
        // Filter badge sesuai role agar jumlah titik merah sinkron
        // ====================================================================
        $query = Notifikasi::whereDoesntHave('users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        });

        if ($user->role === 'design') {
            $query->whereIn('judul', ['UMKM Perlu Design', 'Design Perlu Revisi', 'Design Perlu Revisi ⚠️']);
        } elseif ($user->role === 'client') {
            $query->whereIn('judul', ['UMKM Baru Masuk','Design Baru Upload', 'Desain Telah Direvisi 🎨']);
        } elseif ($user->role === 'pic_lapangan') {
            $query->whereIn('judul', ['Design Perlu Revisi', 'Design Perlu Revisi ⚠️']);
        } elseif ($user->role === 'team_pasang') { // TAMBAHKAN INI
            $query->whereIn('judul', ['UMKM Perlu Branding']);
        }
        $unreadCount = $query->count();

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
        $user = auth()->user();
        $userRole = $user?->role;

        // Logika sync notifikasi yang belum dibaca (sudah benar, tetap dipertahankan)
        if ($user) {
            $unreadQuery = Notifikasi::whereDoesntHave('users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });

            // Filter query sesuai role agar sinkron
            if ($userRole === 'design') {
                $unreadQuery->whereIn('judul', ['UMKM Perlu Design', 'Design Perlu Revisi', 'Design Perlu Revisi ⚠️']);
            } elseif ($userRole === 'client') {
                $unreadQuery->whereIn('judul', ['UMKM Baru Masuk','Design Baru Upload', 'Desain Telah Direvisi 🎨']);
            } elseif ($userRole === 'pic_lapangan') {
                $unreadQuery->whereIn('judul', ['Design Perlu Revisi', 'Design Perlu Revisi ⚠️']);
            } elseif ($userRole === 'team_pasang') {
                $unreadQuery->whereIn('judul', ['UMKM Perlu Branding']);
            }

            $unreadNotifications = $unreadQuery->get();
            foreach ($unreadNotifications as $notif) {
                if (method_exists($notif, 'users')) {
                    $notif->users()->syncWithoutDetaching([$user->id]);
                }
            }
        }

        $lastSeenId = Session::get('notifikasi_last_seen_id', 0);
        $latestRecord = Notifikasi::max('id'); 
        if ($latestRecord) {
            Session::put('notifikasi_last_seen_id', $latestRecord);
        }

        return $table
            ->modifyQueryUsing(function (Builder $query) use ($userRole) {
                if ($userRole === 'design') {
                    $query->whereIn('judul', ['UMKM Perlu Design', 'Design Perlu Revisi', 'Design Perlu Revisi ⚠️']);
                } elseif ($userRole === 'client') {
                    $query->whereIn('judul', ['UMKM Baru Masuk','Design Baru Upload', 'Desain Telah Direvisi 🎨']);
                } elseif ($userRole === 'pic_lapangan') {
                    $query->whereIn('judul', ['Design Perlu Revisi', 'Design Perlu Revisi ⚠️']);
                } elseif ($userRole === 'team_pasang') {
                    $query->where('judul', 'UMKM Perlu Branding');
                }
            })
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
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordUrl(function (Notifikasi $record) {
                // 1. Penanganan khusus untuk 'UMKM Perlu Design'
                if ($record->judul === 'UMKM Perlu Design') {
                    if ($record->umkm_id) {
                        return UmkmDesignResource::getUrl('create', ['umkm' => $record->umkm_id]);
                    }
                    $umkm = \App\Models\Umkm::all()->first(fn($item) => str_contains(strtolower($record->pesan), strtolower($item->nama_usaha)));
                    return $umkm ? UmkmDesignResource::getUrl('create', ['umkm' => $umkm->id]) : UmkmDesignResource::getUrl('create');
                }

                // 2. array untuk mengelompokkan judul yang memiliki tujuan URL sama
                $judul = $record->judul;
                
                return match (true) {
                    $judul === 'UMKM Baru Masuk' => UmkmResource::getUrl('index', ['tableFilters' => ['status' => ['value' => 'pending']]]),
                    
                    $judul === 'Design Baru Upload' => UmkmDesignResource::getUrl('index', ['tableFilters' => ['status' => ['value' => 'pending']]]),
                    
                    $judul === 'Desain Telah Direvisi 🎨' => UmkmDesignResource::getUrl('index', ['tableFilters' => ['status' => ['value' => 'revised']]]),
                    
                    $judul === 'Design Perlu Revisi' || $judul === 'Design Perlu Revisi ⚠️' => UmkmDesignResource::getUrl('index', ['tableFilters' => ['status' => ['value' => 'revision_needed']]]),
                    
                    $judul === 'UMKM Perlu Branding' => UmkmResource::getUrl('index', ['tableFilters' => ['status' => ['value' => 'ready_to_brand']]]),
                    
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