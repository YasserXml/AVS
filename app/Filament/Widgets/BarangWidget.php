<?php

namespace App\Filament\Widgets;

use App\Models\Barang;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class BarangWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';
    
    protected static bool $isLazy = false;
    
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        // Hitung statistik dasar
        $totalBarang = Barang::count();
        $totalStok = Barang::sum('jumlah_barang');
        $barangKosong = Barang::where('jumlah_barang', 0)->count();
        $barangMenuipis = Barang::where('jumlah_barang', '>', 0)
            ->where('jumlah_barang', '<', 10)->count();

        // Hitung persentase
        $persentaseKosong = $totalBarang > 0 ? round(($barangKosong / $totalBarang) * 100, 1) : 0;
        $persentaseMenuipis = $totalBarang > 0 ? round(($barangMenuipis / $totalBarang) * 100, 1) : 0;

        // Barang yang baru ditambahkan minggu ini
        $barangBaruMingguIni = Barang::where('created_at', '>=', now()->startOfWeek())->count();
        $barangBaruMingguLalu = Barang::whereBetween('created_at', [
            now()->subWeek()->startOfWeek(),
            now()->subWeek()->endOfWeek()
        ])->count();
        
        $trendBarangBaru = $barangBaruMingguLalu > 0 
            ? round((($barangBaruMingguIni - $barangBaruMingguLalu) / $barangBaruMingguLalu) * 100, 1)
            : ($barangBaruMingguIni > 0 ? 100 : 0);

        // Data untuk mini chart
        $miniChartData = [$barangKosong, $barangMenuipis, $totalBarang - $barangKosong - $barangMenuipis];

        return [
            // Card 1: Total Barang dengan trend
            Stat::make('Total Barang', number_format($totalBarang))
                ->description($barangBaruMingguIni . ' barang baru minggu ini')
                ->descriptionIcon($trendBarangBaru >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($trendBarangBaru >= 0 ? 'success' : 'danger')
                ->chart($miniChartData)
                ->icon('heroicon-o-cube'),

            // Card 2: Total Stok dengan alert status
            Stat::make('Total Stok', number_format($totalStok) . ' Unit')
                ->description($barangKosong > 0 ? $barangKosong . ' barang kosong' : ($barangMenuipis > 0 ? $barangMenuipis . ' barang menipis' : 'Semua stok aman'))
                ->descriptionIcon($barangKosong > 0 ? 'heroicon-m-exclamation-triangle' : ($barangMenuipis > 0 ? 'heroicon-m-clock' : 'heroicon-m-check-circle'))
                ->color($barangKosong > 0 ? 'danger' : ($barangMenuipis > 0 ? 'warning' : 'success'))
                ->chart([$totalStok - $barangKosong, $barangKosong])
                ->icon('heroicon-o-inbox-stack'),
        ];
    }

    protected function getColumns(): int
    {
        return 2; // 2 kolom sesuai permintaan
    }

    public static function canView(): bool
    {
        return true;
    }
    
    // Method untuk mendapatkan warna berdasarkan kondisi stok
    protected function getStockStatusColor(int $stock): string
    {
        return match (true) {
            $stock === 0 => 'danger',
            $stock < 10 => 'warning',
            default => 'success'
        };
    }

    // Method untuk mendapatkan icon berdasarkan kondisi stok
    protected function getStockStatusIcon(int $stock): string
    {
        return match (true) {
            $stock === 0 => 'heroicon-o-x-circle',
            $stock < 10 => 'heroicon-o-exclamation-triangle',
            default => 'heroicon-o-check-circle'
        };
    }
}
