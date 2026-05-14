<?php

namespace App\Filament\Widgets;

use App\Models\UmkmDesign;
use Filament\Widgets\ChartWidget;

class DesignProgressChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Status Design UMKM';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $approved = UmkmDesign::where('status', 'approved')->count();
        $pending = UmkmDesign::where('status', 'pending')->count();
        $revision = UmkmDesign::where('status', 'revision_needed')->count();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Design',
                    'data' => [$approved, $pending, $revision],
                    'backgroundColor' => [
                        '#10B981', // green - approved
                        '#F59E0B', // yellow - pending
                        '#EF4444', // red - revision
                    ],
                ],
            ],
            'labels' => ['Approved', 'Pending', 'Perlu Revisi'],
        ];
    }

    protected function getType(): string
    {
        return 'bar'; // atau 'doughnut' / 'pie' untuk pie chart
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
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