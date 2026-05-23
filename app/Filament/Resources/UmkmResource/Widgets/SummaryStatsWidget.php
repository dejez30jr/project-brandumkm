<?php

namespace App\Filament\Resources\UmkmResource\Widgets;

use App\Filament\Resources\UmkmDesignResource;
use App\Filament\Resources\UmkmResource;
use App\Filament\Resources\UmkmTerbrandingResource;
use App\Models\Umkm;
use App\Models\UmkmDesign;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\HtmlString;

class SummaryStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected static ?string $pollingInterval = null;

    protected array|int|string $columns = [
        'default' => 2,
        'sm' => 2,
        'md' => 3,
        'lg' => 4,
    ];

    protected function getStats(): array
    {
        $stats = [];
        $user = auth()->user();
        $userRole = $user?->role;

        $baseStyle = 'box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.4), 0 4px 6px -4px rgba(0, 0, 0, 0.4); border-radius: 0.75rem; cursor: pointer; transition: transform 0.2s, filter 0.2s;';

        $extraHtmlStyles = [
            'class' => '[&_*]:text-white [&_*]:text-white/90 [&_p]:text-white [&_span]:text-white [&_svg]:!text-white'
        ];

        // Batch query UmkmDesign untuk menghindari N+1
        $designCounts = null;
        if (in_array($userRole, ['design', 'admin', 'client'])) {
            if ($userRole === 'design') {
                $designCounts = UmkmDesign::selectRaw("
                    SUM(CASE WHEN status = 'approved' AND designer_id = ? THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN status = 'revision_needed' AND designer_id = ? THEN 1 ELSE 0 END) as revision_needed,
                    SUM(CASE WHEN status = 'revised' AND designer_id = ? THEN 1 ELSE 0 END) as revised,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_all
                ", [$user->id, $user->id, $user->id])->first();
            } else {
                $designCounts = UmkmDesign::selectRaw("
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN status = 'revision_needed' THEN 1 ELSE 0 END) as revision_needed,
                    SUM(CASE WHEN status = 'revised' THEN 1 ELSE 0 END) as revised,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_all
                ")->first();
            }
        }

        if (in_array($userRole, ['team_pasang'])) {
            $queryPerluBranding = Umkm::where('status', Umkm::STATUS_DESIGN_APPROVED)
                ->where(function ($q) {
                    $q->whereNull('stiker_tampak_depan')
                      ->orWhereNull('stiker_tampak_kanan')
                      ->orWhereNull('stiker_tampak_kiri')
                      ->orWhereNull('foto_wide');
                });

            $stats[] = Stat::make(new HtmlString('<span style="color: #ffffff !important; font-weight: 600;">UMKM Perlu di-Branding</span>'), $queryPerluBranding->count())
                ->description(new HtmlString('<span style="color: #ffffff !important; opacity: 0.9;">Total antrean pasang stiker</span>'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->url('/admin/pemasangan-stiker')
                ->extraAttributes(array_merge($extraHtmlStyles, [
                    'style' => $baseStyle . ' background-color: #b45309;',
                    'onmouseover' => "this.style.transform='translateY(-4px)'; this.style.filter='brightness(1.15)';",
                    'onmouseout' => "this.style.transform='translateY(0)'; this.style.filter='brightness(1)';"
                ]));
        }

        if (in_array($userRole, ['admin', 'client', 'team_pasang'])) {
            $totalSudahBranding = Umkm::where('status', Umkm::STATUS_BRANDED)->count();

            $stats[] = Stat::make(new HtmlString('<span style="color: #ffffff !important; font-weight: 600;">Total UMKM Sudah di-Branding</span>'), $totalSudahBranding)
                ->description(new HtmlString('<span style="color: #ffffff !important; opacity: 0.9;">Gerobak selesai pasang stiker</span>'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->url(UmkmTerbrandingResource::getUrl('index'))
                ->extraAttributes(array_merge($extraHtmlStyles, [
                    'style' => $baseStyle . ' background-color: #047857;',
                    'onmouseover' => "this.style.transform='translateY(-4px)'; this.style.filter='brightness(1.15)';",
                    'onmouseout' => "this.style.transform='translateY(0)'; this.style.filter='brightness(1)';"
                ]));
        }

        if (in_array($userRole, ['pic_lapangan'])) {
            $stats[] = Stat::make(new HtmlString('<span style="color: #ffffff !important; font-weight: 600;">Create Data UMKM</span>'), 'Tambah')
                ->description(new HtmlString('<span style="color: #ffffff !important; opacity: 0.9;">Tambah data UMKM</span>'))
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('primary')
                ->url(UmkmResource::getUrl('create'))
                ->extraAttributes(array_merge($extraHtmlStyles, [
                    'style' => $baseStyle . ' background-color: #2563eb;',
                    'onmouseover' => "this.style.transform='translateY(-4px)'; this.style.filter='brightness(1.15)';",
                    'onmouseout' => "this.style.transform='translateY(0)'; this.style.filter='brightness(1)';"
                ]));
        }

        if (in_array($userRole, ['design'])) {
            $stats[] = Stat::make(new HtmlString('<span style="color: #ffffff !important; font-weight: 600;">Create Design UMKM</span>'), 'Tambah')
                ->description(new HtmlString('<span style="color: #ffffff !important; opacity: 0.9;">Tambah design UMKM</span>'))
                ->descriptionIcon('heroicon-m-paint-brush')
                ->color('success')
                ->url(UmkmDesignResource::getUrl('create'))
                ->extraAttributes(array_merge($extraHtmlStyles, [
                    'style' => $baseStyle . ' background-color: #16a34a;',
                    'onmouseover' => "this.style.transform='translateY(-4px)'; this.style.filter='brightness(1.15)';",
                    'onmouseout' => "this.style.transform='translateY(0)'; this.style.filter='brightness(1)';"
                ]));
        }

        if (in_array($userRole, ['client'])) {
            $stats[] = Stat::make(new HtmlString('<span style="color: #ffffff !important; font-weight: 600;">Design Perlu Review</span>'), $designCounts?->pending_all ?? 0)
                ->description(new HtmlString('<span style="color: #ffffff !important; opacity: 0.9;">Menunggu review</span>'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->url(UmkmDesignResource::getUrl('index', [
                    'tableFilters' => [
                        'status' => ['value' => 'pending'],
                    ],
                ]))
                ->extraAttributes(array_merge($extraHtmlStyles, [
                    'style' => $baseStyle . ' background-color: #ea580c;',
                    'onmouseover' => "this.style.transform='translateY(-4px)'; this.style.filter='brightness(1.15)';",
                    'onmouseout' => "this.style.transform='translateY(0)'; this.style.filter='brightness(1)';"
                ]));
        }

        if (in_array($userRole, ['design', 'admin'])) {
            if ($userRole === 'design') {
                $queryAntrean = Umkm::query()
                    ->whereIn('umkms.status', [Umkm::STATUS_APPROVED, Umkm::STATUS_REVISION_NEEDED])
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

            $stats[] = Stat::make(
                new HtmlString('<span style="color: #ffffff !important; font-weight: 600;">Design Approved</span>'),
                $designCounts?->approved ?? 0
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

            $stats[] = Stat::make(
                new HtmlString('<span style="color: #ffffff !important; font-weight: 600;">Design Perlu Revisi</span>'),
                $designCounts?->revision_needed ?? 0
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

            $stats[] = Stat::make(
                new HtmlString('<span style="color: #ffffff !important; font-weight: 600;">Design Sudah Direvisi</span>'),
                $designCounts?->revised ?? 0
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

        $query = Umkm::query();
        if ($userRole === 'pic_lapangan') {
            $query->whereHas('submittedBy', function ($q) use ($user) {
                $q->where('name', $user->name);
            });
        }

        $getFilter = function ($status) use ($user, $userRole) {
            $filters = [];
            if (!empty($status)) {
                $filters['status'] = ['value' => $status];
            }
            if ($userRole === 'pic_lapangan') {
                $filters['pic_filter'] = ['value' => $user->name];
            }
            return UmkmResource::getUrl('index', ['tableFilters' => $filters]);
        };

        if ($userRole === 'pic_lapangan') {
            $stats[] = Stat::make(new HtmlString('<span style="color: #ffffff !important; font-weight: 600;">UMKM Pending</span>'), (clone $query)->where('status', 'pending')->count())
                ->description(new HtmlString('<span style="color: #ffffff !important; opacity: 0.9;">Menunggu di review</span>'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->url($getFilter('pending'))
                ->extraAttributes(array_merge($extraHtmlStyles, ['style' => $baseStyle . ' background-color: #ea580c;']));
        }

        if (in_array($userRole, ['admin', 'pic_lapangan', 'client'])) {
            $stats[] = Stat::make(new HtmlString('<span style="color: #ffffff !important; font-weight: 600;">Total UMKM Masuk</span>'), (clone $query)->count())
                ->description(new HtmlString('<span style="color: #ffffff !important; opacity: 0.9;">' . ($userRole === 'pic_lapangan' ? 'Data Milik Saya' : 'Semua kota') . '</span>'))
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('primary')
                ->url($getFilter(''))
                ->extraAttributes(array_merge($extraHtmlStyles, ['style' => $baseStyle . ' background-color: #4f46e5;']));

            $stats[] = Stat::make(new HtmlString('<span style="color: #ffffff !important; font-weight: 600;">UMKM Approved</span>'), (clone $query)->where('status', 'approved')->count())
                ->description(new HtmlString('<span style="color: #ffffff !important; opacity: 0.9;">Menunggu didesain</span>'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->url($getFilter('approved'))
                ->extraAttributes(array_merge($extraHtmlStyles, ['style' => $baseStyle . ' background-color: #16a34a;']));

            if ($userRole !== 'pic_lapangan') {
                $designProcessCount = (clone $query)->whereIn('status', ['designing', 'design_review', 'revision_needed'])->count();
                $stats[] = Stat::make(new HtmlString('<span style="color: #ffffff !important; font-weight: 600;">Proses Design</span>'), $designProcessCount)
                    ->description(new HtmlString('<span style="color: #ffffff !important; opacity: 0.9;">Sedang didesain/review</span>'))
                    ->descriptionIcon('heroicon-m-paint-brush')
                    ->color('info')
                    ->url($getFilter('designing'))
                    ->extraAttributes(array_merge($extraHtmlStyles, ['style' => $baseStyle . ' background-color: #0891b2;']));

                $stats[] = Stat::make(new HtmlString('<span style="color: #ffffff !important; font-weight: 600;">Siap Pasang Stiker</span>'), (clone $query)->where('status', 'design_approved')->count())
                    ->description(new HtmlString('<span style="color: #ffffff !important; opacity: 0.9;">Design disetujui</span>'))
                    ->descriptionIcon('heroicon-m-scissors')
                    ->color('success')
                    ->url($getFilter('design_approved'))
                    ->extraAttributes(array_merge($extraHtmlStyles, ['style' => $baseStyle . ' background-color: #059669;']));
            }

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
