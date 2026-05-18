<?php

namespace App\Filament\Widgets;

use App\Models\Umkm;
use App\Models\UmkmDesign;
use App\Filament\Resources\UmkmResource;
use App\Filament\Resources\UmkmDesignResource;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\HtmlString; // WAJIB ADA


class SummaryStatsWidget extends BaseWidget {
    protected static ?int $sort = 1;

    // ====================================================================
    // ATUR RESPONSIVE GRID (2 Kolom di Mobile, 3 di Tablet, 4 di Desktop)
    // ====================================================================
    protected array | int | string $columns = [
        'default' => 2, // Di layar HP (Mobile), paksa jadi 2 kolom ke bawah
        'sm' => 2,      // Layar kecil (Small devices)
        'md' => 3,      // Layar medium (Tablet)
        'lg' => 4,      // Layar besar (Desktop komputer)
    ];
    // ====================================================================

    protected function getStats(): array {
        $stats = [];
        $userRole = auth()->user()?->role;
        
        // Base Style CSS Murni untuk efek timbul dan melayang
        $baseStyle = 'box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.4), 0 4px 6px -4px rgba(0, 0, 0, 0.4); border-radius: 0.75rem; cursor: pointer; transition: transform 0.2s, filter 0.2s;';
        
        // Memaksa nilai angka utama dan ikon menjadi putih terang via Tailwind Utility beserta Icon Deskripsi
        $extraHtmlStyles = [
            'class' => '[&_*]:text-white [&_*]:text-white/90 [&_p]:text-white [&_span]:text-white [&_svg]:!text-white'
        ];

        // ====================================================================
        // 1. AKSES: PIC LAPANGAN (Untuk Tambah Data)
        // ====================================================================
        if ( in_array($userRole, ['pic_lapangan']) ) {
            $stats[] = Stat::make( new HtmlString('<span style="color: #ffffff !important; font-weight: 600;">Create Data UMKM</span>'), 'Tambah' )
            ->description( new HtmlString('<span style="color: #ffffff !important; opacity: 0.9;">Tambah data UMKM</span>') )
            ->descriptionIcon( 'heroicon-m-building-storefront' )
            ->color( 'primary' )
            ->url( UmkmResource::getUrl( 'create' ) )
            ->extraAttributes(array_merge($extraHtmlStyles, [
                'style' => $baseStyle . ' background-color: #2563eb;', // Biru Solid
                'onmouseover' => "this.style.transform='translateY(-4px)'; this.style.filter='brightness(1.15)';",
                'onmouseout' => "this.style.transform='translateY(0)'; this.style.filter='brightness(1)';"
            ]));
        }

        if ( in_array($userRole, ['design']) ) {
            $stats[] = Stat::make( new HtmlString('<span style="color: #ffffff !important; font-weight: 600;">Create Design UMKM</span>'), 'Tambah' )
            ->description( new HtmlString('<span style="color: #ffffff !important; opacity: 0.9;">Tambah design UMKM</span>') )
            ->descriptionIcon( 'heroicon-m-paint-brush' )
            ->color( 'success' )
            ->url( UmkmDesignResource::getUrl( 'create' ) )
            ->extraAttributes(array_merge($extraHtmlStyles, [
                'style' => $baseStyle . ' background-color: #16a34a;', // Hijau Solid
                'onmouseover' => "this.style.transform='translateY(-4px)'; this.style.filter='brightness(1.15)';",
                'onmouseout' => "this.style.transform='translateY(0)'; this.style.filter='brightness(1)';"
            ]));
        }

        if( in_array($userRole, ['client']) ) {
            $stats[] = Stat::make( new HtmlString('<span style="color: #ffffff !important; font-weight: 600;">Design Perlu Review</span>'), UmkmDesign::where( 'status', 'pending' )->count() )
            ->description( new HtmlString('<span style="color: #ffffff !important; opacity: 0.9;">Menunggu review</span>') )
            ->descriptionIcon( 'heroicon-m-clock' )
            ->color( 'warning' )
            ->url( UmkmDesignResource::getUrl( 'index', [
                'tableFilters' => [
                    'status' => [ 'value' => 'pending' ],
                ],
            ] ) )
            ->extraAttributes(array_merge($extraHtmlStyles, [
                'style' => $baseStyle . ' background-color: #ea580c;', // Oranye Solid
                'onmouseover' => "this.style.transform='translateY(-4px)'; this.style.filter='brightness(1.15)';",
                'onmouseout' => "this.style.transform='translateY(0)'; this.style.filter='brightness(1)';"
            ]));
        }

        // ====================================================================
        // 2. AKSES: KHUSUS DESIGN & ADMIN (Hanya yang berkaitan dengan DESIGN)
        // ====================================================================
        if ( in_array($userRole, ['design', 'admin']) ) {
            
            $antreanDesignCount = Umkm::where('status', 'approved')
                ->where(function ($query) {
                    $query->whereNull('design_final')
                          ->orWhereNull('design_gerobak_depan')
                          ->orWhereNull('design_gerobak_kiri')
                          ->orWhereNull('design_gerobak_kanan');
                })
                ->count();

            // UMKM Perlu di-Design (Oranye Pekat)
            $stats[] = Stat::make( new HtmlString('<span style="color: #ffffff !important; font-weight: 600;">UMKM Perlu di-Design</span>'), $antreanDesignCount )
            ->description( new HtmlString('<span style="color: #ffffff !important; opacity: 0.9;">UMKM menunggu antrean design</span>') )
            ->descriptionIcon( 'heroicon-m-paint-brush' )
            ->color( 'warning' )
            ->url('#tabel-antrean-design') 
            ->extraAttributes(array_merge($extraHtmlStyles, [
                'style' => $baseStyle . ' background-color: #d97706;', 
                'onmouseover' => "this.style.transform='translateY(-4px)'; this.style.filter='brightness(1.15)';",
                'onmouseout' => "this.style.transform='translateY(0)'; this.style.filter='brightness(1)';"
            ]));

            // Design Approved (Hijau Emerald Solid)
            $stats[] = Stat::make( new HtmlString('<span style="color: #ffffff !important; font-weight: 600;">Design Approved</span>'), UmkmDesign::where( 'status', 'approved' )->count() )
            ->description( new HtmlString('<span style="color: #ffffff !important; opacity: 0.9;">Design disetujui</span>') )
            ->descriptionIcon( 'heroicon-m-paint-brush' )
            ->color( 'success' )
            ->url( UmkmDesignResource::getUrl( 'index', [
                'tableFilters' => [
                    'status' => [ 'value' => 'approved' ],
                ],
            ] ) )
            ->extraAttributes(array_merge($extraHtmlStyles, [
                'style' => $baseStyle . ' background-color: #059669;', 
                'onmouseover' => "this.style.transform='translateY(-4px)'; this.style.filter='brightness(1.15)';",
                'onmouseout' => "this.style.transform='translateY(0)'; this.style.filter='brightness(1)';"
            ]));

            // Design Perlu Revisi (Merah Solid)
            $stats[] = Stat::make( new HtmlString('<span style="color: #ffffff !important; font-weight: 600;">Design Perlu Revisi</span>'), UmkmDesign::where( 'status', 'revision_needed' )->count() )
            ->description( new HtmlString('<span style="color: #ffffff !important; opacity: 0.9;">Perlu diperbaiki</span>') )
            ->descriptionIcon( 'heroicon-m-exclamation-triangle' )
            ->color( 'danger' )
            ->url( UmkmDesignResource::getUrl( 'index', [
                'tableFilters' => [
                    'status' => [ 'value' => 'revision_needed' ],
                ],
            ] ) )
            ->extraAttributes(array_merge($extraHtmlStyles, [
                'style' => $baseStyle . ' background-color: #dc2626;', 
                'onmouseover' => "this.style.transform='translateY(-4px)'; this.style.filter='brightness(1.15)';",
                'onmouseout' => "this.style.transform='translateY(0)'; this.style.filter='brightness(1)';"
            ]));

            // Design Sudah Direvisi (Cyan/Teal Solid)
            $stats[] = Stat::make( new HtmlString('<span style="color: #ffffff !important; font-weight: 600;">Design Sudah Direvisi</span>'), UmkmDesign::where( 'status', 'revised' )->count() )
            ->description( new HtmlString('<span style="color: #ffffff !important; opacity: 0.9;">Menunggu review ulang</span>') )
            ->descriptionIcon( 'heroicon-m-arrow-path-rounded-square' )
            ->color( 'info' )
            ->url( UmkmDesignResource::getUrl( 'index', [
                'tableFilters' => [
                    'status' => [ 'value' => 'revised' ],
                ],
            ] ) )
            ->extraAttributes(array_merge($extraHtmlStyles, [
                'style' => $baseStyle . ' background-color: #0891b2;', 
                'onmouseover' => "this.style.transform='translateY(-4px)'; this.style.filter='brightness(1.15)';",
                'onmouseout' => "this.style.transform='translateY(0)'; this.style.filter='brightness(1)';"
            ]));
        }

        // ====================================================================
        // 3. AKSES: ADMIN, PIC LAPANGAN, & CLIENT (Card Umum/Non-Design)
        // ====================================================================
        if ( in_array($userRole, ['admin', 'pic_lapangan', 'client']) ) {
            
            // Total UMKM Masuk (Indigo / Ungu)
            $stats[] = Stat::make( new HtmlString('<span style="color: #ffffff !important; font-weight: 600;">Total UMKM Masuk</span>'), Umkm::count() )
            ->description( new HtmlString('<span style="color: #ffffff !important; opacity: 0.9;">Semua kota</span>') )
            ->descriptionIcon( 'heroicon-m-building-storefront' )
            ->color( 'primary' )
            ->url( UmkmResource::getUrl( 'index' ) )
            ->extraAttributes(array_merge($extraHtmlStyles, [
                'style' => $baseStyle . ' background-color: #4f46e5;', 
                'onmouseover' => "this.style.transform='translateY(-4px)'; this.style.filter='brightness(1.15)';",
                'onmouseout' => "this.style.transform='translateY(0)'; this.style.filter='brightness(1)';"
            ]));

            // UMKM Approved (Hijau)
            $stats[] = Stat::make( new HtmlString('<span style="color: #ffffff !important; font-weight: 600;">UMKM Approved</span>'), Umkm::where( 'status', 'approved' )->count() )
            ->description( new HtmlString('<span style="color: #ffffff !important; opacity: 0.9;">Sudah disetujui</span>') )
            ->descriptionIcon( 'heroicon-m-check-circle' )
            ->color( 'success' )
            ->url( UmkmResource::getUrl( 'index', [
                'tableFilters' => [
                    'status' => [ 'value' => 'approved' ],
                ],
            ] ) )
            ->extraAttributes(array_merge($extraHtmlStyles, [
                'style' => $baseStyle . ' background-color: #16a34a;', 
                'onmouseover' => "this.style.transform='translateY(-4px)'; this.style.filter='brightness(1.15)';",
                'onmouseout' => "this.style.transform='translateY(0)'; this.style.filter='brightness(1)';"
            ]));

            // UMKM Pending (Amber / Kuning)
            $stats[] = Stat::make( new HtmlString('<span style="color: #ffffff !important; font-weight: 600;">UMKM Pending</span>'), Umkm::where( 'status', 'pending' )->count() )
            ->description( new HtmlString('<span style="color: #ffffff !important; opacity: 0.9;">Menunggu review</span>') )
            ->descriptionIcon( 'heroicon-m-clock' )
            ->color( 'warning' )
            ->url( UmkmResource::getUrl( 'index', [
                'tableFilters' => [
                    'status' => [ 'value' => 'pending' ],
                ],
            ] ) )
            ->extraAttributes(array_merge($extraHtmlStyles, [
                'style' => $baseStyle . ' background-color: #ea580c;', 
                'onmouseover' => "this.style.transform='translateY(-4px)'; this.style.filter='brightness(1.15)';",
                'onmouseout' => "this.style.transform='translateY(0)'; this.style.filter='brightness(1)';"
            ]));

            // UMKM Ditolak (Merah Tua)
            $stats[] = Stat::make( new HtmlString('<span style="color: #ffffff !important; font-weight: 600;">UMKM Ditolak</span>'), Umkm::where( 'status', 'rejected' )->count() )
            ->description( new HtmlString('<span style="color: #ffffff !important; opacity: 0.9;">Tidak memenuhi syarat</span>') )
            ->descriptionIcon( 'heroicon-m-x-circle' )
            ->color( 'danger' )
            ->url( UmkmResource::getUrl( 'index', [
                'tableFilters' => [
                    'status' => [ 'value' => 'rejected' ],
                ],
            ] ) )
            ->extraAttributes(array_merge($extraHtmlStyles, [
                'style' => $baseStyle . ' background-color: #be123c;', 
                'onmouseover' => "this.style.transform='translateY(-4px)'; this.style.filter='brightness(1.15)';",
                'onmouseout' => "this.style.transform='translateY(0)'; this.style.filter='brightness(1)';"
            ]));
        }

        return $stats;
    }
}