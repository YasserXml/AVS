<?php

namespace App\Filament\Widgets;

use App\Models\Barangkeluar;
use Filament\Widgets\ChartWidget;

class BarangKeluarWidget extends ChartWidget
{
    protected static ?string $heading = 'Chart Barang Keluar';

    protected static string $color = 'danger';
    
    protected static ?string $pollingInterval = '30s';
    
    protected static bool $isLazy = false;
    
    protected int | string | array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];
    
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        // Data barang keluar berdasarkan status
        $oprasionalData = Barangkeluar::where('status', 'oprasional_kantor')
            ->where('created_at', '>=', now()->subMonths(6))
            ->sum('jumlah_barang_keluar');
            
        $projectData = BarangKeluar::where('status', 'project')
            ->where('created_at', '>=', now()->subMonths(6))
            ->sum('jumlah_barang_keluar');

        $total = $oprasionalData + $projectData;
        
        // Jika tidak ada data, berikan nilai default
        if ($total == 0) {
            $oprasionalData = 1;
            $projectData = 1;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Barang Keluar',
                    'data' => [$oprasionalData, $projectData],
                    'backgroundColor' => [
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(249, 115, 22, 0.8)',
                    ],
                    'borderColor' => [
                        '#ef4444',
                        '#f97316',
                    ],
                    'borderWidth' => 2,
                    'hoverBackgroundColor' => [
                        'rgba(239, 68, 68, 0.9)',
                        'rgba(249, 115, 22, 0.9)',
                    ],
                    'hoverBorderColor' => [
                        '#dc2626',
                        '#ea580c',
                    ],
                    'hoverBorderWidth' => 3,
                ],
            ],
            'labels' => ['Operasional Kantor', 'Project'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'layout' => [
                'padding' => [
                    'top' => 10,
                    'right' => 10,
                    'bottom' => 10,
                    'left' => 10,
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                    'align' => 'center',
                    'labels' => [
                        'usePointStyle' => true,
                        'pointStyle' => 'circle',
                        'padding' => 15,
                        'font' => [
                            'size' => 11,
                        ],
                        'color' => '#6b7280',
                    ],
                ],
                'tooltip' => [
                    'mode' => 'point',
                    'intersect' => true,
                    'backgroundColor' => 'rgba(17, 24, 39, 0.95)',
                    'titleColor' => '#ffffff',
                    'bodyColor' => '#ffffff',
                    'borderColor' => '#ef4444',
                    'borderWidth' => 1,
                    'cornerRadius' => 8,
                    'displayColors' => true,
                    'titleFont' => [
                        'size' => 12,
                        'weight' => '600',
                    ],
                    'bodyFont' => [
                        'size' => 11,
                    ],
                    'padding' => 12,
                    'callbacks' => [
                        'label' => 'function(context) {
                            const label = context.label || "";
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return label + ": " + value + " (" + percentage + "%)";
                        }',
                    ],
                ],
            ],
            'cutout' => '60%',
            'interaction' => [
                'mode' => 'nearest',
                'intersect' => true,
            ],
            'animation' => [
                'animateRotate' => true,
                'animateScale' => true,
            ],
        ];
    }

    public static function canView(): bool
    {
        return true;
    }
}
