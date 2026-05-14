<?php

namespace App\Filament\Widgets;

use App\Models\Umkm;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class SummaryPerKotaWidget extends BaseWidget {
    protected static ?string $heading = 'PENGAJUAN TERBARU';

    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    public function table( Table $table ): Table {
        return $table
        ->query(
            Umkm::query()
            ->with( 'kota' )
            ->latest()
            ->limit( 10 ) // hanya 10 data
        )

        // tombol lihat semua
        ->headerActions( [
            Tables\Actions\Action::make( 'lihat_semua' )
            ->label( 'Lihat Semua' )
            ->url( route( 'filament.admin.resources.umkms.index' ) )
            ->color( 'primary' )
            ->icon( 'heroicon-m-arrow-right' ),
        ] )

        ->columns( [

            // USAHA / KOTA
            Tables\Columns\TextColumn::make( 'nama_usaha' )
            ->label( 'USAHA / KOTA' )
            ->description( fn ( $record ) => $record->kota?->nama )
            ->weight( 'bold' )
            ->searchable(),

            // STATUS
            Tables\Columns\TextColumn::make( 'status' )
            ->label( 'STATUS' )
            ->badge()
            ->formatStateUsing( fn ( $state ) => strtoupper( $state ) )
            ->colors( [
                'warning' => 'pending',
                'primary' => 'design_pending',
                'success' => 'approved',
                'danger' => 'rejected',
            ] ),

            // AKSI
            Tables\Columns\IconColumn::make( 'aksi' )
            ->label( 'AKSI' )
            ->icon( 'heroicon-m-chevron-right' )
            ->color( 'gray' ),
        ] )

        // popup view
        ->actions( [

            Tables\Actions\ViewAction::make()
            ->slideOver()
            ->modalWidth( 'screen' )
            ->modalHeading( fn ( $record ) => $record->nama_usaha )

            ->infolist( [

                // DATA PEMILIK
                \Filament\Infolists\Components\Section::make( 'Data Pemilik' )
                ->schema( [

                    \Filament\Infolists\Components\TextEntry::make( 'nama_pemilik' )
                    ->label( 'Nama Pemilik' ),

                    \Filament\Infolists\Components\TextEntry::make( 'nama_usaha' )
                    ->label( 'Nama Usaha' ),

                    \Filament\Infolists\Components\TextEntry::make( 'alamat_usaha' )
                    ->label( 'Alamat' ),

                    \Filament\Infolists\Components\TextEntry::make( 'no_wa' )
                    ->label( 'No WhatsApp' ),

                    \Filament\Infolists\Components\TextEntry::make( 'radius' )
                    ->label( 'Radius Alfamart' ),

                    \Filament\Infolists\Components\TextEntry::make( 'kota.nama' )
                    ->label( 'Kota' ),

                ] )
                ->columns( 2 ),

                // REKENING
                \Filament\Infolists\Components\Section::make( 'Data Rekening' )
                ->schema( [
                    \Filament\Infolists\Components\TextEntry::make( 'no_rekening' )
                    ->label( 'No Rekening' ),

                    \Filament\Infolists\Components\TextEntry::make( 'nama_bank' )
                    ->label( 'Bank' ),

                    \Filament\Infolists\Components\TextEntry::make( 'atas_nama_rekening' )
                    ->label( 'Atas Nama' ),
                ] )
                ->columns( 3 ),

                // LOKASI
                \Filament\Infolists\Components\Section::make( 'Lokasi' )
                ->schema( [

                    \Filament\Infolists\Components\TextEntry::make( 'latitude' )
                    ->label( 'Latitude' ),

                    \Filament\Infolists\Components\TextEntry::make( 'longitude' )
                    ->label( 'Longitude' ),

                    \Filament\Infolists\Components\TextEntry::make( 'sharelock_url' )
                    ->label( 'Google Maps' )
                    ->url( fn ( $record ) => $record->sharelock_url )
                    ->openUrlInNewTab(),
                ] )
                ->columns( 2 ),

                // UKURAN PANEL
                \Filament\Infolists\Components\Section::make( 'Ukuran Panel' )
                ->schema( [

                    \Filament\Infolists\Components\TextEntry::make( 'total_area_branding' )
                    ->label( 'Total Area Branding' ),

                    \Filament\Infolists\Components\TextEntry::make( 'depan_panel_atas_m2' )
                    ->label( 'Depan Atas' ),

                    \Filament\Infolists\Components\TextEntry::make( 'depan_panel_tengah_m2' )
                    ->label( 'Depan Tengah' ),

                    \Filament\Infolists\Components\TextEntry::make( 'depan_panel_bawah_m2' )
                    ->label( 'Depan Bawah' ),

                    \Filament\Infolists\Components\TextEntry::make( 'kanan_panel_atas_m2' )
                    ->label( 'Kanan Atas' ),

                    \Filament\Infolists\Components\TextEntry::make( 'kanan_panel_tengah_m2' )
                    ->label( 'Kanan Tengah' ),

                    \Filament\Infolists\Components\TextEntry::make( 'kanan_panel_bawah_m2' )
                    ->label( 'Kanan Bawah' ),

                    \Filament\Infolists\Components\TextEntry::make( 'kiri_panel_atas_m2' )
                    ->label( 'Kiri Atas' ),

                    \Filament\Infolists\Components\TextEntry::make( 'kiri_panel_tengah_m2' )
                    ->label( 'Kiri Tengah' ),

                    \Filament\Infolists\Components\TextEntry::make( 'kiri_panel_bawah_m2' )
                    ->label( 'Kiri Bawah' ),

                ] )
                ->columns( 3 ),

                // FOTO
                \Filament\Infolists\Components\Section::make( 'Foto' )
                ->schema( [

                    \Filament\Infolists\Components\ImageEntry::make( 'foto_depan' )
                    ->label( 'Foto Depan' )
                    ->height( 200 )
                    ->url( fn ( $record ) => asset( 'storage/' . $record->foto_depan ), true ),

                    \Filament\Infolists\Components\ImageEntry::make( 'foto_kanan' )
                    ->label( 'Foto Kanan' )
                    ->height( 200 )
                    ->url( fn ( $record ) => asset( 'storage/' . $record->foto_kanan ), true ),

                    \Filament\Infolists\Components\ImageEntry::make( 'foto_kiri' )
                    ->label( 'Foto Kiri' )
                    ->height( 200 )
                    ->url( fn ( $record ) => asset( 'storage/' . $record->foto_kiri ), true ),

                ] )
                ->columns( 2 ),

            ] ),
        ] )

        // supaya klik row buka popup
        ->recordAction( 'view' )

        ->paginated( false )

        ->striped();
    }
}