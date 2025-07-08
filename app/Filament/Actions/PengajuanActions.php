<?php

namespace App\Filament\Actions;

use App\Mail\PengajuanApprovedMail;
use App\Mail\PengajuanReadyPickupMail;
use App\Mail\PengajuanRejectMail;
use App\Mail\PengajuanSentToSuperAdminMail;
use App\Models\User;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Tables;
use Illuminate\Support\Facades\Mail;

class PengajuanActions
{
    public static function mulaiReview()
    {
        return Tables\Actions\Action::make('mulai_review')
            ->label('Mulai Review')
            ->icon('heroicon-o-play')
            ->color('primary')
            ->visible(
                fn($record) =>
                $record->status === 'pengajuan_terkirim' &&
                    filament()->auth()->user()->hasRole('admin')
            )
            ->requiresConfirmation()
            ->modalHeading('Mulai Review Pengajuan')
            ->modalDescription('Apakah Anda yakin ingin memulai review pengajuan ini?')
            ->action(function ($record) {
                $record->update([
                    'status' => 'pending_admin_review',
                ]);

                $record->addStatusHistory(
                    'pending_admin_review',
                    filament()->auth()->id(),
                    'Review pengajuan dimulai oleh admin'
                );

                // Kirim notifikasi database saja
                self::sendDatabaseNotification($record, 'review_started');

                Notification::make()
                    ->title('Berhasil')
                    ->body('Review pengajuan telah dimulai.')
                    ->success()
                    ->send();
            });
    }

    public static function kirimKeSuperAdmin()
    {
        return Tables\Actions\Action::make('kirim_ke_superadmin')
            ->label('Kirim ke Pengadaan')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('warning')
            ->visible(
                fn($record) =>
                $record->status === 'pending_admin_review' &&
                    filament()->auth()->user()->hasRole('admin')
            )
            ->requiresConfirmation()
            ->modalHeading('Kirim ke Pengadaan')
            ->modalDescription('Apakah Anda yakin ingin mengirim pengajuan ini ke tim pengadaan?')
            ->action(function ($record) {
                $record->update([
                    'status' => 'diajukan_ke_superadmin',
                ]);

                $record->addStatusHistory(
                    'diajukan_ke_superadmin',
                    filament()->auth()->id(),
                    'Pengajuan dikirim ke tim pengadaan'
                );

                // Kirim notifikasi database dan email
                self::sendNotificationWithEmail($record, 'sent_to_superadmin');

                Notification::make()
                    ->title('Berhasil')
                    ->body('Pengajuan berhasil dikirim ke tim pengadaan.')
                    ->success()
                    ->send();
            });
    }

    public static function approveSuperAdmin()
    {
        return Tables\Actions\Action::make('approve_superadmin')
            ->label('Setujui')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->visible(
                fn($record) =>
                $record->status === 'diajukan_ke_superadmin' &&
                    filament()->auth()->user()->hasRole('super_admin')
            )
            ->form([
                Forms\Components\Textarea::make('catatan')
                    ->label('Catatan (Opsional)')
                    ->rows(3)
                    ->placeholder('Tambahkan catatan jika diperlukan...'),
            ])
            ->requiresConfirmation()
            ->modalHeading('Setujui Pengajuan')
            ->modalDescription('Apakah Anda yakin ingin menyetujui pengajuan ini?')
            ->action(function ($record, array $data) {
                $record->update([
                    'status' => 'superadmin_approved',
                    'approved_by' => filament()->auth()->id(),
                    'approved_at' => now(),
                ]);

                $record->addStatusHistory(
                    'superadmin_approved',
                    filament()->auth()->id(),
                    $data['catatan'] ?? 'Pengajuan disetujui oleh tim pengadaan'
                );

                // Kirim notifikasi database dan email
                self::sendNotificationWithEmail($record, 'approved', $data['catatan'] ?? null);

                Notification::make()
                    ->title('Berhasil')
                    ->body('Pengajuan berhasil disetujui.')
                    ->success()
                    ->send();
            });
    }

    public static function rejectSuperAdmin()
    {
        return Tables\Actions\Action::make('reject_superadmin')
            ->label('Tolak')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->visible(
                fn($record) =>
                $record->status === 'diajukan_ke_superadmin' &&
                    filament()->auth()->user()->hasRole('super_admin')
            )
            ->form([
                Forms\Components\Textarea::make('alasan')
                    ->label('Alasan Penolakan')
                    ->required()
                    ->rows(3)
                    ->placeholder('Jelaskan alasan penolakan...'),
            ])
            ->requiresConfirmation()
            ->modalHeading('Tolak Pengajuan')
            ->modalDescription('Apakah Anda yakin ingin menolak pengajuan ini?')
            ->action(function ($record, array $data) {
                $record->update([
                    'status' => 'superadmin_rejected',
                    'rejected_by' => filament()->auth()->id(),
                    'rejected_at' => now(),
                    'reject_reason' => $data['alasan'],
                    'rejected_by_role' => 'superadmin',
                ]);

                $record->addStatusHistory(
                    'superadmin_rejected',
                    filament()->auth()->id(),
                    'Pengajuan ditolak: ' . $data['alasan']
                );

                // Kirim notifikasi database dan email
                self::sendNotificationWithEmail($record, 'rejected', $data['alasan']);

                Notification::make()
                    ->title('Pengajuan Ditolak')
                    ->body('Pengajuan berhasil ditolak.')
                    ->warning()
                    ->send();
            });
    }

    public static function mulaiProses()
    {
        return Tables\Actions\Action::make('mulai_proses')
            ->label('Mulai Proses')
            ->icon('heroicon-o-play')
            ->color('warning')
            ->visible(
                fn($record) =>
                $record->status === 'superadmin_approved' &&
                    filament()->auth()->user()->hasRole('admin')
            )
            ->requiresConfirmation()
            ->action(function ($record) {
                $record->update(['status' => 'processing']);

                $record->addStatusHistory(
                    'processing',
                    filament()->auth()->id(),
                    'Proses pengajuan dimulai'
                );

                // Kirim notifikasi database saja
                self::sendDatabaseNotification($record, 'processing_started');

                Notification::make()
                    ->title('Berhasil')
                    ->body('Proses pengajuan telah dimulai.')
                    ->success()
                    ->send();
            });
    }

    public static function siapDiambil()
    {
        return Tables\Actions\Action::make('siap_diambil')
            ->label('Siap Diambil')
            ->icon('heroicon-o-inbox-arrow-down')
            ->color('info')
            ->visible(
                fn($record) =>
                $record->status === 'processing' &&
                    filament()->auth()->user()->hasRole('admin')
            )
            ->requiresConfirmation()
            ->action(function ($record) {
                $record->update(['status' => 'ready_pickup']);

                $record->addStatusHistory(
                    'ready_pickup',
                    filament()->auth()->id(),
                    'Barang siap diambil'
                );

                // Kirim notifikasi database dan email
                self::sendNotificationWithEmail($record, 'ready_pickup');

                Notification::make()
                    ->title('Berhasil')
                    ->body('Status berhasil diubah menjadi siap diambil.')
                    ->success()
                    ->send();
            });
    }

    public static function selesai()
    {
        return Tables\Actions\Action::make('selesai')
            ->label('Selesai')
            ->icon('heroicon-o-check-badge')
            ->color('success')
            ->visible(
                fn($record) =>
                $record->status === 'ready_pickup' &&
                    filament()->auth()->user()->hasRole('admin')
            )
            ->form([
                Forms\Components\TextInput::make('received_by')
                    ->label('Diterima Oleh')
                    ->required()
                    ->helperText('Masukkan nama penerima barang'),
                Forms\Components\Textarea::make('catatan')
                    ->label('Catatan Penerimaan')
                    ->rows(3)
                    ->placeholder('Tambahkan catatan penerimaan...'),
            ])
            ->requiresConfirmation()
            ->action(function ($record, array $data) {
                $record->update([
                    'status' => 'completed',
                    'received_by' => $data['received_by'],
                ]);

                $record->addStatusHistory(
                    'completed',
                    filament()->auth()->id(),
                    $data['catatan'] ?? 'Pengajuan selesai dan barang telah diserahkan'
                );

                // Kirim notifikasi database saja
                self::sendDatabaseNotification($record, 'completed', $data['received_by']);

                Notification::make()
                    ->title('Berhasil')
                    ->body('Pengajuan berhasil diselesaikan.')
                    ->success()
                    ->send();
            });
    }

    /**
     * Kirim notifikasi database dan email
     */
    private static function sendNotificationWithEmail($record, $status, $additionalData = null)
    {
        // Ambil user yang mengajukan
        $pengaju = $record->user;

        // Ambil semua admin dan super admin untuk notifikasi database
        $adminUsers = User::whereHas(
            'roles',
            fn($query) =>
            $query->whereIn('name', ['super_admin', 'admin'])
        )->get();

        // Kirim email berdasarkan status
        if ($pengaju && $pengaju->email) {
            switch ($status) {
                case 'sent_to_superadmin':
                    Mail::to($pengaju->email)->queue(new PengajuanSentToSuperAdminMail($record));
                    break;
                case 'approved':
                    Mail::to($pengaju->email)->queue(new PengajuanApprovedMail($record, 'Tim Pengadaan', $additionalData));
                    break;
                case 'rejected':
                    Mail::to($pengaju->email)->queue(new PengajuanRejectedMail($record, 'Tim Pengadaan', $additionalData));
                    break;
                case 'ready_pickup':
                    Mail::to($pengaju->email)->queue(new PengajuanReadyPickupMail($record));
                    break;
            }
        }

        // Kirim notifikasi database
        self::sendDatabaseNotification($record, $status, $additionalData);
    }

    /**
     * Kirim notifikasi database saja
     */
    private static function sendDatabaseNotification($record, $status, $additionalData = null)
    {
        // Ambil user yang mengajukan
        $pengaju = $record->user;

        // Ambil semua admin dan super admin untuk notifikasi database
        $adminUsers = User::whereHas(
            'roles',
            fn($query) =>
            $query->whereIn('name', ['super_admin', 'admin'])
        )->get();

        // Konfigurasi notifikasi berdasarkan status
        $notificationConfig = self::getNotificationConfig($status, $record, $additionalData);

        // Kirim notifikasi database ke pengaju
        if ($pengaju) {
            Notification::make()
                ->title($notificationConfig['title'])
                ->icon($notificationConfig['icon'])
                ->iconColor($notificationConfig['iconColor'])
                ->body($notificationConfig['body'])
                ->sendToDatabase($pengaju);
        }

        // Kirim notifikasi database ke semua admin
        foreach ($adminUsers as $admin) {
            Notification::make()
                ->title($notificationConfig['title'])
                ->icon($notificationConfig['icon'])
                ->iconColor($notificationConfig['iconColor'])
                ->body($notificationConfig['body'])
                ->sendToDatabase($admin);
        }
    }

    /**
     * Dapatkan konfigurasi notifikasi berdasarkan status
     */
    private static function getNotificationConfig($status, $record, $additionalData = null)
    {
        switch ($status) {
            case 'pending_admin_review':
                return [
                    'title' => 'Review Dimulai',
                    'icon' => 'heroicon-o-play',
                    'iconColor' => 'primary',
                    'body' => "🔍 Review pengajuan #{$record->id} telah dimulai oleh admin."
                ];

            case 'diajukan_ke_superadmin':
                return [
                    'title' => 'Dikirim ke Tim Pengadaan',
                    'icon' => 'heroicon-o-arrow-up-tray',
                    'iconColor' => 'warning',
                    'body' => "📤 Pengajuan #{$record->id} telah dikirim ke tim pengadaan untuk persetujuan."
                ];

            case 'superadmin_approved':
                return [
                    'title' => 'Pengajuan Disetujui',
                    'icon' => 'heroicon-o-check-circle',
                    'iconColor' => 'success',
                    'body' => "✅ Pengajuan #{$record->id} telah disetujui oleh Tim Pengadaan." .
                        ($additionalData ? " Catatan: {$additionalData}" : '')
                ];

            case 'superadmin_rejected':  
                return [
                    'title' => 'Pengajuan Ditolak',
                    'icon' => 'heroicon-o-x-circle',
                    'iconColor' => 'danger',
                    'body' => "❌ Pengajuan #{$record->id} ditolak oleh Tim Pengadaan. Alasan: {$additionalData}"
                ];

            case 'processing_started':
                return [
                    'title' => 'Proses Dimulai',
                    'icon' => 'heroicon-o-play',
                    'iconColor' => 'warning',
                    'body' => "⚙️ Proses pengajuan #{$record->id} telah dimulai."
                ];

            case 'ready_pickup':
                return [
                    'title' => 'Siap Diambil',
                    'icon' => 'heroicon-o-inbox-arrow-down',
                    'iconColor' => 'info',
                    'body' => "📦 Pengajuan #{$record->id} sudah siap diambil."
                ];

            case 'completed':
                return [
                    'title' => 'Pengajuan Selesai',
                    'icon' => 'heroicon-o-check-badge',
                    'iconColor' => 'success',
                    'body' => "🎉 Pengajuan #{$record->id} telah selesai dan diterima oleh {$additionalData}."
                ];

            default:
                return [
                    'title' => 'Update Status',
                    'icon' => 'heroicon-o-bell',
                    'iconColor' => 'primary',
                    'body' => "📢 Status pengajuan #{$record->id} telah diperbarui."
                ];
        }
    }

    public static function getAllActions()
    {
        return [
            self::mulaiReview(),
            self::kirimKeSuperAdmin(),
            self::approveSuperAdmin(),
            self::rejectSuperAdmin(),
            self::mulaiProses(),
            self::siapDiambil(),
            self::selesai(),
        ];
    }
}
