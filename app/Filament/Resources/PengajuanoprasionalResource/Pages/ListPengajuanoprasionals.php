<?php

namespace App\Filament\Resources\PengajuanoprasionalResource\Pages;

use App\Filament\Resources\PengajuanoprasionalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class ListPengajuanoprasionals extends ListRecords
{
    protected static string $resource = PengajuanoprasionalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
             ->label('Buat Pengajuan')
             ->icon('heroicon-o-plus')
             ->iconPosition('before'),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return new HtmlString('
            <div class="flex items-center gap-2 ">
                <span class="text-xl font-bold">Pengajuan Barang Operasional</span>
            </div>
        ');
    }

    public function getStatusIcon($status)
    {
        $icons = [
            'pengajuan_terkirim' => '<x-heroicon-o-paper-airplane class="w-5 h-5 text-blue-500" />',
            'pending_admin_review' => '<x-heroicon-o-eye class="w-5 h-5 text-yellow-500" />',
            'diajukan_ke_superadmin' => '<x-heroicon-o-arrow-up-tray class="w-5 h-5 text-orange-500" />',
            'superadmin_approved' => '<x-heroicon-o-check-circle class="w-5 h-5 text-green-500" />',
            'superadmin_rejected' => '<x-heroicon-o-x-circle class="w-5 h-5 text-red-500" />',
            'processing' => '<x-heroicon-o-cog-6-tooth class="w-5 h-5 text-purple-500" />',
            'ready_pickup' => '<x-heroicon-o-inbox-arrow-down class="w-5 h-5 text-teal-500" />',
            'completed' => '<x-heroicon-o-check-badge class="w-5 h-5 text-green-600" />',
        ];
        
        return $icons[$status] ?? '<x-heroicon-o-information-circle class="w-5 h-5 text-gray-500" />';
    }

    public function getStatusLabel($status)
    {
        $labels = [
            'pengajuan_terkirim' => 'Pengajuan Terkirim',
            'pending_admin_review' => 'Menunggu Review Admin',
            'diajukan_ke_superadmin' => 'Dikirim ke Tim Pengadaan',
            'superadmin_approved' => 'Disetujui Tim Pengadaan',
            'superadmin_rejected' => 'Ditolak Tim Pengadaan',
            'processing' => 'Sedang Diproses',
            'ready_pickup' => 'Siap Diambil',
            'completed' => 'Selesai',
        ];
        
        return $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }

    public function getStatusBadgeColor($status)
    {
        $colors = [
            'pengajuan_terkirim' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
            'pending_admin_review' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
            'diajukan_ke_superadmin' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
            'superadmin_approved' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            'superadmin_rejected' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
            'processing' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
            'ready_pickup' => 'bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-200',
            'completed' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        ];
        
        return $colors[$status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
    }
}
