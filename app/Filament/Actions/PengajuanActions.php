<?php

namespace App\Filament\Actions;

use App\Mail\PengajuanApprovedMail;
use App\Mail\PengajuanReadyPickupMail;
use App\Mail\PengajuanRejectMail;
use App\Mail\PengajuanSentToSuperAdminMail;
use App\Models\User;
use App\Services\PengajuanEmailService;
use App\Services\PengajuanNotificationService;
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

                PengajuanNotificationService::sendDatabaseNotification($record, 'pending_admin_review');

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

                PengajuanEmailService::sendEmailToSuperAdmin($record);
                PengajuanNotificationService::sendDatabaseNotification($record, 'diajukan_ke_superadmin');

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
                    filament()->auth()->user()->hasRole('purchasing')
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

                PengajuanEmailService::sendNotificationWithEmail($record, 'superadmin_approved', $data['catatan'] ?? null);

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
                    filament()->auth()->user()->hasRole('purchasing')
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

                PengajuanEmailService::sendNotificationWithEmail($record, 'rejected', $data['alasan']);

                Notification::make()
                    ->title('Pengajuan Ditolak')
                    ->body('Pengajuan berhasil ditolak.')
                    ->warning()
                    ->send();
            });
    }

    // ACTION BARU: Kirim ke Direksi
    public static function kirimKeDireksi()
    {
        return Tables\Actions\Action::make('kirim_ke_direksi')
            ->label('Kirim ke Direksi')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('warning')
            ->visible(
                fn($record) =>
                $record->status === 'superadmin_approved' &&
                    filament()->auth()->user()->hasRole('purchasing')
            )
            ->requiresConfirmation()
            ->modalHeading('Kirim ke Direksi')
            ->modalDescription('Apakah Anda yakin ingin mengirim pengajuan ini ke direksi?')
            ->action(function ($record) {
                $record->update([
                    'status' => 'pengajuan_dikirim_ke_direksi',
                ]);

                $record->addStatusHistory(
                    'pengajuan_dikirim_ke_direksi',
                    filament()->auth()->id(),
                    'Pengajuan dikirim ke direksi'
                );

                PengajuanEmailService::sendEmailToDireksi($record);

                Notification::make()
                    ->title('Berhasil')
                    ->body('Pengajuan berhasil dikirim ke direksi.')
                    ->success()
                    ->send();
            });
    }

    // ACTION BARU: Approve Direksi
    public static function approveDireksi()
    {
        return Tables\Actions\Action::make('approve_direksi')
            ->label('Setujui')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->visible(
                fn($record) =>
                $record->status === 'pengajuan_dikirim_ke_direksi' &&
                    filament()->auth()->user()->hasRole('direktur_keuangan')
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
                    'status' => 'acc_direksi',
                    'approved_by_direksi' => filament()->auth()->id(),
                    'approved_at_direksi' => now(),
                ]);

                $record->addStatusHistory(
                    'acc_direksi',
                    filament()->auth()->id(),
                    $data['catatan'] ?? 'Pengajuan disetujui oleh direksi'
                );

                PengajuanEmailService::sendNotificationWithEmail($record, 'approved_direksi', $data['catatan'] ?? null);

                Notification::make()
                    ->title('Berhasil')
                    ->body('Pengajuan berhasil disetujui.')
                    ->success()
                    ->send();
            });
    }

    public static function rejectDireksi()
    {
        return Tables\Actions\Action::make('reject_direksi')
            ->label('Tolak')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->visible(
                fn($record) =>
                $record->status === 'pengajuan_dikirim_ke_direksi' &&
                    filament()->auth()->user()->hasRole('direktur_keuangan')
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
                    'status' => 'reject_direksi',
                    'rejected_by' => filament()->auth()->id(),
                    'rejected_at' => now(),
                    'reject_reason' => $data['alasan'],
                    'rejected_by_role' => 'direksi',
                ]);

                $record->addStatusHistory(
                    'reject_direksi',
                    filament()->auth()->id(),
                    'Pengajuan ditolak oleh direksi: ' . $data['alasan']
                );

                PengajuanEmailService::sendNotificationWithEmail($record, 'rejected_direksi', $data['alasan']);

                Notification::make()
                    ->title('Pengajuan Ditolak')
                    ->body('Pengajuan berhasil ditolak.')
                    ->warning()
                    ->send();
            });
    }

    // ACTION BARU: Kirim ke Keuangan
    public static function kirimKeKeuangan()
    {
        return Tables\Actions\Action::make('kirim_ke_keuangan')
            ->label('Kirim ke Keuangan')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('warning')
            ->visible(
                fn($record) =>
                $record->status === 'acc_direksi' &&
                    filament()->auth()->user()->hasRole('direktur_keuangan')
            )
            ->requiresConfirmation()
            ->modalHeading('Kirim ke Keuangan')
            ->modalDescription('Apakah Anda yakin ingin mengirim pengajuan ini ke keuangan?')
            ->action(function ($record) {
                $record->update([
                    'status' => 'pengajuan_dikirim_ke_keuangan',
                ]);

                $record->addStatusHistory(
                    'pengajuan_dikirim_ke_keuangan',
                    filament()->auth()->id(),
                    'Pengajuan dikirim ke keuangan'
                );

                PengajuanEmailService::sendEmailToKeuangan($record);

                Notification::make()
                    ->title('Berhasil')
                    ->body('Pengajuan berhasil dikirim ke keuangan.')
                    ->success()
                    ->send();
            });
    }

    // ACTION BARU: Review Keuangan
    public static function reviewKeuangan()
    {
        return Tables\Actions\Action::make('review_keuangan')
            ->label('Mulai Review')
            ->icon('heroicon-o-play')
            ->color('primary')
            ->visible(
                fn($record) =>
                $record->status === 'pengajuan_dikirim_ke_keuangan' &&
                    filament()->auth()->user()->hasRole('keuangan')
            )
            ->requiresConfirmation()
            ->modalHeading('Mulai Review Keuangan')
            ->modalDescription('Apakah Anda yakin ingin memulai review keuangan?')
            ->action(function ($record) {
                $record->update([
                    'status' => 'pending_keuangan',
                ]);

                $record->addStatusHistory(
                    'pending_keuangan',
                    filament()->auth()->id(),
                    'Review keuangan dimulai'
                );

                PengajuanNotificationService::sendDatabaseNotification($record, 'pending_keuangan');

                Notification::make()
                    ->title('Berhasil')
                    ->body('Review keuangan telah dimulai.')
                    ->success()
                    ->send();
            });
    }

    // ACTION BARU: Proses Keuangan
    public static function prosesKeuangan()
    {
        return Tables\Actions\Action::make('proses_keuangan')
            ->label('Proses Keuangan')
            ->icon('heroicon-o-cog')
            ->color('warning')
            ->visible(
                fn($record) =>
                $record->status === 'pending_keuangan' &&
                    filament()->auth()->user()->hasRole('keuangan')
            )
            ->requiresConfirmation()
            ->modalHeading('Proses Keuangan')
            ->modalDescription('Apakah Anda yakin ingin memproses pengajuan ini?')
            ->action(function ($record) {
                $record->update([
                    'status' => 'process_keuangan',
                ]);

                $record->addStatusHistory(
                    'process_keuangan',
                    filament()->auth()->id(),
                    'Pengajuan sedang diproses oleh keuangan'
                );

                PengajuanNotificationService::sendDatabaseNotification($record, 'process_keuangan');

                Notification::make()
                    ->title('Berhasil')
                    ->body('Pengajuan sedang diproses.')
                    ->success()
                    ->send();
            });
    }

    // ACTION BARU: Execute Keuangan
    public static function executeKeuangan()
    {
        return Tables\Actions\Action::make('execute_keuangan')
            ->label('Selesai Proses')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->visible(
                fn($record) =>
                $record->status === 'process_keuangan' &&
                    filament()->auth()->user()->hasRole('keuangan')
            )
            ->form([
                Forms\Components\Textarea::make('catatan')
                    ->label('Catatan Keuangan')
                    ->rows(3)
                    ->placeholder('Tambahkan catatan proses keuangan...'),
            ])
            ->requiresConfirmation()
            ->modalHeading('Selesai Proses Keuangan')
            ->modalDescription('Apakah Anda yakin proses keuangan telah selesai?')
            ->action(function ($record, array $data) {
                $record->update([
                    'status' => 'execute_keuangan',
                    'executed_by_keuangan' => filament()->auth()->id(),
                    'executed_at_keuangan' => now(),
                ]);

                $record->addStatusHistory(
                    'execute_keuangan',
                    filament()->auth()->id(),
                    $data['catatan'] ?? 'Proses keuangan selesai'
                );

                PengajuanEmailService::sendNotificationWithEmail($record, 'execute_keuangan', $data['catatan'] ?? null);

                Notification::make()
                    ->title('Berhasil')
                    ->body('Proses keuangan telah selesai.')
                    ->success()
                    ->send();
            });
    }

    // ACTION BARU: Kirim ke Pengadaan (dari keuangan)
    public static function kirimKePengadaan()
    {
        return Tables\Actions\Action::make('kirim_ke_pengadaan')
            ->label('Kirim ke Pengadaan')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('warning')
            ->visible(
                fn($record) =>
                $record->status === 'execute_keuangan' &&
                    filament()->auth()->user()->hasRole('keuangan')
            )
            ->requiresConfirmation()
            ->modalHeading('Kirim ke Pengadaan')
            ->modalDescription('Apakah Anda yakin ingin mengirim pengajuan ini ke pengadaan?')
            ->action(function ($record) {
                $record->update([
                    'status' => 'pengajuan_dikirim_ke_pengadaan',
                ]);

                $record->addStatusHistory(
                    'pengajuan_dikirim_ke_pengadaan',
                    filament()->auth()->id(),
                    'Pengajuan dikirim ke pengadaan'
                );

                PengajuanEmailService::sendEmailToPengadaan($record);

                Notification::make()
                    ->title('Berhasil')
                    ->body('Pengajuan berhasil dikirim ke pengadaan.')
                    ->success()
                    ->send();
            });
    }

    // ACTION BARU: Kirim ke Admin (dari pengadaan)
    public static function kirimKeAdmin()
    {
        return Tables\Actions\Action::make('kirim_ke_admin')
            ->label('Kirim ke Admin')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('warning')
            ->visible(
                fn($record) =>
                $record->status === 'pengajuan_dikirim_ke_pengadaan' &&
                    filament()->auth()->user()->hasRole('purchasing')
            )
            ->requiresConfirmation()
            ->modalHeading('Kirim ke Admin')
            ->modalDescription('Apakah Anda yakin ingin mengirim pengajuan ini ke admin?')
            ->action(function ($record) {
                $record->update([
                    'status' => 'pengajuan_dikirim_ke_admin',
                ]);

                $record->addStatusHistory(
                    'pengajuan_dikirim_ke_admin',
                    filament()->auth()->id(),
                    'Pengajuan dikirim ke admin'
                );

                PengajuanEmailService::sendEmailToAdmin($record);

                Notification::make()
                    ->title('Berhasil')
                    ->body('Pengajuan berhasil dikirim ke admin.')
                    ->success()
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
                $record->status === 'pengajuan_dikirim_ke_admin' &&
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

                PengajuanNotificationService::sendDatabaseNotification($record, 'processing_started');

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

                PengajuanEmailService::sendNotificationWithEmail($record, 'ready_pickup');

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

                PengajuanNotificationService::sendDatabaseNotification($record, 'completed', $data['received_by']);

                Notification::make()
                    ->title('Berhasil')
                    ->body('Pengajuan berhasil diselesaikan.')
                    ->success()
                    ->send();
            });
    }

    public static function getAllActions()
    {
        return [
            self::mulaiReview(),
            self::kirimKeSuperAdmin(),
            self::approveSuperAdmin(),
            self::rejectSuperAdmin(),
            self::kirimKeDireksi(),
            self::approveDireksi(),
            self::rejectDireksi(),
            self::kirimKeKeuangan(),
            self::reviewKeuangan(),
            self::prosesKeuangan(),
            self::executeKeuangan(),
            self::kirimKePengadaan(),
            self::kirimKeAdmin(),
            self::mulaiProses(),
            self::siapDiambil(),
            self::selesai(),
        ];
    }
}
