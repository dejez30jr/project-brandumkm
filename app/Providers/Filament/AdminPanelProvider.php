<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\DesignProgressChartWidget;
use App\Filament\Widgets\SummaryPerKotaWidget;
use App\Filament\Widgets\SummaryStatsWidget;
use App\Filament\Widgets\UmkmChartWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider {
    public function panel( Panel $panel ): Panel {
        return $panel
        ->default()
        ->id( 'admin' )
        ->brandName('UMKM BRANDING')
        ->path( 'admin' )
       ->brandLogo(fn () => view('components.logo'))
        ->login()
        ->colors( [
            'primary' => Color::Amber,
        ] )
        ->discoverResources( in: app_path( 'Filament/Resources' ), for: 'App\\Filament\\Resources' )
        ->discoverPages( in: app_path( 'Filament/Pages' ), for: 'App\\Filament\\Pages' )
        ->pages( [
            Pages\Dashboard::class,
        ] )
//        ->navigationItems([
//     NavigationItem::make('Notifikasi')
//         ->url('/admin/notifikasis') // sesuaikan URL
//         ->icon('heroicon-o-bell')
//         ->badge(
//             badge: fn () => \App\Models\Notifikasi::where('user_id', auth()->id())
//                             ->where('is_read', false)
//                             ->count() ?: null,
//             color: 'danger'
//         )
//         ->sort(1),
// ])
        ->userMenuItems([
    MenuItem::make()
        ->label(function () {
            $roleMap = [
                'admin' => 'Admin',
                'design' => 'Designer',
                'client' => 'Client',
                'pic_lapangan' => 'PIC Lapangan',
            ];
            $role = auth()->user()?->role;
            return 'Role: ' . ($roleMap[$role] ?? ucfirst($role));
        })
        ->icon('heroicon-o-identification')
        ->url('#')
        ->sort(0),
])
        // Register widgets di sini
        ->discoverWidgets( in: app_path( 'Filament/Widgets' ), for: 'App\\Filament\\Widgets' )
        ->widgets( [
            // Widgets\AccountWidget::class,  // Widget default - bisa dihapus jika tidak mau
            // Widgets\FilamentInfoWidget::class,  // Widget default - bisa dihapus jika tidak mau
            SummaryStatsWidget::class,
            UmkmChartWidget::class,
            SummaryPerKotaWidget::class,
            DesignProgressChartWidget::class,
        ] )
        ->middleware( [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            AuthenticateSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            DisableBladeIconComponents::class,
            DispatchServingFilamentEvent::class,
        ] )
        ->authMiddleware( [
            Authenticate::class,
        ] );
    }
}