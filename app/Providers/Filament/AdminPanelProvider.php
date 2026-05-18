<?php

namespace App\Providers\Filament;

use App\Filament\Resources\NotifikasiResource;
use App\Filament\Widgets\DesignProgressChartWidget;
use App\Filament\Widgets\SummaryPerKotaWidget;
use App\Filament\Widgets\SummaryStatsWidget;
use App\Filament\Widgets\UmkmChartWidget;
use App\Filament\Widgets\UmkmPerluDesignTableWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentView;
use Filament\Support\View\PanelsRenderHooks;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\HtmlString;
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
        ->login(\App\Filament\Pages\Auth\CustomLogin::class)
        ->profile()
        ->colors( [
            'primary' => Color::Amber,
        ] )
        ->discoverResources( in: app_path( 'Filament/Resources' ), for: 'App\\Filament\\Resources' )
        ->discoverPages( in: app_path( 'Filament/Pages' ), for: 'App\\Filament\\Pages' )
        ->pages( [
            Pages\Dashboard::class,
        ] )
     ->renderHook(
    'panels::user-menu.before',
    function (): string {
        $user = auth()->user();
        $hasNewNotification = false;

        if ($user) {
            // SINKRONISASI LOGIKA MULTI-USER:
            // Dot biru akan menyala jika ada notifikasi yang belum dibaca (belum tercatat di tabel notifikasi_user) oleh user ini
            $hasNewNotification = \App\Models\Notifikasi::whereDoesntHave('users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->exists();
        }

        // HAPUS tanda komentar (//) di bawah ini jika ingin MEMAKSA dot muncul saat tes tampilan:
        // $hasNewNotification = true;

        return Blade::render('
            <div class="flex items-center justify-center mr-2 h-9 w-9">
                <a href="{{ \App\Filament\Resources\NotifikasiResource::getUrl() }}" 
                   class="flex items-center justify-center w-full h-full rounded-full text-gray-500 hover:text-gray-700 hover:bg-gray-500/10 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-400/10 transition focus:outline-none"
                   title="Notifikasi">
                    
                    <div class="relative p-1">
                        <x-filament::icon
                            icon="heroicon-o-bell"
                            class="h-6 w-6"
                        />

                        @if($hasNewNotification)
                            <span style="position: absolute; top: -2px; right: -2px; display: flex; height: 9px; width: 9px;">
                                <span style="position: absolute; display: inline-flex; height: 100%; width: 100%; border-radius: 9999px; background-color: #3b82f6; opacity: 0.75; animation: ping 1s cubic-bezier(0, 0, 0.2, 1) infinite;"></span>
                                <span style="position: relative; display: inline-flex; border-radius: 9999px; height: 9px; width: 9px; background-color: #2563eb; box-shadow: 0 0 0 2px #ffffff;"></span>
                            </span>
                        @endif
                    </div>

                </a>
            </div>
        ', ['hasNewNotification' => $hasNewNotification]);
    }
)
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
            UmkmPerluDesignTableWidget::class,
            UmkmChartWidget::class,
            SummaryPerKotaWidget::class,
            // DesignProgressChartWidget::class,
        ] )
        ->bootUsing(function () {
    FilamentView::registerRenderHook(
        'panels::head.end',
        fn () => new HtmlString("
            <style>
                /* Background Menu Aktif */
                html:not(.dark) .fi-sidebar-item-active > a {
                    background-color: #f97316 !important; 
                }
                /* Teks Menu */
                html:not(.dark) .fi-sidebar-item-active > a span {
                    color: #ffffff !important;
                }
                /* Ikon Menu */
                html:not(.dark) .fi-sidebar-item-active > a svg {
                    color: #ffffff !important;
                }
                
                /* ================================================= */
                /* CUSTOM BADGE ANGKA SAAT AKTIF                     */
                /* ================================================= */
                /* Mengubah background wadah badge menjadi putih */
                html:not(.dark) .fi-sidebar-item-active > a .fi-badge {
                    background-color: #ffffff !important;
                }
                /* Mengubah angka di dalam badge menjadi orange */
                html:not(.dark) .fi-sidebar-item-active > a .fi-badge span {
                    color: #f97316 !important;
                }
            </style>
        ")
    );
})
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