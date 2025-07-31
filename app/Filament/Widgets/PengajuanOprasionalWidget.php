<?php

namespace App\Filament\Widgets;

use App\Models\Pengajuanoprasional;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PengajuanOprasionalWidget extends BaseWidget
{
     protected static ?int $sort = 4;

    public static function canView(): bool
    {
        $user = filament()->auth()->user();

        // Jika user tidak login, tidak bisa melihat
        if (!$user) {
            return false;
        }

        // User biasa bisa melihat widget pengajuan operasional
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
            Stat::make('Total Pengajuan Operasional', number_format($totalPengajuan, 0, ',', '.'))
                ->description('Semua pengajuan operasional')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('primary')
                ->chart($this->getPengajuanTrendChart())
                ->url(route(('filament.admin.resources.permintaan.pengajuan-operasional.index'))),

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
            return Pengajuanoprasional::whereIn('status', [
                'diajukan_ke_superadmin',
                'superadmin_approved',
                'superadmin_rejected',
                'pengajuan_dikirim_ke_pengadaan',
                'cancelled'
            ]);
        }

        if ($user->hasRole('admin')) {
            return Pengajuanoprasional::whereIn('status', [
                'pengajuan_terkirim',
                'pending_admin_review',
                'pengajuan_dikirim_ke_admin',
                'processing',
                'ready_pickup',
                'cancelled',
                'completed',
            ]);
        }

        if ($user->hasRole('direktur_keuangan')) {
            return Pengajuanoprasional::whereIn('status', [
                'pengajuan_dikirim_ke_direksi',
                'approved_by_direksi',
                'cancelled',
            ]);
        }

        if ($user->hasRole('keuangan')) {
            return Pengajuanoprasional::whereIn('status', [
                'pengajuan_dikirim_ke_keuangan',
                'pending_keuangan',
                'process_keuangan',
                'execute_keuangan',
                'cancelled',
            ]);
        }

        // Untuk user biasa, hanya tampilkan pengajuan mereka sendiri
        return Pengajuanoprasional::where('user_id', $user->id);
    }

    private function getTotalPengajuan(): int
    {
        return $this->getBaseQuery()->count();
    }

    private function getPengajuanPending(): int
    {
        return $this->getBaseQuery()->whereIn('status', [
            'pengajuan_terkirim',
            'pending_admin_review',
            'diajukan_ke_superadmin',
            'pengajuan_dikirim_ke_direksi',
            'pengajuan_dikirim_ke_keuangan',
            'pending_keuangan',
            'process_keuangan',
            'pengajuan_dikirim_ke_pengadaan',
            'processing'
        ])->count();
    }

    private function getPengajuanApproved(): int
    {
        return $this->getBaseQuery()->whereIn('status', [
            'superadmin_approved',
            'approved_by_direksi',
            'approved_at_direksi',
            'execute_keuangan',
            'executed_by_keuangan',
            'executed_at_keuangan',
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
