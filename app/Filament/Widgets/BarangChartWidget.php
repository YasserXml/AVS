<?php

namespace App\Filament\Widgets;

use App\Models\Barang;
use Filament\Widgets\ChartWidget;

class BarangChartWidget extends ChartWidget
{
    protected static ?string $heading = 'chart';
    
    protected static string $color = 'info';
    
    protected static ?string $pollingInterval = '30s';
    
    protected static bool $isLazy = false;
    
    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 3,
    ];
    
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        // Data trend penambahan barang 30 hari terakhir
        $data = Barang::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Buat array untuk 30 hari terakhir dengan nilai 0 sebagai default
        $dates = [];
        $counts = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dates[] = now()->subDays($i)->format('d/m');
            
            // Cari data untuk tanggal ini
            $dayData = $data->where('date', $date)->first();
            $counts[] = $dayData ? $dayData->count : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Barang Ditambahkan',
                    'data' => $counts,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.05)',
                    'fill' => true,
                    'tension' => 0.4,
                    'pointBackgroundColor' => '#3b82f6',
                    'pointBorderColor' => '#ffffff',
                    'pointBorderWidth' => 2,
                    'pointRadius' => 3,
                    'pointHoverRadius' => 5,
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $dates,
        ];
    }

    protected function getType(): string
    {
        return 'line';
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
                    'bottom' => 0,
                    'left' => 10,
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                    'align' => 'start',
                    'labels' => [
                        'usePointStyle' => true,
                        'pointStyle' => 'circle',
                        'padding' => 15,
                        'font' => [
                            'size' => 11,
                            'family' => 'Inter, sans-serif',
                        ],
                        'color' => '#6b7280',
                    ],
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                    'backgroundColor' => 'rgba(17, 24, 39, 0.95)',
                    'titleColor' => '#ffffff',
                    'bodyColor' => '#ffffff',
                    'borderColor' => '#3b82f6',
                    'borderWidth' => 1,
                    'cornerRadius' => 8,
                    'displayColors' => false,
                    'titleFont' => [
                        'size' => 12,
                        'weight' => '600',
                    ],
                    'bodyFont' => [
                        'size' => 11,
                    ],
                    'padding' => 12,
                ],
            ],
            'scales' => [
                'x' => [
                    'display' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Tanggal',
                        'color' => '#6b7280',
                        'font' => [
                            'size' => 12,
                            'weight' => 'bold',
                        ],
                    ],
                    'grid' => [
                        'display' => false,
                    ],
                    'border' => [
                        'display' => false,
                    ],
                    'ticks' => [
                        'color' => '#9ca3af',
                        'font' => [
                            'size' => 10,
                        ],
                        'maxTicksLimit' => 10,
                        'padding' => 8,
                    ],
                ],
                'y' => [
                    'display' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Jumlah Barang',
                        'color' => '#6b7280',
                        'font' => [
                            'size' => 12,
                            'weight' => 'bold',
                        ],
                    ],
                    'beginAtZero' => true,
                    'grid' => [
                        'color' => 'rgba(156, 163, 175, 0.1)',
                        'lineWidth' => 1,
                        'drawBorder' => false,
                    ],
                    'border' => [
                        'display' => false,
                    ],
                    'ticks' => [
                        'color' => '#9ca3af',
                        'font' => [
                            'size' => 10,
                        ],
                        'stepSize' => 1,
                        'padding' => 8,
                    ],
                ],
            ],
            'interaction' => [
                'mode' => 'nearest',
                'axis' => 'x',
                'intersect' => false,
            ],
            'elements' => [
                'point' => [
                    'hoverBackgroundColor' => '#1d4ed8',
                    'hoverBorderColor' => '#ffffff',
                    'hoverBorderWidth' => 2,
                ],
                'line' => [
                    'borderWidth' => 2,
                ],
            ],
        ];
    }

    // Method untuk styling kustom (opsional)
    public function getColumnSpan(): int | string | array
    {
        return [
            'md' => 2,
            'xl' => 3,
        ];
    }

}
