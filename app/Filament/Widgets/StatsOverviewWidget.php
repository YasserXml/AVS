<?php

namespace App\Filament\Widgets;

use App\Models\Barang;
use App\Models\Barangkeluar;
use App\Models\Barangmasuk;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalBarang = $this->getTotalBarang();
        $barangMasukBulanIni = $this->getBarangMasukBulanIni();
        $barangKeluarBulanIni = $this->getBarangKeluarBulanIni();
        $barangMasukBulanLalu = $this->getBarangMasukBulanLalu();
        $barangKeluarBulanLalu = $this->getBarangKeluarBulanLalu();
        $barangHampirHabis = $this->getBarangHampirHabis();

        // Hitung persentase perubahan
        $perubahanMasuk = $this->hitungPersentasePerubahan($barangMasukBulanIni, $barangMasukBulanLalu);
        $perubahanKeluar = $this->hitungPersentasePerubahan($barangKeluarBulanIni, $barangKeluarBulanLalu);

        return [
            Stat::make('Total Stok Barang', number_format($totalBarang, 0, ',', '.') . ' Unit')
                ->description($barangHampirHabis > 0 ?
                    $barangHampirHabis . ' barang hampir habis (< 10 unit)' :
                    'Semua barang stok aman')
                ->descriptionIcon($barangHampirHabis > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($barangHampirHabis > 0 ? 'warning' : 'success')
                ->chart($this->getStokTrendChart()),

            Stat::make('Barang Masuk', number_format($barangMasukBulanIni, 0, ',', '.') . ' Unit')
                ->description($this->getDescriptionWithTrend(
                    'bulan ' . now()->locale('id')->format('F Y'),
                    $perubahanMasuk
                ))
                ->descriptionIcon($perubahanMasuk >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($perubahanMasuk >= 0 ? 'success' : 'danger')
                ->chart($this->getBarangMasukChart()),

            Stat::make('Barang Keluar', number_format($barangKeluarBulanIni, 0, ',', '.') . ' Unit')
                ->description($this->getDescriptionWithTrend(
                    'bulan ' . now()->locale('id')->format('F Y'),
                    $perubahanKeluar
                ))
                ->descriptionIcon($perubahanKeluar >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($perubahanKeluar >= 0 ? 'info' : 'success')
                ->chart($this->getBarangKeluarChart()),
        ];
    }

    private function getTotalBarang(): int
    {
        return Barang::sum('jumlah_barang');
    }

    private function getBarangMasukBulanIni(): int
    {
        return Barangmasuk::whereMonth('tanggal_barang_masuk', now()->month)
            ->whereYear('tanggal_barang_masuk', now()->year)
            ->sum('jumlah_barang_masuk');
    }

    private function getBarangKeluarBulanIni(): int
    {
        return BarangKeluar::whereMonth('tanggal_keluar_barang', now()->month)
            ->whereYear('tanggal_keluar_barang', now()->year)
            ->sum('jumlah_barang_keluar');
    }

    private function getBarangMasukBulanLalu(): int
    {
        $bulanLalu = now()->subMonth();
        return Barangmasuk::whereMonth('tanggal_barang_masuk', $bulanLalu->month)
            ->whereYear('tanggal_barang_masuk', $bulanLalu->year)
            ->sum('jumlah_barang_masuk');
    }

    private function getBarangKeluarBulanLalu(): int
    {
        $bulanLalu = now()->subMonth();
        return Barangkeluar::whereMonth('tanggal_keluar_barang', $bulanLalu->month)
            ->whereYear('tanggal_keluar_barang', $bulanLalu->year)
            ->sum('jumlah_barang_keluar');
    }

    private function getBarangHampirHabis(): int
    {
        return Barang::where('jumlah_barang', '<', 10)->count();
    }

    private function hitungPersentasePerubahan(int $nilaiSekarang, int $nilaiBefore): float
    {
        if ($nilaiBefore == 0) {
            return $nilaiSekarang > 0 ? 100 : 0;
        }

        return (($nilaiSekarang - $nilaiBefore) / $nilaiBefore) * 100;
    }

    private function getDescriptionWithTrend(string $periode, float $persentase): string
    {
        $persentaseFormatted = number_format(abs($persentase), 1, ',', '.');
        $trendText = $persentase >= 0 ? 'naik' : 'turun';

        if ($persentase == 0) {
            return "Sama seperti bulan lalu • {$periode}";
        }

        return "{$trendText} {$persentaseFormatted}% dari bulan lalu • {$periode}";
    }

    private function getStokTrendChart(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $stok = Barang::sum('jumlah_barang'); // Simplified - you might want to track historical data
            $data[] = $stok;
        }
        return $data;
    }

    private function getBarangMasukChart(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $masuk = Barangmasuk::whereDate('tanggal_barang_masuk', $date)
                ->sum('jumlah_barang_masuk');
            $data[] = $masuk;
        }
        return $data;
    }

    private function getBarangKeluarChart(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $keluar = Barangkeluar::whereDate('tanggal_keluar_barang', $date)
                ->sum('jumlah_barang_keluar');
            $data[] = $keluar;
        }
        return $data;
    }
}
