<?php

namespace App\Filament\Widgets;

use App\Models\Kota;
use App\Models\Umkm;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class SummaryPerKotaWidget extends BaseWidget
{
    protected static ?string $heading = 'Summary UMKM Per Kota';
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Kota::query()->whereHas('umkms'))
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Kota'),
                Tables\Columns\TextColumn::make('total_umkm')
                    ->label('Total Masuk')
                    ->state(fn (Kota $record): int => 
                        Umkm::where('kota_id', $record->id)->count()
                    ),
                Tables\Columns\TextColumn::make('umkm_approved')
                    ->label('Approved')
                    ->state(fn (Kota $record): int => 
                        Umkm::where('kota_id', $record->id)
                            ->where('status', 'approved')
                            ->count()
                    )
                    ->color('success'),
                Tables\Columns\TextColumn::make('umkm_pending')
                    ->label('Pending')
                    ->state(fn (Kota $record): int => 
                        Umkm::where('kota_id', $record->id)
                            ->where('status', 'pending')
                            ->count()
                    )
                    ->color('warning'),
                Tables\Columns\TextColumn::make('umkm_rejected')
                    ->label('Rejected')
                    ->state(fn (Kota $record): int => 
                        Umkm::where('kota_id', $record->id)
                            ->where('status', 'rejected')
                            ->count()
                    )
                    ->color('danger'),
            ])
            ->paginated(false);
    }
}