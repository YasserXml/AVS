<?php

namespace App\Services;

use App\Models\User;
use Filament\Notifications\Notification;

class PengajuanNotificationService
{
    /**
     * Kirim notifikasi database dengan pembatasan
     */
    public static function sendDatabaseNotification($record, $status, $additionalData = null)
    {
        $pengaju = $record->user;
        $currentUserId = filament()->auth()->id();

        $adminUsers = User::whereHas(
            'roles',
            fn($query) => $query->whereIn('name', ['super_admin', 'admin', 'direktur_keuangan', 'keuangan', 'purchasing'])
        )
            ->where('id', '!=', $pengaju->id)
            ->get();

        $notificationConfigs = self::getNotificationConfigs($status, $record, $additionalData);

        if ($pengaju && $pengaju->id != $currentUserId) {
            $userConfig = $notificationConfigs['user'];
            Notification::make()
                ->title($userConfig['title'])
                ->icon($userConfig['icon'])
                ->iconColor($userConfig['iconColor'])
                ->body($userConfig['body'])
                ->sendToDatabase($pengaju); 
        }

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
        $pengajuName = $record->user->name ?? 'Pengguna';

        switch ($status) {
            case 'pending_admin_review':
                return [
                    'user' => [
                        'title' => 'Review Dimulai',
                        'icon' => 'heroicon-o-play',
                        'iconColor' => 'primary',
                        'body' => "🔍 Review pengajuan Anda telah dimulai oleh admin."
                    ],
                    'admin' => [
                        'title' => 'Review Dimulai',
                        'icon' => 'heroicon-o-play',
                        'iconColor' => 'primary',
                        'body' => "🔍 Review pengajuan dari {$pengajuName} telah dimulai."
                    ]
                ];

            case 'diajukan_ke_superadmin':
                return [
                    'user' => [
                        'title' => 'Dikirim ke Tim Pengadaan',
                        'icon' => 'heroicon-o-arrow-up-tray',
                        'iconColor' => 'warning',
                        'body' => "📤 Pengajuan Anda telah dikirim ke tim pengadaan untuk persetujuan."
                    ],
                    'admin' => [
                        'title' => 'Dikirim ke Tim Pengadaan',
                        'icon' => 'heroicon-o-arrow-up-tray',
                        'iconColor' => 'warning',
                        'body' => "📤 Pengajuan dari {$pengajuName} telah dikirim ke tim pengadaan."
                    ]
                ];

            case 'superadmin_approved':
                return [
                    'user' => [
                        'title' => 'Pengajuan Disetujui',
                        'icon' => 'heroicon-o-check-circle',
                        'iconColor' => 'success',
                        'body' => "✅ Pengajuan Anda telah disetujui oleh Tim Pengadaan." .
                            ($additionalData ? " Catatan: {$additionalData}" : '')
                    ],
                    'admin' => [
                        'title' => 'Pengajuan Disetujui',
                        'icon' => 'heroicon-o-check-circle',
                        'iconColor' => 'success',
                        'body' => "✅ Pengajuan dari {$pengajuName} telah disetujui oleh Tim Pengadaan." .
                            ($additionalData ? " Catatan: {$additionalData}" : '')
                    ]
                ];

            case 'superadmin_rejected':
                return [
                    'user' => [
                        'title' => 'Pengajuan Ditolak',
                        'icon' => 'heroicon-o-x-circle',
                        'iconColor' => 'danger',
                        'body' => "❌ Pengajuan Anda ditolak oleh Tim Pengadaan. Alasan: {$additionalData}"
                    ],
                    'admin' => [
                        'title' => 'Pengajuan Ditolak',
                        'icon' => 'heroicon-o-x-circle',
                        'iconColor' => 'danger',
                        'body' => "❌ Pengajuan dari {$pengajuName} ditolak oleh Tim Pengadaan. Alasan: {$additionalData}"
                    ]
                ];

            case 'pengajuan_dikirim_ke_direksi':
                return [
                    'user' => [
                        'title' => 'Dikirim ke Direksi',
                        'icon' => 'heroicon-o-arrow-up-tray',
                        'iconColor' => 'warning',
                        'body' => "📤 Pengajuan Anda telah dikirim ke direksi untuk persetujuan."
                    ],
                    'admin' => [
                        'title' => 'Dikirim ke Direksi',
                        'icon' => 'heroicon-o-arrow-up-tray',
                        'iconColor' => 'warning',
                        'body' => "📤 Pengajuan dari {$pengajuName} telah dikirim ke direksi."
                    ]
                ];

            case 'acc_direksi':
                return [
                    'user' => [
                        'title' => 'Disetujui Direksi',
                        'icon' => 'heroicon-o-check-circle',
                        'iconColor' => 'success',
                        'body' => "✅ Pengajuan Anda telah disetujui oleh direksi." .
                            ($additionalData ? " Catatan: {$additionalData}" : '')
                    ],
                    'admin' => [
                        'title' => 'Disetujui Direksi',
                        'icon' => 'heroicon-o-check-circle',
                        'iconColor' => 'success',
                        'body' => "✅ Pengajuan dari {$pengajuName} telah disetujui oleh direksi." .
                            ($additionalData ? " Catatan: {$additionalData}" : '')
                    ]
                ];

            case 'reject_direksi':
                return [
                    'user' => [
                        'title' => 'Ditolak Direksi',
                        'icon' => 'heroicon-o-x-circle',
                        'iconColor' => 'danger',
                        'body' => "❌ Pengajuan Anda ditolak oleh direksi. Alasan: {$additionalData}"
                    ],
                    'admin' => [
                        'title' => 'Ditolak Direksi',
                        'icon' => 'heroicon-o-x-circle',
                        'iconColor' => 'danger',
                        'body' => "❌ Pengajuan dari {$pengajuName} ditolak oleh direksi. Alasan: {$additionalData}"
                    ]
                ];

            case 'pengajuan_dikirim_ke_keuangan':
                return [
                    'user' => [
                        'title' => 'Dikirim ke Keuangan',
                        'icon' => 'heroicon-o-arrow-up-tray',
                        'iconColor' => 'warning',
                        'body' => "📤 Pengajuan Anda telah dikirim ke keuangan untuk diproses."
                    ],
                    'admin' => [
                        'title' => 'Dikirim ke Keuangan',
                        'icon' => 'heroicon-o-arrow-up-tray',
                        'iconColor' => 'warning',
                        'body' => "📤 Pengajuan dari {$pengajuName} telah dikirim ke keuangan."
                    ]
                ];

            case 'pending_keuangan':
                return [
                    'user' => [
                        'title' => 'Review Keuangan',
                        'icon' => 'heroicon-o-play',
                        'iconColor' => 'primary',
                        'body' => "🔍 Review keuangan pengajuan Anda telah dimulai."
                    ],
                    'admin' => [
                        'title' => 'Review Keuangan',
                        'icon' => 'heroicon-o-play',
                        'iconColor' => 'primary',
                        'body' => "🔍 Review keuangan pengajuan dari {$pengajuName} telah dimulai."
                    ]
                ];

            case 'process_keuangan':
                return [
                    'user' => [
                        'title' => 'Proses Keuangan',
                        'icon' => 'heroicon-o-cog',
                        'iconColor' => 'warning',
                        'body' => "⚙️ Pengajuan Anda sedang diproses oleh keuangan."
                    ],
                    'admin' => [
                        'title' => 'Proses Keuangan',
                        'icon' => 'heroicon-o-cog',
                        'iconColor' => 'warning',
                        'body' => "⚙️ Pengajuan dari {$pengajuName} sedang diproses oleh keuangan."
                    ]
                ];

            case 'execute_keuangan':
                return [
                    'user' => [
                        'title' => 'Proses Keuangan Selesai',
                        'icon' => 'heroicon-o-check-circle',
                        'iconColor' => 'success',
                        'body' => "✅ Proses keuangan pengajuan Anda telah selesai." .
                            ($additionalData ? " Catatan: {$additionalData}" : '')
                    ],
                    'admin' => [
                        'title' => 'Proses Keuangan Selesai',
                        'icon' => 'heroicon-o-check-circle',
                        'iconColor' => 'success',
                        'body' => "✅ Proses keuangan pengajuan dari {$pengajuName} telah selesai." .
                            ($additionalData ? " Catatan: {$additionalData}" : '')
                    ]
                ];

            case 'pengajuan_dikirim_ke_pengadaan':
                return [
                    'user' => [
                        'title' => 'Dikirim ke Pengadaan',
                        'icon' => 'heroicon-o-arrow-up-tray',
                        'iconColor' => 'warning',
                        'body' => "📤 Pengajuan Anda telah dikirim ke pengadaan."
                    ],
                    'admin' => [
                        'title' => 'Dikirim ke Pengadaan',
                        'icon' => 'heroicon-o-arrow-up-tray',
                        'iconColor' => 'warning',
                        'body' => "📤 Pengajuan dari {$pengajuName} telah dikirim ke pengadaan."
                    ]
                ];

            case 'pengajuan_dikirim_ke_admin':
                return [
                    'user' => [
                        'title' => 'Dikirim ke Admin',
                        'icon' => 'heroicon-o-arrow-up-tray',
                        'iconColor' => 'warning',
                        'body' => "📤 Pengajuan Anda telah dikirim ke admin untuk diproses."
                    ],
                    'admin' => [
                        'title' => 'Dikirim ke Admin',
                        'icon' => 'heroicon-o-arrow-up-tray',
                        'iconColor' => 'warning',
                        'body' => "📤 Pengajuan dari {$pengajuName} telah dikirim ke admin."
                    ]
                ];

            case 'processing_started':
                return [
                    'user' => [
                        'title' => 'Proses Dimulai',
                        'icon' => 'heroicon-o-play',
                        'iconColor' => 'warning',
                        'body' => "⚙️ Proses pengajuan Anda telah dimulai."
                    ],
                    'admin' => [
                        'title' => 'Proses Dimulai',
                        'icon' => 'heroicon-o-play',
                        'iconColor' => 'warning',
                        'body' => "⚙️ Proses pengajuan dari {$pengajuName} telah dimulai."
                    ]
                ];

            case 'ready_pickup':
                return [
                    'user' => [
                        'title' => 'Siap Diambil',
                        'icon' => 'heroicon-o-inbox-arrow-down',
                        'iconColor' => 'info',
                        'body' => "📦 Pengajuan Anda sudah siap diambil."
                    ],
                    'admin' => [
                        'title' => 'Siap Diambil',
                        'icon' => 'heroicon-o-inbox-arrow-down',
                        'iconColor' => 'info',
                        'body' => "📦 Pengajuan dari {$pengajuName} sudah siap diambil."
                    ]
                ];

            case 'completed':
                return [
                    'user' => [
                        'title' => 'Pengajuan Selesai',
                        'icon' => 'heroicon-o-check-badge',
                        'iconColor' => 'success',
                        'body' => "🎉 Pengajuan Anda telah selesai dan diterima oleh {$additionalData}."
                    ],
                    'admin' => [
                        'title' => 'Pengajuan Selesai',
                        'icon' => 'heroicon-o-check-badge',
                        'iconColor' => 'success',
                        'body' => "🎉 Pengajuan dari {$pengajuName} telah selesai dan diterima oleh {$additionalData}."
                    ]
                ];
            default:
                return [
                    'user' => [
                        'title' => 'Update Status',
                        'icon' => 'heroicon-o-bell',
                        'iconColor' => 'primary',
                        'body' => "📢 Status pengajuan Anda telah diperbarui."
                    ],
                    'admin' => [
                        'title' => 'Update Status',
                        'icon' => 'heroicon-o-bell',
                        'iconColor' => 'primary',
                        'body' => "📢 Status pengajuan dari {$pengajuName} telah diperbarui."
                    ]
                ];
        }
    }
}
