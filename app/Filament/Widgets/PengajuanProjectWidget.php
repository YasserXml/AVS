<?php

namespace App\Filament\Widgets;

use App\Models\Pengajuanproject;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PengajuanProjectWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    public static function canView(): bool
    {
        $user = filament()->auth()->user();

        // Jika user tidak login, tidak bisa melihat
        if (!$user) {
            return false;
        }

        // User biasa bisa melihat widget pengajuan project
        return true;
    }

    protected function getStats(): array
    {
        $totalPengajuan = $this->getTotalPengajuan();
        $pengajuanPending = $this->getPengajuanPending();
        $pengajuanApproved = $this->getPengajuanApproved();
        $pengajuanBulanIni = $this->getPengajuanBulanIni();
        $pengajuanBulanLalu = $this->getPengajuanBulanLalu();

        // Hitung persentase perubahan
        $perubahanBulanIni = $this->hitungPersentasePerubahan($pengajuanBulanIni, $pengajuanBulanLalu);

        return [
            Stat::make('Total Pengajuan Barang Project', number_format($totalPengajuan, 0, ',', '.'))
                ->description('Semua pengajuan project')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('primary')
                ->chart($this->getPengajuanTrendChart()),

            Stat::make('Menunggu Review', number_format($pengajuanPending, 0, ',', '.'))
                ->description('Pengajuan yang menunggu persetujuan')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Disetujui', number_format($pengajuanApproved, 0, ',', '.'))
                ->description('Pengajuan yang sudah disetujui')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Bulan Ini', number_format($pengajuanBulanIni, 0, ',', '.'))
                ->description($this->getDescriptionWithTrend(
                    'bulan ' . now()->locale('id')->format('F Y'),
                    $perubahanBulanIni
                ))
                ->descriptionIcon($perubahanBulanIni >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($perubahanBulanIni >= 0 ? 'success' : 'danger'),
        ];
    }

    /**
     * Mendapatkan query base untuk user yang sedang login
     */
    private function getBaseQuery()
    {
        $user = filament()->auth()->user();
        
        // Jika user memiliki role khusus, tampilkan sesuai dengan role mereka
        if ($user->hasRole('purchasing')) {
            return Pengajuanproject::whereIn('status', [
                'disetujui_pm_dikirim_ke_pengadaan',
                'disetujui_pengadaan',
                'ditolak_pengadaan',
                'pengajuan_dikirim_ke_pengadaan_final',
                'cancelled'
            ]);
        }

        if ($user->hasRole('admin')) {
            return Pengajuanproject::whereIn('status', [
                'pengajuan_terkirim',
                'pending_admin_review',
                'pengajuan_dikirim_ke_admin',
                'processing',
                'ready_pickup',
                'completed',
                'cancelled'
            ]);
        }

        if ($user->hasRole('direktur_keuangan')) {
            return Pengajuanproject::whereIn('status', [
                'pengajuan_dikirim_ke_direksi',
                'approved_by_direksi',
                'reject_direksi',
                'cancelled'
            ]);
        }

        if ($user->hasRole('keuangan')) {
            return Pengajuanproject::whereIn('status', [
                'pengajuan_dikirim_ke_keuangan',
                'pending_keuangan',
                'process_keuangan',
                'execute_keuangan',
                'cancelled'
            ]);
        }

        // Untuk user biasa atau PM, hanya tampilkan pengajuan mereka sendiri
        return Pengajuanproject::where(function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->orWhere(function ($subQuery) use ($user) {
                    // Jika user adalah PM, bisa melihat pengajuan project yang mereka kelola
                    $subQuery->whereHas('nameproject', function ($projectQuery) use ($user) {
                        $projectQuery->where('user_id', $user->id);
                    })->whereIn('status', [
                        'pengajuan_terkirim',
                        'pending_pm_review',
                        'disetujui_pm_dikirim_ke_pengadaan',
                        'ditolak_pm',
                    ]);
                });
        });
    }

    private function getTotalPengajuan(): int
    {
        return $this->getBaseQuery()->count();
    }

    private function getPengajuanPending(): int
    {
        return $this->getBaseQuery()->whereIn('status', [
            'pengajuan_terkirim',
            'pending_pm_review',
            'disetujui_pm_dikirim_ke_pengadaan',
            'pengajuan_dikirim_ke_direksi',
            'pengajuan_dikirim_ke_keuangan',
            'pending_keuangan',
            'process_keuangan',
            'pengajuan_dikirim_ke_pengadaan_final',
            'pengajuan_dikirim_ke_admin',
            'processing'
        ])->count();
    }

    private function getPengajuanApproved(): int
    {
        return $this->getBaseQuery()->whereIn('status', [
            'approved_by_direksi',
            'execute_keuangan',
            'disetujui_pengadaan',
            'ready_pickup',
            'completed'
        ])->count();
    }

    private function getPengajuanBulanIni(): int
    {
        return $this->getBaseQuery()
            ->whereMonth('tanggal_pengajuan', now()->month)
            ->whereYear('tanggal_pengajuan', now()->year)
            ->count();
    }

    private function getPengajuanBulanLalu(): int
    {
        $bulanLalu = now()->subMonth();
        return $this->getBaseQuery()
            ->whereMonth('tanggal_pengajuan', $bulanLalu->month)
            ->whereYear('tanggal_pengajuan', $bulanLalu->year)
            ->count();
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

    private function getPengajuanTrendChart(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = $this->getBaseQuery()
                ->whereDate('tanggal_pengajuan', $date)
                ->count();
            $data[] = $count;
        }
        return $data;
    }
}
