<?php

namespace App\Filament\Widgets;

use App\Models\Umkm;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\HtmlString;

class SummaryPerKotaWidget extends BaseWidget {
    protected static ?string $heading = 'PENGAJUAN TERBARU';

    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

   public static function canView(): bool
{
    // Mengambil user yang sedang login
    $user = auth()->user();

    // Memastikan user ada dan role-nya adalah 'admin' atau 'client'
    return $user && in_array($user->role, ['admin', 'client']);
}

    public function table( Table $table ): Table {
        $user = auth()->user();

        return $table
        ->query(
            Umkm::query()   
            ->with( 'kota' ) 
            // ====================================================================
            // TAMBAHAN FILTER PRIVASI KOTA: Kunci data jika bukan Admin/Client global
            // ====================================================================
            // ->when(in_array($user?->role, ['design', 'pic_lapangan']) && $user?->kota_id, function ($query) use ($user) {
            //     $query->where('kota_id', $user->kota_id);
            // })
            ->latest()
            ->limit( 10 ) 
        )

        ->headerActions( [
            // 1. INJEKSI CSS MURNI (Disembunyikan secara visual, hanya memuat style)
            Tables\Actions\Action::make('custom_css_injector')
                ->label('')
                ->disabled()
                ->extraAttributes([
                    'style' => 'display: none !important; padding: 0 !important; margin: 0 !important;'
                ])
                ->icon(fn () => new HtmlString('
                    <style>
                        /* Mewarnai Latar Belakang & Teks Header Tabel */
                        .fi-ta-table thead, 
                        .fi-ta-table thead tr {
                            background-color: #ea580c !important; /* Warna Oranye Filament */
                        }
                        
                        /* Memaksa text th menjadi putih bersih, tebal, dan kontras */
                        .fi-ta-table thead th span,
                        .fi-ta-table thead th {
                            color: #ffffff !important;
                            font-weight: 700 !important;
                            letter-spacing: 0.05em !important;
                        }

                        /* Kustomisasi Tombol "Lihat Semua" agar serasi dengan Oranye (Dark Charcoal Style) */
                        .btn-lihat-semua-custom {
                            background-color: #1e293b !important; /* Slate / Dark Charcoal */
                            color: #ffffff !important;
                            border: 1px solid #334155 !important;
                            border-radius: 8px !important;
                            padding: 6px 14px !important;
                            transition: all 0.2s ease-in-out !important;
                        }

                        .btn-lihat-semua-custom:hover {
                            background-color: #334155 !important; /* Lebih terang saat di-hover */
                            transform: translateY(-1px);
                            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                        }
                        
                        /* Menghilangkan border default gray bawah thead */
                        .fi-ta-header-cell {
                            border-bottom: none !important;
                        }
                    </style>
                ')),

            // 2. TOMBOL UTAMA "LIHAT SEMUA"
            Tables\Actions\Action::make( 'lihat_semua' )
            ->label( 'Lihat Semua' )
            ->url( route( 'filament.admin.resources.umkms.index' ) )
            ->icon( 'heroicon-m-arrow-right' )
            ->extraAttributes([
                'class' => 'btn-lihat-semua-custom' // Menyambungkan ke CSS di atas
            ]),
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

                // 2. Menampilkan Alasan Reject dengan CSS Murni (Inline Styles)
                \Filament\Infolists\Components\TextEntry::make('alasan_reject')
                    ->label('Alasan Penolakan (Reject)')
                    ->placeholder('Tidak ada alasan tertulis.')
                    ->color('danger')
                    ->weight('bold')
                    ->icon('heroicon-m-exclamation-triangle')
                    ->iconColor('danger')
                    
                    // KUNCI UTAMA: Menggunakan CSS Murni / Inline Styles
                    ->extraAttributes([
                        'style' => '
                            margin-top: 8px;
                            padding: 16px;
                            background-color: rgba(239, 68, 68, 0.08); /* Warna merah transparan soft */
                            border-left: 4px solid #ef4444;            /* Garis vertikal merah tegas */
                            border-top-right-radius: 12px;
                            border-bottom-right-radius: 12px;
                            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
                            white-space: normal;
                            word-break: break-word;
                        '
                    ])
                    ->visible(fn ($record) => $record?->status === 'rejected'),

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
                \Filament\Infolists\Components\Section::make('Ukuran Panel')
                    ->schema([
                        \Filament\Infolists\Components\ViewEntry::make('ukuran_panel_table')
                            ->view('filament.infolists.components.tabel-panel')
                            ->columnSpanFull(),
                    ]),
                
                // FOTO
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
                    \Filament\Infolists\Components\ImageEntry::make( 'foto_plang_alfamart' )
                    ->label( 'Foto Plang Alfamart' )
                    ->height( 200 )
                    ->extraAttributes(fn ($record) => [
                        'class' => 'cursor-pointer hover:scale-105 transition duration-300 rounded-lg overflow-hidden',
                        'x-on:click' => '$dispatch("open-preview-modal", { src: "' . asset('storage/' . $record->foto_kiri) . '" })',
                    ]),
                ] )
                ->columns( 3 ),

                // DESIGN GEROBAK
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
                ->columns(3)
                   ->collapsible() 
                ->visible(fn ($record) =>
                    !empty($record->design_final) ||
                    !empty($record->design_gerobak_depan) ||
                    !empty($record->design_gerobak_kiri) ||
                    !empty($record->design_gerobak_kanan)
                ),
                 //  Section "UMKM Terbranding" 
            \Filament\Infolists\Components\Section::make('UMKM Terbranding')
            // tambahin background dan icon biar lebih menonjol bahwa ini sudah selesai dibranding
                ->description('UMKM yang sudah selesai proses branding dan pemasangan stiker.')
                ->icon('heroicon-o-check-badge')
                ->schema([
                    \Filament\Infolists\Components\TextEntry::make('status_pasang')
                        ->label('Status Branding')
                        ->default('SELESAI BRANDING')
                        ->badge()
                        ->color('success'),
                    \Filament\Infolists\Components\TextEntry::make('updated_at')
                        ->label('Tanggal Selesai')
                        ->dateTime('d M Y H:i'),
                    \Filament\Infolists\Components\ImageEntry::make('stiker_tampak_depan')
                        ->label('Stiker Tampak Depan')
                        ->height(200)
                        ->extraAttributes(fn ($record) => [
                            'class' => 'cursor-pointer hover:scale-105 transition duration-300 rounded-lg overflow-hidden',
                            'x-on:click' => '$dispatch("open-preview-modal", { src: "' . asset('storage/' . $record->stiker_tampak_depan) . '" })',
                        ]),
                    \Filament\Infolists\Components\ImageEntry::make('stiker_tampak_kanan')
                        ->label('Stiker Tampak Kanan')
                        ->height(200)
                        ->extraAttributes(fn ($record) => [
                            'class' => 'cursor-pointer hover:scale-105 transition duration-300 rounded-lg overflow-hidden',
                            'x-on:click' => '$dispatch("open-preview-modal", { src: "' . asset('storage/' . $record->stiker_tampak_kanan) . '" })',
                        ]),
                    \Filament\Infolists\Components\ImageEntry::make('stiker_tampak_kiri')
                        ->label('Stiker Tampak Kiri')   
                        ->height(200)
                        ->extraAttributes(fn ($record) => [
                            'class' => 'cursor-pointer hover:scale-105 transition duration-300 rounded-lg overflow-hidden',
                            'x-on:click' => '$dispatch("open-preview-modal", { src: "' . asset('storage/' . $record->stiker_tampak_kiri) . '" })',
                        ]),
                    \Filament\Infolists\Components\ImageEntry::make('foto_wide')
                        ->label('Foto Wide (Keseluruhan)')
                        ->height(200)
                        ->extraAttributes(fn ($record) => [
                            'class' => 'cursor-pointer hover:scale-105 transition duration-300 rounded-lg   overflow-hidden',
                            'x-on:click' => '$dispatch("open-preview-modal", { src: "' . asset('storage/' . $record->foto_wide) . '" })',
                        ]),
                ])->columns(2)
                ->collapsible() 
                ->visible(fn ($record) =>
                    !empty($record->stiker_tampak_depan) ||
                    !empty($record->stiker_tampak_kanan) ||
                    !empty($record->stiker_tampak_kiri) ||
                    !empty($record->foto_wide)
                ),
            ] ),
        ] )

        // supaya klik row buka popup
        ->recordAction( 'view' )
        ->paginated()
        ->striped()
        ->poll('5s');
    }
}