<?php

namespace App\Filament\Widgets;

use App\Models\Barangmasuk;
use Filament\Widgets\ChartWidget;

class BarangMasukWidget extends ChartWidget
{
    protected static ?string $heading = 'Chart Barang Masuk';
    
    protected static string $color = 'success';
    
    protected static ?string $pollingInterval = '30s';
    
    protected static bool $isLazy = false;
    
    protected int | string | array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];
    
    protected static ?string $maxHeight = '400px';

    protected function getData(): array
    {
        // Data barang masuk 12 bulan terakhir (kompatibel dengan PostgreSQL)
        $data = Barangmasuk::selectRaw('EXTRACT(YEAR FROM tanggal_barang_masuk) as year, EXTRACT(MONTH FROM tanggal_barang_masuk) as month, SUM(jumlah_barang_masuk) as total')
            ->where('tanggal_barang_masuk', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get(); // Tambahkan get() untuk mengeksekusi query

        // Buat array untuk 12 bulan terakhir
        $months = [];
        $totals = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M');
            
            // Cari data untuk bulan ini
            $monthData = $data->where('year', $date->year)
                             ->where('month', $date->month)
                             ->first();
            $totals[] = $monthData ? $monthData->total : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Barang Masuk',
                    'data' => $totals,
                    'backgroundColor' => [
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(34, 197, 94, 0.7)',
                        'rgba(34, 197, 94, 0.6)',
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(34, 197, 94, 0.7)',
                        'rgba(34, 197, 94, 0.6)',
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(34, 197, 94, 0.7)',
                        'rgba(34, 197, 94, 0.6)',
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(34, 197, 94, 0.7)',
                        'rgba(34, 197, 94, 0.6)',
                    ],
                    'borderColor' => '#22c55e',
                    'borderWidth' => 1,
                    'borderRadius' => 4,
                    'borderSkipped' => false,
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'aspectRatio' => 2,
            'layout' => [
                'padding' => [
                    'top' => 15,
                    'right' => 15,
                    'bottom' => 5,
                    'left' => 15,
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                    'backgroundColor' => 'rgba(17, 24, 39, 0.95)',
                    'titleColor' => '#ffffff',
                    'bodyColor' => '#ffffff',
                    'borderColor' => '#22c55e',
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
                ],
            ],
            'scales' => [
                'x' => [
                    'display' => true,
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
                        'padding' => 8,
                    ],
                ],
                'y' => [
                    'display' => true,
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
                        'stepSize' => 5,
                        'padding' => 8,
                        'maxTicksLimit' => 6,
                    ],
                ],
            ],
            'interaction' => [
                'mode' => 'nearest',
                'axis' => 'x',
                'intersect' => false,
            ],
        ];
    }

    public static function canView(): bool
    {
        return true;
    }
}