<?php
namespace App\Services;

use App\Models\User;
use App\Mail\NewPengajuanMail;
use App\Mail\OprasionalMail;
use Filament\Notifications\Actions\Action;
use Illuminate\Support\Facades\Mail;
use Filament\Notifications\Notification;


class OprasionalNotificationService
{
    public static function sendNewPengajuanNotifications($pengajuan)
    {
        // Ambil semua admin
        $adminUsers = User::role(['super_admin', 'admin'])->get();
        
        // Buat URL untuk melihat detail pengajuan
        $pengajuanUrl = route('filament.admin.resources.permintaan.pengajuan-oprasional.index', [
            'record' => $pengajuan->id
        ]);
        
        foreach ($adminUsers as $admin) {
            // Kirim notifikasi Filament ke database
            Notification::make()
                ->title('Pengajuan Barang Baru')
                ->icon('heroicon-o-cube')
                ->iconColor('warning')
                ->body("📦 {$pengajuan->user->name} telah mengajukan " . count($pengajuan->detail_barang) . " item barang.")
                ->actions([
                    Action::make('view')
                        ->label('Lihat Pengajuan')
                        ->url($pengajuanUrl)
                        ->button(),
                ])
                ->sendToDatabase($admin);
        }
    }
}