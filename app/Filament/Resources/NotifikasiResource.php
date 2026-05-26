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
use Illuminate\Database\Eloquent\Builder;
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
    protected static ?string $navigationBadgeColor = 'danger';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    private static function applyRoleFilter(Builder $query, $user): Builder
    {
        return match ($user->role) {
            'admin' => $query->where('user_id', $user->id),
            'client' => $query->where('user_id', $user->id),
            'design' => $query->where('user_id', $user->id),
            'pic_lapangan' => $query->where('user_id', $user->id),
            'team_pasang' => $query->where('user_id', $user->id),
            default => $query->where('user_id', $user->id),
        };
    }

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        if (!$user) return null;

        $query = Notifikasi::where('user_id', $user->id)
            ->where('is_read', false);

        $count = $query->count();
        return $count > 0 ? (string) $count : null;
    }

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

        // Mark semua notifikasi user sebagai read saat buka halaman
        if ($user) {
            Notifikasi::where('user_id', $user->id)
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }

        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('user_id', $user?->id))
            ->columns([
                Tables\Columns\TextColumn::make('judul')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('pesan')
                    ->limit(80)
                    ->tooltip(fn ($record) => $record->pesan),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordUrl(function (Notifikasi $record) {
                return match ($record->tipe) {
                    'umkm_baru' => UmkmResource::getUrl('index', ['tableFilters' => ['status' => ['value' => 'pending']]]),
                    'umkm_approved' => UmkmResource::getUrl('index', ['tableFilters' => ['status' => ['value' => 'approved']]]),
                    'umkm_rejected' => UmkmResource::getUrl('index', ['tableFilters' => ['status' => ['value' => 'rejected']]]),
                    'perlu_design' => UmkmDesignResource::getUrl('create', ['umkm' => $record->notifiable_id]),
                    'design_baru' => UmkmDesignResource::getUrl('index', ['tableFilters' => ['status' => ['value' => 'pending']]]),
                    'perlu_revisi' => UmkmDesignResource::getUrl('index', ['tableFilters' => ['status' => ['value' => 'revision_needed']]]),
                    'revised' => UmkmDesignResource::getUrl('index', ['tableFilters' => ['status' => ['value' => 'revised']]]),
                    'siap_pasang' => '/admin/pemasangan-stiker',
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
