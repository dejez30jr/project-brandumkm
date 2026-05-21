<?php

namespace App\Filament\Widgets;

use App\Models\Kota;
use App\Models\Umkm;
use Filament\Widgets\ChartWidget;

class UmkmChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Progres UMKM Per Kota';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $maxHeight = '400px';

   public static function canView(): bool
{
    // Mengambil user yang sedang login
    $user = auth()->user();

    // Memastikan user ada dan role-nya adalah 'admin' atau 'client'
    return $user && in_array($user->role, ['admin', 'client']);
}

    protected function getData(): array
    {
        // Ambil semua kota yang memiliki UMKM
        $kotas = Kota::whereHas('umkms')->get();
        
        $labels = [];
        $approved = [];
        $pending = [];
        $rejected = [];

        foreach ($kotas as $kota) {
            $labels[] = $kota->nama;
            
            $approved[] = Umkm::where('kota_id', $kota->id)
                ->where('status', 'approved')
                ->count();
            
            $pending[] = Umkm::where('kota_id', $kota->id)
                ->where('status', 'pending')
                ->count();
            
            $rejected[] = Umkm::where('kota_id', $kota->id)
                ->where('status', 'rejected')
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Approved',
                    'data' => $approved,
                    'backgroundColor' => '#10B981',
                    'borderColor' => '#10B981',
                ],
                [
                    'label' => 'Pending',
                    'data' => $pending,
                    'backgroundColor' => '#F59E0B',
                    'borderColor' => '#F59E0B',
                ],
                [
                    'label' => 'Rejected',
                    'data' => $rejected,
                    'backgroundColor' => '#EF4444',
                    'borderColor' => '#EF4444',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
        ];
    }
}