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
use Illuminate\Support\Facades\Log;
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
                self::sendEmailToSuperAdmin($record, 'sent_to_superadmin');

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
                    'received_by_name' => $data['received_by'],
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

        // Kirim email berdasarkan status
        if ($pengaju && $pengaju->email) {
            switch ($status) {
                case 'superadmin_approved':
                    Mail::to($pengaju->email)->queue(new PengajuanApprovedMail($record, 'Tim Pengadaan', $additionalData));
                    break;
                case 'superadmin_rejected':
                    Mail::to($pengaju->email)->queue(new PengajuanRejectMail($record, 'Tim Pengadaan', $additionalData));
                    break;
                case 'ready_pickup':
                    Mail::to($pengaju->email)->queue(new PengajuanReadyPickupMail($record));
                    break;
            }
        }

        // Kirim notifikasi database
        self::sendDatabaseNotification($record, $status, $additionalData);
    }

   private static function sendEmailToSuperAdmin($record)
    {
        try {
            // Pastikan model User dan Mail sudah di-import
            if (!class_exists('App\Models\User')) {
                throw new \Exception('Model User tidak ditemukan');
            }

            if (!class_exists('App\Mail\PengajuanSentToSuperAdminMail')) {
                throw new \Exception('Mail class PengajuanSentToSuperAdminMail tidak ditemukan');
            }

            // Ambil semua user dengan role superadmin atau super_admin
            $superadmins = User::whereHas('roles', function ($query) {
                $query->where('name', 'super_admin'); // Pastikan nama role sesuai
            })->get();

            // Debug: Log jumlah superadmin yang ditemukan
            Log::info('Jumlah superadmin ditemukan: ' . $superadmins->count());

            if ($superadmins->isEmpty()) {
                Log::warning('Tidak ada user dengan role super_admin yang ditemukan');
                return;
            }

            // Kirim email ke semua superadmin
            foreach ($superadmins as $superadmin) {
                if ($superadmin->email) {
                    Log::info('Mengirim email ke: ' . $superadmin->email);
                    
                    // Gunakan dispatch untuk debugging yang lebih baik
                    Mail::to($superadmin->email)->queue(new PengajuanSentToSuperAdminMail($record));
                } else {
                    Log::warning('Superadmin ' . $superadmin->name . ' tidak memiliki email');
                }
            }

        } catch (\Exception $e) {
            Log::error('Error dalam sendEmailToSuperAdmin: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Kirim notifikasi database dengan pembatasan
     */
    private static function sendDatabaseNotification($record, $status, $additionalData = null)
    {
        // Ambil user yang mengajukan
        $pengaju = $record->user;
        $currentUserId = filament()->auth()->id();

        // Ambil admin dan super admin (kecuali yang mengajukan)
        $adminUsers = User::whereHas(
            'roles',
            fn($query) => $query->whereIn('name', ['super_admin', 'admin'])
        )
            ->where('id', '!=', $pengaju->id) // Kecualikan pengaju dari notifikasi admin
            ->get();

        // Konfigurasi notifikasi
        $notificationConfigs = self::getNotificationConfigs($status, $record, $additionalData);

        // Kirim notifikasi ke pengaju (jika bukan dia yang melakukan aksi)
        if ($pengaju && $pengaju->id != $currentUserId) {
            $userConfig = $notificationConfigs['user'];
            Notification::make()
                ->title($userConfig['title'])
                ->icon($userConfig['icon'])
                ->iconColor($userConfig['iconColor'])
                ->body($userConfig['body'])
                ->sendToDatabase($pengaju);
        }

        // Kirim notifikasi ke admin/super admin (kecuali yang melakukan aksi)
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
    private static function getNotificationConfigs($status, $record, $additionalData = null)
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
