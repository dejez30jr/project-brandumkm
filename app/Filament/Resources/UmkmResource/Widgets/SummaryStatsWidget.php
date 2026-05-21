<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\UmkmDesignResource;
use App\Filament\Resources\UmkmResource;
use App\Filament\Resources\UmkmTerbrandingResource;
use App\Models\Umkm;
use App\Models\UmkmDesign;
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
        $user = auth()->user();
        $userRole = $user?->role;
        
        // Base Style CSS Murni untuk efek timbul dan melayang
        $baseStyle = 'box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.4), 0 4px 6px -4px rgba(0, 0, 0, 0.4); border-radius: 0.75rem; cursor: pointer; transition: transform 0.2s, filter 0.2s;';
        
        // Memaksa nilai angka utama dan ikon menjadi putih terang via Tailwind Utility beserta Icon Deskripsi
        $extraHtmlStyles = [
            'class' => '[&_*]:text-white [&_*]:text-white/90 [&_p]:text-white [&_span]:text-white [&_svg]:!text-white'
        ];  

        // ====================================================================
        // NEW CODE - 1. CARD: UMKM YANG PERLU DI BRANDING (Akses: team_pasang)
        // ====================================================================
    if ( in_array($userRole, ['team_pasang']) ) {
            
    $queryPerluBranding = Umkm::where(function ($q) {
        // 1. Pastikan semua desain sudah terisi (tidak ada yang NULL)
        $q->whereNotNull('design_final')
          ->whereNotNull('design_gerobak_depan')
          ->whereNotNull('design_gerobak_kiri')
          ->whereNotNull('design_gerobak_kanan');
    })
    ->where(function ($q) {
        // 2. Dan salah satu field stiker masih kosong
        $q->whereNull('stiker_tampak_depan')
          ->orWhereNull('stiker_tampak_kanan')
          ->orWhereNull('stiker_tampak_kiri')
          ->orWhereNull('foto_wide');
    });

    // Batasi hitungan data hanya untuk kota yang sama dengan team_pasang
    // if ($user && !empty($user->kota_id)) {
    //     $queryPerluBranding->where('kota_id', $user->kota_id);
    // }

    $stats[] = Stat::make( new HtmlString('<span style="color: #ffffff !important; font-weight: 600;">UMKM Perlu di-Branding</span>'), $queryPerluBranding->count() )
    ->description( new HtmlString('<span style="color: #ffffff !important; opacity: 0.9;">Total antrean pasang stiker</span>') )
    ->descriptionIcon( 'heroicon-m-clock' )
    ->color( 'warning' )
    ->url('/admin/pemasangan-stiker') 
    ->extraAttributes(array_merge($extraHtmlStyles, [
        'style' => $baseStyle . ' background-color: #b45309;', 
        'onmouseover' => "this.style.transform='translateY(-4px)'; this.style.filter='brightness(1.15)';",
        'onmouseout' => "this.style.transform='translateY(0)'; this.style.filter='brightness(1)';"
    ]));
}

        // ====================================================================
        // NEW CODE - 2. CARD: TOTAL UMKM YANG SUDAH DI BRANDING (Akses: admin, design, client)
        // ====================================================================
        if ( in_array($userRole, ['admin', 'client', 'team_pasang']) ) {
            
            // Kriteria: Ke-4 foto dokumentasi stiker WAJIB sudah keisi semua (bukan NULL)
            $totalSudahBranding = Umkm::whereNotNull('stiker_tampak_depan')
                ->whereNotNull('stiker_tampak_kanan')
                ->whereNotNull('stiker_tampak_kiri')
                ->whereNotNull('foto_wide')
                ->count();

            $stats[] = Stat::make( new HtmlString('<span style="color: #ffffff !important; font-weight: 600;">Total UMKM Sudah di-Branding</span>'), $totalSudahBranding )
            ->description( new HtmlString('<span style="color: #ffffff !important; opacity: 0.9;">Gerobak selesai pasang stiker</span>') )
            ->descriptionIcon( 'heroicon-m-check-circle' )
            ->color( 'success' )
            ->url( UmkmTerbrandingResource::getUrl( 'index' ) )
            ->extraAttributes(array_merge($extraHtmlStyles, [
                'style' => $baseStyle . ' background-color: #047857;', // Teal/Hijau Hutan Tua Solid
                'onmouseover' => "this.style.transform='translateY(-4px)'; this.style.filter='brightness(1.15)';",
                'onmouseout' => "this.style.transform='translateY(0)'; this.style.filter='brightness(1)';"
            ]));
        }

        // ====================================================================
        // 3. AKSES: PIC LAPANGAN (Untuk Tambah Data)
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
// 4. AKSES: KHUSUS DESIGN & ADMIN (Hanya yang berkaitan dengan DESIGN)
// ====================================================================
if (in_array($userRole, ['design', 'admin'])) {
    
    // --- Query Antrean (Hanya untuk Role Design) ---
    if ($userRole === 'design') {
        $queryAntrean = Umkm::query()
            ->where('umkms.status', 'approved')
            ->leftJoin('umkm_designs', 'umkm_designs.umkm_id', '=', 'umkms.id')
            ->where(function ($q) use ($user) {
                $q->whereNull('umkm_designs.id')
                  ->orWhere(function ($sub) use ($user) {
                      $sub->where('umkm_designs.status', 'revision_needed')
                          ->where('umkm_designs.designer_id', $user->id);
                  });
            });

        $stats[] = Stat::make(new HtmlString('<span style="color: #ffffff !important; font-weight: 600;">UMKM Perlu di-Design</span>'), $queryAntrean->count())
            ->description(new HtmlString('<span style="color: #ffffff !important; opacity: 0.9;">Menunggu antrean design</span>'))
            ->descriptionIcon('heroicon-m-paint-brush')
            ->color('warning')
            ->url('#tabel-antrean-design')
            ->extraAttributes(array_merge($extraHtmlStyles, [
                'style' => $baseStyle . ' background-color: #d97706;',
                'onmouseover' => "this.style.transform='translateY(-4px)'; this.style.filter='brightness(1.15)';",
                'onmouseout' => "this.style.transform='translateY(0)'; this.style.filter='brightness(1)';"
            ]));
    }

    // --- Statistik Design (Admin & Design bisa melihat) ---
    
    // 1. Design Approved
    $stats[] = Stat::make(
        new HtmlString('<span style="color: #ffffff !important; font-weight: 600;">Design Approved</span>'),
        ($userRole === 'design' ? UmkmDesign::where('status', 'approved')->where('designer_id', $user->id) : UmkmDesign::where('status', 'approved'))->count()
    )
    ->description(new HtmlString('<span style="color: #ffffff !important; opacity: 0.9;">Design disetujui</span>'))
    ->descriptionIcon('heroicon-m-paint-brush')
    ->color('success')
    ->url(UmkmDesignResource::getUrl('index', ['tableFilters' => ['status' => ['value' => 'approved']]]))
    ->extraAttributes(array_merge($extraHtmlStyles, [
        'style' => $baseStyle . ' background-color: #059669;',
        'onmouseover' => "this.style.transform='translateY(-4px)'; this.style.filter='brightness(1.15)';",
        'onmouseout' => "this.style.transform='translateY(0)'; this.style.filter='brightness(1)';"
    ]));

    // 2. Design Perlu Revisi
    $stats[] = Stat::make(
        new HtmlString('<span style="color: #ffffff !important; font-weight: 600;">Design Perlu Revisi</span>'),
        ($userRole === 'design' ? UmkmDesign::where('status', 'revision_needed')->where('designer_id', $user->id) : UmkmDesign::where('status', 'revision_needed'))->count()
    )
    ->description(new HtmlString('<span style="color: #ffffff !important; opacity: 0.9;">Perlu diperbaiki</span>'))
    ->descriptionIcon('heroicon-m-exclamation-triangle')
    ->color('danger')
    ->url(UmkmDesignResource::getUrl('index', ['tableFilters' => ['status' => ['value' => 'revision_needed']]]))
    ->extraAttributes(array_merge($extraHtmlStyles, [
        'style' => $baseStyle . ' background-color: #ef4444;',
        'onmouseover' => "this.style.transform='translateY(-4px)'; this.style.filter='brightness(1.15)';",
        'onmouseout' => "this.style.transform='translateY(0)'; this.style.filter='brightness(1)';"
    ]));

    // 3. Design Sudah Direvisi
    $stats[] = Stat::make(
        new HtmlString('<span style="color: #ffffff !important; font-weight: 600;">Design Sudah Direvisi</span>'),
        ($userRole === 'design' ? UmkmDesign::where('status', 'revised')->where('designer_id', $user->id) : UmkmDesign::where('status', 'revised'))->count()
    )
    ->description(new HtmlString('<span style="color: #ffffff !important; opacity: 0.9;">Menunggu review ulang</span>'))
    ->descriptionIcon('heroicon-m-arrow-path-rounded-square')
    ->color('info')
    ->url(UmkmDesignResource::getUrl('index', ['tableFilters' => ['status' => ['value' => 'revised']]]))
    ->extraAttributes(array_merge($extraHtmlStyles, [
        'style' => $baseStyle . ' background-color: #0891b2;',
        'onmouseover' => "this.style.transform='translateY(-4px)'; this.style.filter='brightness(1.15)';",
        'onmouseout' => "this.style.transform='translateY(0)'; this.style.filter='brightness(1)';"
    ]));
}
       
        
        //====================================================================

        // 5. AKSES: ADMIN, PIC LAPANGAN, & CLIENT (Card Umum/Non-Design)

        // ====================================================================
        
  // 1. Definisikan $user dan $userRole di awal sekali (sebelum blok IF manapun)
$user = auth()->user();
$userRole = $user->role;

// 2. Buat query dasar yang terfilter untuk PIC Lapangan
$query = Umkm::query();
if ($userRole === 'pic_lapangan') {
    $query->whereHas('submittedBy', function ($q) use ($user) {
        $q->where('name', $user->name);
    });
}

// 3. Definisikan fungsi $getFilter SEBELUM digunakan
$getFilter = function($status) use ($user, $userRole) {
    $filters = [];
    if (!empty($status)) {
        $filters['status'] = ['value' => $status];
    }
    if ($userRole === 'pic_lapangan') {
        $filters['pic_filter'] = ['value' => $user->name];
    }
    return UmkmResource::getUrl('index', ['tableFilters' => $filters]);
};

// 4. Mulai masukkan ke $stats
// Card khusus PIC Lapangan (Pending)
if ($userRole === 'pic_lapangan') {
    $stats[] = Stat::make(new HtmlString('<span style="color: #ffffff !important; font-weight: 600;">UMKM Pending</span>'), (clone $query)->where('status', 'pending')->count())
        ->description(new HtmlString('<span style="color: #ffffff !important; opacity: 0.9;">Menunggu di review</span>'))
        ->descriptionIcon('heroicon-m-clock')
        ->color('warning')
        ->url($getFilter('pending'))
        ->extraAttributes(array_merge($extraHtmlStyles, ['style' => $baseStyle . ' background-color: #ea580c;']));
}

// 5. Card Umum (Admin, PIC, Client)
if (in_array($userRole, ['admin', 'pic_lapangan', 'client'])) {
    
    // Total
    $stats[] = Stat::make(new HtmlString('<span style="color: #ffffff !important; font-weight: 600;">Total UMKM Masuk</span>'), (clone $query)->count())
        ->description(new HtmlString('<span style="color: #ffffff !important; opacity: 0.9;">' . ($userRole === 'pic_lapangan' ? 'Data Milik Saya' : 'Semua kota') . '</span>'))
        ->descriptionIcon('heroicon-m-building-storefront')
        ->color('primary')
        ->url($getFilter(''))
        ->extraAttributes(array_merge($extraHtmlStyles, ['style' => $baseStyle . ' background-color: #4f46e5;']));

    // Approved
    $stats[] = Stat::make(new HtmlString('<span style="color: #ffffff !important; font-weight: 600;">UMKM Approved</span>'), (clone $query)->where('status', 'approved')->count())
        ->description(new HtmlString('<span style="color: #ffffff !important; opacity: 0.9;">Sudah disetujui</span>'))
        ->descriptionIcon('heroicon-m-check-circle')
        ->color('success')
        ->url($getFilter('approved'))
        ->extraAttributes(array_merge($extraHtmlStyles, ['style' => $baseStyle . ' background-color: #16a34a;']));

    // Pending (Khusus non-pic_lapangan agar tidak double dengan card di atas)
    // if ($userRole !== 'pic_lapangan') {
    //     $stats[] = Stat::make(new HtmlString('<span style="color: #ffffff !important; font-weight: 600;">UMKM Perlu Review</span>'), (clone $query)->where('status', 'pending')->count())
    //         ->description(new HtmlString('<span style="color: #ffffff !important; opacity: 0.9;">Menunggu review</span>'))
    //         ->descriptionIcon('heroicon-m-clock')
    //         ->color('warning')
    //         ->url($getFilter('pending'))
    //         ->extraAttributes(array_merge($extraHtmlStyles, ['style' => $baseStyle . ' background-color: #ea580c;']));
    // }

    // Ditolak
    $stats[] = Stat::make(new HtmlString('<span style="color: #ffffff !important; font-weight: 600;">UMKM Ditolak</span>'), (clone $query)->where('status', 'rejected')->count())
        ->description(new HtmlString('<span style="color: #ffffff !important; opacity: 0.9;">Tidak memenuhi syarat</span>'))
        ->descriptionIcon('heroicon-m-x-circle')
        ->color('danger')
        ->url($getFilter('rejected'))
        ->extraAttributes(array_merge($extraHtmlStyles, ['style' => $baseStyle . ' background-color: #be123c;']));
}

return $stats;
    }
}