<?php

namespace App\Filament\Widgets;

use App\Models\Umkm;
use App\Models\UmkmDesign;
use App\Filament\Resources\UmkmResource;
use App\Filament\Resources\UmkmDesignResource;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SummaryStatsWidget extends BaseWidget {
    protected static ?int $sort = 1;

    protected function getStats(): array {
        $stats = [];
        
        // Create UMKM hanya untuk pic_lapangan
        if ( auth()->user()?->role === 'pic_lapangan' ) {
            $stats[] = Stat::make( 'Create Data UMKM', 'Tambah' )
            ->description( 'Tambah data UMKM' )
            ->descriptionIcon( 'heroicon-m-building-storefront' )
            ->color( 'primary' )
            ->url( UmkmResource::getUrl( 'create' ) );
        }

        // Create Design hanya untuk design
        if ( auth()->user()?->role === 'design' ) {
            $stats[] = Stat::make( 'Create Design UMKM', 'Tambah' )
            ->description( 'Tambah design UMKM' )
            ->descriptionIcon( 'heroicon-m-paint-brush' )
            ->color( 'success' )
            ->url( UmkmDesignResource::getUrl( 'create' ) );
        }

        // Total UMKM Masuk
        $stats[] = Stat::make( 'Total UMKM Masuk', Umkm::count() )
        ->description( 'Semua kota' )
        ->descriptionIcon( 'heroicon-m-building-storefront' )
        ->color( 'primary' )
        ->url( UmkmResource::getUrl( 'index' ) )
        ->extraAttributes( [
            'class' => 'cursor-pointer hover:opacity-80 transition-opacity',
        ] );

        $stats[] = Stat::make( 'Design Perlu Review', UmkmDesign::where( 'status', 'pending' )->count() )
        ->description( 'Menunggu review' )
        ->descriptionIcon( 'heroicon-m-clock' )
        ->color( 'warning' )
        ->url( UmkmDesignResource::getUrl( 'index', [
            'tableFilters' => [
                'status' => [ 'value' => 'pending' ],
            ],
        ] ) )
        ->extraAttributes( [
            'class' => 'cursor-pointer hover:opacity-80 transition-opacity',
        ] );

        // UMKM Approved
        $stats[] = Stat::make( 'UMKM Approved', Umkm::where( 'status', 'approved' )->count() )
        ->description( 'Sudah disetujui' )
        ->descriptionIcon( 'heroicon-m-check-circle' )
        ->color( 'success' )
        ->url( UmkmResource::getUrl( 'index', [
            'tableFilters' => [
                'status' => [ 'value' => 'approved' ],
            ],
        ] ) )
        ->extraAttributes( [
            'class' => 'cursor-pointer hover:opacity-80 transition-opacity',
        ] );

        // UMKM Pending
        $stats[] = Stat::make( 'UMKM Pending', Umkm::where( 'status', 'pending' )->count() )
        ->description( 'Menunggu review' )
        ->descriptionIcon( 'heroicon-m-clock' )
        ->color( 'warning' )
        ->url( UmkmResource::getUrl( 'index', [
            'tableFilters' => [
                'status' => [ 'value' => 'pending' ],
            ],
        ] ) )
        ->extraAttributes( [
            'class' => 'cursor-pointer hover:opacity-80 transition-opacity',
        ] );

        // UMKM Ditolak
        $stats[] = Stat::make( 'UMKM Ditolak', Umkm::where( 'status', 'rejected' )->count() )
        ->description( 'Tidak memenuhi syarat' )
        ->descriptionIcon( 'heroicon-m-x-circle' )
        ->color( 'danger' )
        ->url( UmkmResource::getUrl( 'index', [
            'tableFilters' => [
                'status' => [ 'value' => 'rejected' ],
            ],
        ] ) )
        ->extraAttributes( [
            'class' => 'cursor-pointer hover:opacity-80 transition-opacity',
        ] );

        // Design Approved
        $stats[] = Stat::make( 'Design Approved', UmkmDesign::where( 'status', 'approved' )->count() )
        ->description( 'Design disetujui' )
        ->descriptionIcon( 'heroicon-m-paint-brush' )
        ->color( 'success' )
        ->url( UmkmDesignResource::getUrl( 'index', [
            'tableFilters' => [
                'status' => [ 'value' => 'approved' ],
            ],
        ] ) )
        ->extraAttributes( [
            'class' => 'cursor-pointer hover:opacity-80 transition-opacity',
        ] );

        // Design Perlu Revisi
        $stats[] = Stat::make( 'Design Perlu Revisi', UmkmDesign::where( 'status', 'revision_needed' )->count() )
        ->description( 'Perlu diperbaiki' )
        ->descriptionIcon( 'heroicon-m-exclamation-triangle' )
        ->color( 'danger' )
        ->url( UmkmDesignResource::getUrl( 'index', [
            'tableFilters' => [
                'status' => [ 'value' => 'revision_needed' ],
            ],
        ] ) )
        ->extraAttributes( [
            'class' => 'cursor-pointer hover:opacity-80 transition-opacity',
        ] );

        // ========== TAMBAHAN: DESIGN SUDAH DIREVISI ==========
        $stats[] = Stat::make( 'Design Sudah Direvisi', UmkmDesign::where( 'status', 'revised' )->count() )
        ->description( 'Menunggu review ulang' )
        ->descriptionIcon( 'heroicon-m-arrow-path-rounded-square' )
        ->color( 'info' ) // Warna biru info
        ->url( UmkmDesignResource::getUrl( 'index', [
            'tableFilters' => [
                'status' => [ 'value' => 'revised' ],
            ],
        ] ) )
        ->extraAttributes( [
            'class' => 'cursor-pointer hover:opacity-80 transition-opacity',
        ] );

        return $stats;
    }
}