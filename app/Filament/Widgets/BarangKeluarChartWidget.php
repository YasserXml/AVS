<?php

namespace App\Filament\Widgets;

use App\Models\Barangkeluar;
use Filament\Widgets\ChartWidget;

class BarangKeluarChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Chart Barang Keluar';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = [
        'md' => 2,
        'lg' => 1,
    ];

    protected function getData(): array
    {
        $data = $this->getBarangKeluarPerBulan();

        return [
            'datasets' => [
                [
                    'label' => 'Barang Keluar',
                    'data' => $data['data'],
                    'backgroundColor' => '#10b981',
                    'borderColor' => '#059669',
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    private function getBarangKeluarPerBulan(): array
    {
        $months = [];
        $data = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M Y');

            $count = Barangkeluar::whereMonth('tanggal_keluar_barang', $date->month)
                ->whereYear('tanggal_keluar_barang', $date->year)
                ->sum('jumlah_barang_keluar');

            $data[] = $count;
        }

        return [
            'labels' => $months,
            'data' => $data,
        ];
    }
}
