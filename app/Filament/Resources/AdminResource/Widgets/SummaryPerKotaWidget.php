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
            ->with( 'kota' ) // Sesuaikan nama relasi kota jika berbeda
            ->latest()
            ->limit( 10 ) 
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

                // =========================================================================
                // INJEKSI LIGHTBOX POPUP COMPONENT (Stanby mendengarkan klik pada gambar)
                // =========================================================================
                \Filament\Infolists\Components\ViewEntry::make('image_lightbox')
                    ->view('filament.infolists.components.image-lightbox')
                    ->columnSpanFull(),

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

                // FOTO (Diubah ke sistem trigger click Event Lightbox)
                \Filament\Infolists\Components\Section::make( 'Foto' )
                ->schema( [
                    \Filament\Infolists\Components\ImageEntry::make( 'foto_depan' )
                    ->label( 'Foto Depan' )
                    ->height( 200 )
                    ->extraAttributes(fn ($record) => [
                        'class' => 'cursor-pointer hover:scale-105 transition duration-300 rounded-lg overflow-hidden',
                        'x-on:click' => '$dispatch("open-preview-modal", { src: "' . asset('storage/' . $record->foto_depan) . '" })',
                    ]),

                    \Filament\Infolists\Components\ImageEntry::make( 'foto_kanan' )
                    ->label( 'Foto Kanan' )
                    ->height( 200 )
                    ->extraAttributes(fn ($record) => [
                        'class' => 'cursor-pointer hover:scale-105 transition duration-300 rounded-lg overflow-hidden',
                        'x-on:click' => '$dispatch("open-preview-modal", { src: "' . asset('storage/' . $record->foto_kanan) . '" })',
                    ]),

                    \Filament\Infolists\Components\ImageEntry::make( 'foto_kiri' )
                    ->label( 'Foto Kiri' )
                    ->height( 200 )
                    ->extraAttributes(fn ($record) => [
                        'class' => 'cursor-pointer hover:scale-105 transition duration-300 rounded-lg overflow-hidden',
                        'x-on:click' => '$dispatch("open-preview-modal", { src: "' . asset('storage/' . $record->foto_kiri) . '" })',
                    ]),
                ] )
                ->columns( 3 ), // Diubah ke 3 agar sebaris rapi

                // DESIGN GEROBAK (Diubah ke sistem trigger click Event Lightbox)
                \Filament\Infolists\Components\Section::make('Design Gerobak')
                ->description('Design final dan tampak gerobak yang sudah disetujui.')
                ->icon('heroicon-o-paint-brush')
                ->schema([
                    \Filament\Infolists\Components\ImageEntry::make('design_final')
                    ->label('Design Final')
                    ->height(220)
                    ->columnSpanFull() 
                    ->extraAttributes(fn ($record) => [
                        'class' => 'cursor-pointer hover:scale-105 transition duration-300 rounded-lg overflow-hidden',
                        'x-on:click' => '$dispatch("open-preview-modal", { src: "' . asset('storage/' . $record->design_final) . '" })',
                    ])
                    ->visible(fn ($record) => !empty($record->design_final)),

                    \Filament\Infolists\Components\ImageEntry::make('design_gerobak_depan')
                    ->label('Gerobak Tampak Depan')
                    ->height(200)
                    ->extraAttributes(fn ($record) => [
                        'class' => 'cursor-pointer hover:scale-105 transition duration-300 rounded-lg overflow-hidden',
                        'x-on:click' => '$dispatch("open-preview-modal", { src: "' . asset('storage/' . $record->design_gerobak_depan) . '" })',
                    ])
                    ->visible(fn ($record) => !empty($record->design_gerobak_depan)),

                    \Filament\Infolists\Components\ImageEntry::make('design_gerobak_kiri')
                    ->label('Gerobak Tampak Kiri')
                    ->height(200)
                    ->extraAttributes(fn ($record) => [
                        'class' => 'cursor-pointer hover:scale-105 transition duration-300 rounded-lg overflow-hidden',
                        'x-on:click' => '$dispatch("open-preview-modal", { src: "' . asset('storage/' . $record->design_gerobak_kiri) . '" })',
                    ])
                    ->visible(fn ($record) => !empty($record->design_gerobak_kiri)),

                    \Filament\Infolists\Components\ImageEntry::make('design_gerobak_kanan')
                    ->label('Gerobak Tampak Kanan')
                    ->height(200)
                    ->extraAttributes(fn ($record) => [
                        'class' => 'cursor-pointer hover:scale-105 transition duration-300 rounded-lg overflow-hidden',
                        'x-on:click' => '$dispatch("open-preview-modal", { src: "' . asset('storage/' . $record->design_gerobak_kanan) . '" })',
                    ])
                    ->visible(fn ($record) => !empty($record->design_gerobak_kanan)),
                ])
                ->columns(3) // Diubah ke 3 agar simetris di layout web m2 screen
                ->collapsible() 
                ->visible(fn ($record) =>
                    !empty($record->design_final) ||
                    !empty($record->design_gerobak_depan) ||
                    !empty($record->design_gerobak_kiri) ||
                    !empty($record->design_gerobak_kanan)
                ),
            ] ),
        ] )

        // supaya klik row buka popup
        ->recordAction( 'view' )
        ->paginated( false )
        ->striped();
    }
}