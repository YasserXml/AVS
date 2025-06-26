<?php

namespace App\Filament\Widgets;

use App\Models\Barangmasuk;
use Filament\Widgets\ChartWidget;

class BarangMasukChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Chart Barang Masuk';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = [
        'md' => 2,
        'lg' => 1,
    ];

    protected function getData(): array
    {
        $data = $this->getBarangMasukPerBulan();

        return [
            'datasets' => [
                [
                    'label' => 'Barang Masuk',
                    'data' => $data['data'],
                    'backgroundColor' => '#3b82f6',
                    'borderColor' => '#1d4ed8',
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    private function getBarangMasukPerBulan(): array
    {
        $months = [];
        $data = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M Y');

            $count = Barangmasuk::whereMonth('tanggal_barang_masuk', $date->month)
                ->whereYear('tanggal_barang_masuk', $date->year)
                ->sum('jumlah_barang_masuk');

            $data[] = $count;
        }

        return [
            'labels' => $months,
            'data' => $data,
        ];
    }
}
