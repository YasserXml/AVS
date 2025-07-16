<?php

namespace App\Services;

use App\Models\User;
use Filament\Notifications\Notification;

class PengajuanProjectNotificationService
{
    /**
     * Kirim notifikasi database dengan pembatasan
     */
    public static function sendDatabaseNotification($record, $status, $additionalData = null)
    {
        // Pastikan record dan relasi user ada
        if (!$record || !$record->user) {
            return;
        }

        $pengaju = $record->user;
        $currentUserId = filament()->auth()->id();

        // Ambil admin users
        $adminUsers = User::role(['super_admin', 'admin'])->get();

        $notificationConfigs = self::getNotificationConfigs($status, $record, $additionalData);

        // Kirim notifikasi ke pengaju (jika bukan user yang sedang login)
        if ($pengaju && $pengaju->id != $currentUserId) {
            $userConfig = $notificationConfigs['user'];
            Notification::make()
                ->title($userConfig['title'])
                ->icon($userConfig['icon'])
                ->iconColor($userConfig['iconColor'])
                ->body($userConfig['body'])
                ->sendToDatabase($pengaju);
        }

        // Kirim notifikasi ke admin users (kecuali user yang sedang login)
        foreach ($adminUsers as $admin) {
            if ($admin->id != $currentUserId) {
                $adminConfig = $notificationConfigs['admin'];
                Notification::make()
                    ->title($adminConfig['title'])
                    ->icon($adminConfig['icon'])
                    ->iconColor($adminConfig['iconColor'])
                    ->body($adminConfig['body'])
                    ->sendToDatabase($admin);
            }
        }
    }

    /**
     * Dapatkan konfigurasi notifikasi untuk user dan admin
     */
    public static function getNotificationConfigs($status, $record, $additionalData = null)
    {
        // Pastikan relasi nameproject ada
        $pengajuName = $record->user->name ?? 'Pengguna';
        $projectName = $record->nameproject->nama_project ?? 'Project';

        switch ($status) {
            case 'pending_pm_review':
                return [
                    'user' => [
                        'title' => 'Review PM Dimulai',
                        'icon' => 'heroicon-o-play',
                        'iconColor' => 'primary',
                        'body' => "🔍 Review pengajuan untuk project {$projectName} telah dimulai oleh Project Manager."
                    ],
                    'admin' => [
                        'title' => 'Review PM Dimulai',
                        'icon' => 'heroicon-o-play',
                        'iconColor' => 'primary',
                        'body' => "🔍 Review pengajuan project {$projectName} dari {$pengajuName} telah dimulai oleh PM."
                    ]
                ];

            case 'disetujui_pm_dikirim_ke_pengadaan':
                return [
                    'user' => [
                        'title' => 'Disetujui PM - Dikirim ke Pengadaan',
                        'icon' => 'heroicon-o-check-circle',
                        'iconColor' => 'success',
                        'body' => "✅ Pengajuan project {$projectName} telah disetujui PM dan dikirim ke Tim Pengadaan." .
                            ($additionalData ? " Catatan: {$additionalData}" : '')
                    ],
                    'admin' => [
                        'title' => 'Disetujui PM - Dikirim ke Pengadaan',
                        'icon' => 'heroicon-o-check-circle',
                        'iconColor' => 'success',
                        'body' => "✅ Pengajuan project {$projectName} dari {$pengajuName} telah disetujui PM dan dikirim ke Tim Pengadaan." .
                            ($additionalData ? " Catatan: {$additionalData}" : '')
                    ]
                ];

            case 'ditolak_pm':
                return [
                    'user' => [
                        'title' => 'Pengajuan Ditolak PM',
                        'icon' => 'heroicon-o-x-circle',
                        'iconColor' => 'danger',
                        'body' => "❌ Pengajuan project {$projectName} ditolak oleh Project Manager. Alasan: {$additionalData}"
                    ],
                    'admin' => [
                        'title' => 'Pengajuan Ditolak PM',
                        'icon' => 'heroicon-o-x-circle',
                        'iconColor' => 'danger',
                        'body' => "❌ Pengajuan project {$projectName} dari {$pengajuName} ditolak oleh PM. Alasan: {$additionalData}"
                    ]
                ];

            case 'disetujui_pengadaan':
                return [
                    'user' => [
                        'title' => 'Disetujui Tim Pengadaan',
                        'icon' => 'heroicon-o-check-circle',
                        'iconColor' => 'success',
                        'body' => "✅ Pengajuan project {$projectName} telah disetujui oleh Tim Pengadaan." .
                            ($additionalData ? " Catatan: {$additionalData}" : '')
                    ],
                    'admin' => [
                        'title' => 'Disetujui Tim Pengadaan',
                        'icon' => 'heroicon-o-check-circle',
                        'iconColor' => 'success',
                        'body' => "✅ Pengajuan project {$projectName} dari {$pengajuName} telah disetujui oleh Tim Pengadaan." .
                            ($additionalData ? " Catatan: {$additionalData}" : '')
                    ]
                ];

            case 'ditolak_pengadaan':
                return [
                    'user' => [
                        'title' => 'Ditolak Tim Pengadaan',
                        'icon' => 'heroicon-o-x-circle',
                        'iconColor' => 'danger',
                        'body' => "❌ Pengajuan project {$projectName} ditolak oleh Tim Pengadaan. Alasan: {$additionalData}"
                    ],
                    'admin' => [
                        'title' => 'Ditolak Tim Pengadaan',
                        'icon' => 'heroicon-o-x-circle',
                        'iconColor' => 'danger',
                        'body' => "❌ Pengajuan project {$projectName} dari {$pengajuName} ditolak oleh Tim Pengadaan. Alasan: {$additionalData}"
                    ]
                ];

            case 'pengajuan_dikirim_ke_direksi':
                return [
                    'user' => [
                        'title' => 'Dikirim ke Direksi',
                        'icon' => 'heroicon-o-arrow-up-tray',
                        'iconColor' => 'warning',
                        'body' => "📤 Pengajuan project {$projectName} telah dikirim ke direksi untuk persetujuan."
                    ],
                    'admin' => [
                        'title' => 'Dikirim ke Direksi',
                        'icon' => 'heroicon-o-arrow-up-tray',
                        'iconColor' => 'warning',
                        'body' => "📤 Pengajuan project {$projectName} dari {$pengajuName} telah dikirim ke direksi."
                    ]
                ];

            case 'approved_by_direksi':
                return [
                    'user' => [
                        'title' => 'Disetujui Direksi',
                        'icon' => 'heroicon-o-check-circle',
                        'iconColor' => 'success',
                        'body' => "✅ Pengajuan project {$projectName} telah disetujui oleh direksi." .
                            ($additionalData ? " Catatan: {$additionalData}" : '')
                    ],
                    'admin' => [
                        'title' => 'Disetujui Direksi',
                        'icon' => 'heroicon-o-check-circle',
                        'iconColor' => 'success',
                        'body' => "✅ Pengajuan project {$projectName} dari {$pengajuName} telah disetujui oleh direksi." .
                            ($additionalData ? " Catatan: {$additionalData}" : '')
                    ]
                ];

            case 'reject_direksi':
                return [
                    'user' => [
                        'title' => 'Ditolak Direksi',
                        'icon' => 'heroicon-o-x-circle',
                        'iconColor' => 'danger',
                        'body' => "❌ Pengajuan project {$projectName} ditolak oleh direksi. Alasan: {$additionalData}"
                    ],
                    'admin' => [
                        'title' => 'Ditolak Direksi',
                        'icon' => 'heroicon-o-x-circle',
                        'iconColor' => 'danger',
                        'body' => "❌ Pengajuan project {$projectName} dari {$pengajuName} ditolak oleh direksi. Alasan: {$additionalData}"
                    ]
                ];

            case 'pengajuan_dikirim_ke_keuangan':
                return [
                    'user' => [
                        'title' => 'Dikirim ke Keuangan',
                        'icon' => 'heroicon-o-arrow-up-tray',
                        'iconColor' => 'warning',
                        'body' => "📤 Pengajuan project {$projectName} telah dikirim ke keuangan untuk diproses."
                    ],
                    'admin' => [
                        'title' => 'Dikirim ke Keuangan',
                        'icon' => 'heroicon-o-arrow-up-tray',
                        'iconColor' => 'warning',
                        'body' => "📤 Pengajuan project {$projectName} dari {$pengajuName} telah dikirim ke keuangan."
                    ]
                ];

            case 'pending_keuangan':
                return [
                    'user' => [
                        'title' => 'Review Keuangan Dimulai',
                        'icon' => 'heroicon-o-play',
                        'iconColor' => 'primary',
                        'body' => "🔍 Review keuangan untuk project {$projectName} telah dimulai."
                    ],
                    'admin' => [
                        'title' => 'Review Keuangan Dimulai',
                        'icon' => 'heroicon-o-play',
                        'iconColor' => 'primary',
                        'body' => "🔍 Review keuangan project {$projectName} dari {$pengajuName} telah dimulai."
                    ]
                ];

            case 'process_keuangan':
                return [
                    'user' => [
                        'title' => 'Proses Keuangan',
                        'icon' => 'heroicon-o-cog',
                        'iconColor' => 'warning',
                        'body' => "⚙️ Pengajuan project {$projectName} sedang diproses oleh keuangan."
                    ],
                    'admin' => [
                        'title' => 'Proses Keuangan',
                        'icon' => 'heroicon-o-cog',
                        'iconColor' => 'warning',
                        'body' => "⚙️ Pengajuan project {$projectName} dari {$pengajuName} sedang diproses oleh keuangan."
                    ]
                ];

            case 'execute_keuangan':
                return [
                    'user' => [
                        'title' => 'Proses Keuangan Selesai',
                        'icon' => 'heroicon-o-check-circle',
                        'iconColor' => 'success',
                        'body' => "✅ Proses keuangan untuk project {$projectName} telah selesai." .
                            ($additionalData ? " Catatan: {$additionalData}" : '')
                    ],
                    'admin' => [
                        'title' => 'Proses Keuangan Selesai',
                        'icon' => 'heroicon-o-check-circle',
                        'iconColor' => 'success',
                        'body' => "✅ Proses keuangan project {$projectName} dari {$pengajuName} telah selesai." .
                            ($additionalData ? " Catatan: {$additionalData}" : '')
                    ]
                ];

            case 'pengajuan_dikirim_ke_pengadaan_final':
                return [
                    'user' => [
                        'title' => 'Dikirim ke Pengadaan Final',
                        'icon' => 'heroicon-o-arrow-up-tray',
                        'iconColor' => 'warning',
                        'body' => "📤 Pengajuan project {$projectName} telah dikirim kembali ke pengadaan untuk proses final."
                    ],
                    'admin' => [
                        'title' => 'Dikirim ke Pengadaan Final',
                        'icon' => 'heroicon-o-arrow-up-tray',
                        'iconColor' => 'warning',
                        'body' => "📤 Pengajuan project {$projectName} dari {$pengajuName} telah dikirim kembali ke pengadaan untuk proses final."
                    ]
                ];

            case 'pengajuan_dikirim_ke_admin':
                return [
                    'user' => [
                        'title' => 'Dikirim ke Admin',
                        'icon' => 'heroicon-o-arrow-up-tray',
                        'iconColor' => 'warning',
                        'body' => "📤 Pengajuan project {$projectName} telah dikirim ke admin untuk diproses."
                    ],
                    'admin' => [
                        'title' => 'Dikirim ke Admin',
                        'icon' => 'heroicon-o-arrow-up-tray',
                        'iconColor' => 'warning',
                        'body' => "📤 Pengajuan project {$projectName} dari {$pengajuName} telah dikirim ke admin."
                    ]
                ];

            case 'processing':
                return [
                    'user' => [
                        'title' => 'Proses Pengadaan Dimulai',
                        'icon' => 'heroicon-o-play',
                        'iconColor' => 'warning',
                        'body' => "⚙️ Proses pengadaan untuk project {$projectName} telah dimulai."
                    ],
                    'admin' => [
                        'title' => 'Proses Pengadaan Dimulai',
                        'icon' => 'heroicon-o-play',
                        'iconColor' => 'warning',
                        'body' => "⚙️ Proses pengadaan project {$projectName} dari {$pengajuName} telah dimulai."
                    ]
                ];

            case 'ready_pickup':
                return [
                    'user' => [
                        'title' => 'Siap Diambil',
                        'icon' => 'heroicon-o-inbox-arrow-down',
                        'iconColor' => 'info',
                        'body' => "📦 Pengajuan project {$projectName} sudah siap diambil."
                    ],
                    'admin' => [
                        'title' => 'Siap Diambil',
                        'icon' => 'heroicon-o-inbox-arrow-down',
                        'iconColor' => 'info',
                        'body' => "📦 Pengajuan project {$projectName} dari {$pengajuName} sudah siap diambil."
                    ]
                ];

            case 'completed':
                return [
                    'user' => [
                        'title' => 'Pengajuan Selesai',
                        'icon' => 'heroicon-o-check-badge',
                        'iconColor' => 'success',
                        'body' => "🎉 Pengajuan project {$projectName} telah selesai dan diterima oleh {$additionalData}."
                    ],
                    'admin' => [
                        'title' => 'Pengajuan Selesai',
                        'icon' => 'heroicon-o-check-badge',
                        'iconColor' => 'success',
                        'body' => "🎉 Pengajuan project {$projectName} dari {$pengajuName} telah selesai dan diterima oleh {$additionalData}."
                    ]
                ];

            default:
                return [
                    'user' => [
                        'title' => 'Update Status',
                        'icon' => 'heroicon-o-bell',
                        'iconColor' => 'primary',
                        'body' => "📢 Status pengajuan project {$projectName} telah diperbarui."
                    ],
                    'admin' => [
                        'title' => 'Update Status',
                        'icon' => 'heroicon-o-bell',
                        'iconColor' => 'primary',
                        'body' => "📢 Status pengajuan project {$projectName} dari {$pengajuName} telah diperbarui."
                    ]
                ];
        }
    }

    /**
     * Kirim notifikasi ke role/user tertentu
     */
    public static function sendNotificationToRole($record, $status, $roles, $additionalData = null)
    {
        $currentUserId = filament()->auth()->id();
        $users = User::role($roles)->get();

        $notificationConfigs = self::getNotificationConfigs($status, $record, $additionalData);

        foreach ($users as $user) {
            if ($user->id != $currentUserId) {
                $config = $notificationConfigs['admin']; // Gunakan config admin untuk role tertentu
                Notification::make()
                    ->title($config['title'])
                    ->icon($config['icon'])
                    ->iconColor($config['iconColor'])
                    ->body($config['body'])
                    ->sendToDatabase($user);
            }
        }
    }

    public static function sendNotificationToUser($record, $status, $userId, $additionalData = null)
    {
        $currentUserId = filament()->auth()->id();

        if ($userId != $currentUserId) {
            $user = User::find($userId);
            if ($user) {
                $notificationConfigs = self::getNotificationConfigs($status, $record, $additionalData);
                $config = $notificationConfigs['user'];

                Notification::make()
                    ->title($config['title'])
                    ->icon($config['icon'])
                    ->iconColor($config['iconColor'])
                    ->body($config['body'])
                    ->sendToDatabase($user);
            }
        }
    }
}
