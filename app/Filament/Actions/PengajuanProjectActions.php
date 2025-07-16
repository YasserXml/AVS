<?php

namespace App\Filament\Actions;

use App\Services\PengajuanEmailProjectService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;

class PengajuanProjectActions
{
    public static function terimaDanReview()
    {
        return Action::make('terima_dan_review')
            ->label('Terima & Review')
            ->icon('heroicon-o-eye')
            ->color('primary')
            ->visible(function ($record) {
                $user = filament()->auth()->user();
                return $record->status === 'pengajuan_terkirim' &&
                    $record->nameproject &&
                    $record->nameproject->user_id === $user->id;
            })
            ->requiresConfirmation()
            ->modalHeading('Terima dan Review Pengajuan')
            ->modalDescription(function ($record) {
                return 'Apakah Anda yakin ingin menerima dan memulai review pengajuan untuk project: ' . $record->nameproject->nama_project . '?';
            })
            ->action(function ($record) {
                $record->update([
                    'status' => 'pending_pm_review',
                ]);
                $record->addStatusHistory(
                    'pending_pm_review',
                    filament()->auth()->id(),
                    'Pengajuan diterima dan sedang direview oleh PM'
                );

                PengajuanEmailProjectService::sendNotificationWithEmail($record, 'pending_pm_review');

                Notification::make()
                    ->title('Berhasil')
                    ->body('Pengajuan berhasil diterima dan sedang dalam review.')
                    ->success()
                    ->send();
            });
    }

    public static function setujuiDanKirimKePengadaan()
    {
        return Action::make('setujui_dan_kirim_ke_pengadaan')
            ->label('Setujui & Kirim ke Pengadaan')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('success')
            ->visible(function ($record) {
                $user = filament()->auth()->user();
                return $record->status === 'pending_pm_review' &&
                    $record->nameproject &&
                    $record->nameproject->user_id === $user->id;
            })
            ->form([
                Textarea::make('catatan')
                    ->label('Catatan PM')
                    ->placeholder('Berikan catatan atau rekomendasi untuk tim pengadaan...')
                    ->rows(3)
                    ->helperText('Catatan ini akan membantu tim pengadaan dalam memproses pengajuan.'),
            ])
            ->requiresConfirmation()
            ->modalHeading('Setujui dan Kirim ke Pengadaan')
            ->modalDescription(function ($record) {
                return 'Apakah Anda yakin ingin menyetujui pengajuan untuk project: ' . $record->nameproject->nama_project . ' dan mengirimnya ke tim pengadaan?';
            })
            ->action(function ($record, array $data) {
                $record->update([
                    'status' => 'disetujui_pm_dikirim_ke_pengadaan',
                    'approved_by' => filament()->auth()->id(),
                    'approved_at' => now(),
                    'catatan' => $data['catatan'] ?? null,
                ]);
                $record->addStatusHistory(
                    'disetujui_pm_dikirim_ke_pengadaan',
                    filament()->auth()->id(),
                    'Pengajuan disetujui PM dan dikirim ke pengadaan' .
                        ($data['catatan'] ? '. Catatan: ' . $data['catatan'] : '')
                );

                PengajuanEmailProjectService::sendNotificationWithEmail($record, 'disetujui_pm_dikirim_ke_pengadaan', $data['catatan']);
                PengajuanEmailProjectService::sendEmailToPengadaan($record);

                Notification::make()
                    ->title('Berhasil')
                    ->body('Pengajuan berhasil disetujui dan dikirim ke tim pengadaan.')
                    ->success()
                    ->send();
            });
    }

    public static function tolakPengajuan()
    {
        return Action::make('tolak_pengajuan')
            ->label('Tolak')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->visible(function ($record) {
                $user = filament()->auth()->user();
                return $record->status === 'pending_pm_review' &&
                    $record->nameproject &&
                    $record->nameproject->user_id === $user->id;
            })
            ->form([
                Textarea::make('alasan_penolakan')
                    ->label('Alasan Penolakan')
                    ->required()
                    ->placeholder('Jelaskan mengapa pengajuan ini ditolak...')
                    ->rows(3)
                    ->helperText('Berikan alasan yang jelas agar pengaju dapat memahami dan memperbaiki pengajuan.'),
            ])
            ->requiresConfirmation()
            ->modalHeading('Tolak Pengajuan')
            ->modalDescription(function ($record) {
                return 'Apakah Anda yakin ingin menolak pengajuan untuk project: ' . $record->nameproject->nama_project . '?';
            })
            ->action(function ($record, array $data) {
                $record->update([
                    'status' => 'ditolak_pm',
                    'rejected_by' => filament()->auth()->id(),
                    'rejected_at' => now(),
                    'reject_reason' => $data['alasan_penolakan'],
                ]);

                $record->addStatusHistory(
                    'ditolak_pm',
                    filament()->auth()->id(),
                    'Pengajuan ditolak oleh PM: ' . $data['alasan_penolakan']
                );

                PengajuanEmailProjectService::sendNotificationWithEmail($record, 'ditolak_pm', $data['alasan_penolakan']);

                Notification::make()
                    ->title('Pengajuan Ditolak')
                    ->body('Pengajuan berhasil ditolak.')
                    ->warning()
                    ->send();
            });
    }

    public static function setujuiPengadaan()
    {
        return Action::make('setujui_pengadaan')
            ->label('Setujui')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->visible(function ($record) {
                $user = filament()->auth()->user();
                return $record->status === 'disetujui_pm_dikirim_ke_pengadaan' &&
                    $user->hasRole('purchasing');
            })
            ->form([
                Textarea::make('catatan')
                    ->label('Catatan Pengadaan')
                    ->placeholder('Tambahkan catatan persetujuan dari tim pengadaan...')
                    ->rows(3),
            ])
            ->requiresConfirmation()
            ->modalHeading('Setujui Pengajuan')
            ->modalDescription(function ($record) {
                return 'Apakah Anda yakin ingin menyetujui pengajuan untuk project: ' . $record->nameproject->nama_project . '?';
            })
            ->action(function ($record, array $data) {
                $record->update([
                    'status' => 'disetujui_pengadaan',
                    'approved_by' => filament()->auth()->id(),
                    'approved_at' => now(),
                    'catatan' => $data['catatan'] ?? null,
                ]);

                $record->addStatusHistory(
                    'disetujui_pengadaan',
                    filament()->auth()->id(),
                    'Pengajuan disetujui oleh Tim Pengadaan' .
                        ($data['catatan'] ? '. Catatan: ' . $data['catatan'] : '')
                );

                PengajuanEmailProjectService::sendNotificationWithEmail($record, 'disetujui_pengadaan', $data['catatan']);

                Notification::make()
                    ->title('Berhasil')
                    ->body('Pengajuan berhasil disetujui oleh Tim Pengadaan.')
                    ->success()
                    ->send();
            });
    }

    public static function tolakPengadaan()
    {
        return Action::make('tolak_pengadaan')
            ->label('Tolak')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->visible(function ($record) {
                $user = filament()->auth()->user();
                return $record->status === 'disetujui_pm_dikirim_ke_pengadaan' &&
                    $user->hasRole('purchasing');
            })
            ->form([
                Textarea::make('alasan_penolakan_pengadaan')
                    ->label('Alasan Penolakan')
                    ->required()
                    ->placeholder('Jelaskan mengapa pengajuan ini ditolak oleh tim pengadaan...')
                    ->rows(3)
                    ->helperText('Berikan alasan yang jelas untuk penolakan.'),
            ])
            ->requiresConfirmation()
            ->modalHeading('Tolak Pengajuan')
            ->modalDescription(function ($record) {
                return 'Apakah Anda yakin ingin menolak pengajuan untuk project: ' . $record->nameproject->nama_project . '?';
            })
            ->action(function ($record, array $data) {
                $record->update([
                    'status' => 'ditolak_pengadaan',
                    'rejected_by' => filament()->auth()->id(),
                    'rejected_at' => now(),
                    'reject_reason' => $data['alasan_penolakan_pengadaan'],
                ]);

                $record->addStatusHistory(
                    'ditolak_pengadaan',
                    filament()->auth()->id(),
                    'Pengajuan ditolak oleh Tim Pengadaan: ' . $data['alasan_penolakan_pengadaan']
                );

                PengajuanEmailProjectService::sendNotificationWithEmail($record, 'ditolak_pengadaan', $data['alasan_penolakan_pengadaan']);

                Notification::make()
                    ->title('Pengajuan Ditolak')
                    ->body('Pengajuan berhasil ditolak oleh Tim Pengadaan.')
                    ->warning()
                    ->send();
            });
    }

    public static function kirimKeDireksi()
    {
        return Action::make('kirim_ke_direksi')
            ->label('Kirim ke Direksi')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('warning')
            ->visible(function ($record) {
                $user = filament()->auth()->user();
                return $record->status === 'disetujui_pengadaan' &&
                    $user->hasRole('purchasing');
            })
            ->requiresConfirmation()
            ->modalHeading('Kirim ke Direksi')
            ->modalDescription(function ($record) {
                return 'Apakah Anda yakin ingin mengirim pengajuan untuk project: ' . $record->nameproject->nama_project . ' ke direksi?';
            })
            ->action(function ($record) {
                $record->update([
                    'status' => 'pengajuan_dikirim_ke_direksi',
                ]);

                $record->addStatusHistory(
                    'pengajuan_dikirim_ke_direksi',
                    filament()->auth()->id(),
                    'Pengajuan dikirim ke direksi'
                );

                PengajuanEmailProjectService::sendNotificationWithEmail($record, 'pengajuan_dikirim_ke_direksi');
                PengajuanEmailProjectService::sendEmailToDireksi($record);

                Notification::make()
                    ->title('Berhasil')
                    ->body('Pengajuan berhasil dikirim ke direksi.')
                    ->success()
                    ->send();
            });
    }

    public static function approveDireksi()
    {
        return Action::make('approve_direksi')
            ->label('Setujui')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->visible(function ($record) {
                $user = filament()->auth()->user();
                return $record->status === 'pengajuan_dikirim_ke_direksi' &&
                    $user->hasRole('direktur_keuangan');
            })
            ->form([
                Textarea::make('catatan')
                    ->label('Catatan Direksi')
                    ->placeholder('Tambahkan catatan persetujuan dari direksi...')
                    ->rows(3)
                    ->helperText('Catatan ini akan dikirim ke tim terkait.'),
            ])
            ->requiresConfirmation()
            ->modalHeading('Setujui Pengajuan')
            ->modalDescription(function ($record) {
                return 'Apakah Anda yakin ingin menyetujui pengajuan untuk project: ' . $record->nameproject->nama_project . '?';
            })
            ->action(function ($record, array $data) {
                $record->update([
                    'status' => 'approved_by_direksi',
                    'approved_by' => filament()->auth()->id(),
                    'approved_at' => now(),
                    'catatan' => $data['catatan'] ?? null,
                ]);

                $record->addStatusHistory(
                    'approved_by_direksi',
                    filament()->auth()->id(),
                    'Pengajuan disetujui oleh direksi' .
                        ($data['catatan'] ? '. Catatan: ' . $data['catatan'] : '')
                );

                PengajuanEmailProjectService::sendNotificationWithEmail($record, 'approved_by_direksi', $data['catatan']);

                Notification::make()
                    ->title('Berhasil')
                    ->body('Pengajuan berhasil disetujui oleh direksi.')
                    ->success()
                    ->send();
            });
    }

    public static function rejectDireksi()
    {
        return Action::make('reject_direksi')
            ->label('Tolak')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->visible(function ($record) {
                $user = filament()->auth()->user();
                return $record->status === 'pengajuan_dikirim_ke_direksi' &&
                    $user->hasRole('direktur_keuangan');
            })
            ->form([
                Textarea::make('alasan_penolakan_direksi')
                    ->label('Alasan Penolakan')
                    ->required()
                    ->placeholder('Jelaskan mengapa pengajuan ini ditolak oleh direksi...')
                    ->rows(3)
                    ->helperText('Berikan alasan yang jelas untuk penolakan.'),
            ])
            ->requiresConfirmation()
            ->modalHeading('Tolak Pengajuan')
            ->modalDescription(function ($record) {
                return 'Apakah Anda yakin ingin menolak pengajuan untuk project: ' . $record->nameproject->nama_project . '?';
            })
            ->action(function ($record, array $data) {
                $record->update([
                    'status' => 'reject_direksi',
                    'rejected_by' => filament()->auth()->id(),
                    'rejected_at' => now(),
                    'reject_reason' => $data['alasan_penolakan_direksi'],
                ]);

                $record->addStatusHistory(
                    'reject_direksi',
                    filament()->auth()->id(),
                    'Pengajuan ditolak oleh direksi: ' . $data['alasan_penolakan_direksi']
                );

                PengajuanEmailProjectService::sendNotificationWithEmail($record, 'reject_direksi', $data['alasan_penolakan_direksi']);

                Notification::make()
                    ->title('Pengajuan Ditolak')
                    ->body('Pengajuan berhasil ditolak oleh direksi.')
                    ->warning()
                    ->send();
            });
    }

    public static function kirimKeKeuangan()
    {
        return Action::make('kirim_ke_keuangan')
            ->label('Kirim ke Keuangan')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('warning')
            ->visible(function ($record) {
                $user = filament()->auth()->user();
                return $record->status === 'approved_by_direksi' &&
                    $user->hasRole('direktur_keuangan');
            })
            ->requiresConfirmation()
            ->modalHeading('Kirim ke Keuangan')
            ->modalDescription(function ($record) {
                return 'Apakah Anda yakin ingin mengirim pengajuan untuk project: ' . $record->nameproject->nama_project . ' ke keuangan?';
            })
            ->action(function ($record) {
                $record->update([
                    'status' => 'pengajuan_dikirim_ke_keuangan',
                ]);

                $record->addStatusHistory(
                    'pengajuan_dikirim_ke_keuangan',
                    filament()->auth()->id(),
                    'Pengajuan dikirim ke keuangan'
                );

                PengajuanEmailProjectService::sendNotificationWithEmail($record, 'pengajuan_dikirim_ke_keuangan');
                PengajuanEmailProjectService::sendEmailToKeuangan($record);

                Notification::make()
                    ->title('Berhasil')
                    ->body('Pengajuan berhasil dikirim ke keuangan.')
                    ->success()
                    ->send();
            });
    }

    public static function reviewKeuangan()
    {
        return Action::make('review_keuangan')
            ->label('Mulai Review')
            ->icon('heroicon-o-play')
            ->color('primary')
            ->visible(function ($record) {
                $user = filament()->auth()->user();
                return $record->status === 'pengajuan_dikirim_ke_keuangan' &&
                    $user->hasRole('keuangan');
            })
            ->requiresConfirmation()
            ->modalHeading('Mulai Review Keuangan')
            ->modalDescription(function ($record) {
                return 'Apakah Anda yakin ingin memulai review keuangan untuk project: ' . $record->nameproject->nama_project . '?';
            })
            ->action(function ($record) {
                $record->update([
                    'status' => 'pending_keuangan',
                ]);
                $record->addStatusHistory(
                    'pending_keuangan',
                    filament()->auth()->id(),
                    'Review keuangan dimulai'
                );

                PengajuanEmailProjectService::sendNotificationWithEmail($record, 'pending_keuangan');

                Notification::make()
                    ->title('Berhasil')
                    ->body('Review keuangan telah dimulai.')
                    ->success()
                    ->send();
            });
    }

    public static function prosesKeuangan()
    {
        return Action::make('proses_keuangan')
            ->label('Proses Keuangan')
            ->icon('heroicon-o-cog')
            ->color('warning')
            ->visible(function ($record) {
                $user = filament()->auth()->user();
                return $record->status === 'pending_keuangan' &&
                    $user->hasRole('keuangan');
            })
            ->requiresConfirmation()
            ->modalHeading('Proses Keuangan')
            ->modalDescription(function ($record) {
                return 'Apakah Anda yakin ingin memproses pengajuan untuk project: ' . $record->nameproject->nama_project . '?';
            })
            ->action(function ($record) {
                $record->update([
                    'status' => 'process_keuangan',
                ]);

                $record->addStatusHistory(
                    'process_keuangan',
                    filament()->auth()->id(),
                    'Pengajuan sedang diproses oleh keuangan'
                );

                PengajuanEmailProjectService::sendNotificationWithEmail($record, 'process_keuangan');

                Notification::make()
                    ->title('Berhasil')
                    ->body('Pengajuan sedang diproses.')
                    ->success()
                    ->send();
            });
    }

    public static function executeKeuangan()
    {
        return Action::make('execute_keuangan')
            ->label('Selesai Proses')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->visible(function ($record) {
                $user = filament()->auth()->user();
                return $record->status === 'process_keuangan' &&
                    $user->hasRole('keuangan');
            })
            ->form([
                Textarea::make('catatan')
                    ->label('Catatan Keuangan')
                    ->placeholder('Tambahkan catatan proses keuangan...')
                    ->rows(3)
                    ->helperText('Catatan tentang proses keuangan yang telah selesai.'),
            ])
            ->requiresConfirmation()
            ->modalHeading('Selesai Proses Keuangan')
            ->modalDescription(function ($record) {
                return 'Apakah proses keuangan untuk project: ' . $record->nameproject->nama_project . ' telah selesai?';
            })
            ->action(function ($record, array $data) {
                $record->update([
                    'status' => 'execute_keuangan',
                    'approved_by' => filament()->auth()->id(),
                    'approved_at' => now(),
                    'catatan' => $data['catatan'] ?? null,
                ]);
                $record->addStatusHistory(
                    'execute_keuangan',
                    filament()->auth()->id(),
                    'Proses keuangan selesai' .
                        ($data['catatan'] ? '. Catatan: ' . $data['catatan'] : '')
                );

                PengajuanEmailProjectService::sendNotificationWithEmail($record, 'execute_keuangan', $data['catatan']);

                Notification::make()
                    ->title('Berhasil')
                    ->body('Proses keuangan telah selesai.')
                    ->success()
                    ->send();
            });
    }

    public static function kirimKembaliKePengadaan()
    {
        return Action::make('kirim_kembali_ke_pengadaan')
            ->label('Kirim ke Pengadaan')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('warning')
            ->visible(function ($record) {
                $user = filament()->auth()->user();
                return $record->status === 'execute_keuangan' &&
                    $user->hasRole('keuangan');
            })
            ->requiresConfirmation()
            ->modalHeading('Kirim ke Pengadaan')
            ->modalDescription(function ($record) {
                return 'Apakah Anda yakin ingin mengirim pengajuan untuk project: ' . $record->nameproject->nama_project . ' ke pengadaan?';
            })
            ->action(function ($record) {
                $record->update([
                    'status' => 'pengajuan_dikirim_ke_pengadaan_final',
                ]);

                $record->addStatusHistory(
                    'pengajuan_dikirim_ke_pengadaan_final',
                    filament()->auth()->id(),
                    'Pengajuan dikirim kembali ke pengadaan setelah proses keuangan selesai'
                );

                PengajuanEmailProjectService::sendNotificationWithEmail($record, 'pengajuan_dikirim_ke_pengadaan_final');
                PengajuanEmailProjectService::sendEmailToPengadaan($record);

                Notification::make()
                    ->title('Berhasil')
                    ->body('Pengajuan berhasil dikirim ke pengadaan.')
                    ->success()
                    ->send();
            });
    }

    public static function kirimKeAdmin()
    {
        return Action::make('kirim_ke_admin')
            ->label('Kirim ke Admin')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('warning')
            ->visible(function ($record) {
                $user = filament()->auth()->user();
                return $record->status === 'pengajuan_dikirim_ke_pengadaan_final' &&
                    $user->hasRole('purchasing');
            })
            ->requiresConfirmation()
            ->modalHeading('Kirim ke Admin')
            ->modalDescription(function ($record) {
                return 'Apakah Anda yakin ingin mengirim pengajuan untuk project: ' . $record->nameproject->nama_project . ' ke admin?';
            })
            ->action(function ($record) {
                $record->update([
                    'status' => 'pengajuan_dikirim_ke_admin',
                ]);

                $record->addStatusHistory(
                    'pengajuan_dikirim_ke_admin',
                    filament()->auth()->id(),
                    'Pengajuan dikirim ke admin'
                );

                PengajuanEmailProjectService::sendNotificationWithEmail($record, 'pengajuan_dikirim_ke_admin');
                PengajuanEmailProjectService::sendEmailToAdmin($record);

                Notification::make()
                    ->title('Berhasil')
                    ->body('Pengajuan berhasil dikirim ke admin.')
                    ->success()
                    ->send();
            });
    }

    public static function mulaiProsesPengadaan()
    {
        return Action::make('mulai_proses_pengadaan')
            ->label('Mulai Proses')
            ->icon('heroicon-o-play')
            ->color('warning')
            ->visible(function ($record) {
                $user = filament()->auth()->user();

                return $record->status === 'pengajuan_dikirim_ke_admin' &&
                    $user->hasRole('admin');
            })
            ->requiresConfirmation()
            ->modalHeading('Mulai Proses Pengadaan')
            ->modalDescription(function ($record) {
                return 'Apakah Anda yakin ingin memulai proses untuk project: ' . $record->nameproject->nama_project . '?';
            })
            ->action(function ($record) {
                $record->update([
                    'status' => 'processing',
                ]);

                $record->addStatusHistory(
                    'processing',
                    filament()->auth()->id(),
                    'Proses pengajuan dimulai'
                );

                PengajuanEmailProjectService::sendNotificationWithEmail($record, 'processing');

                Notification::make()
                    ->title('Berhasil')
                    ->body('Proses pengajuan telah dimulai.')
                    ->success()
                    ->send();
            });
    }

    public static function siapDiambil()
    {
        return Action::make('siap_diambil')
            ->label('Siap Diambil')
            ->icon('heroicon-o-inbox-arrow-down')
            ->color('info')
            ->visible(function ($record) {
                $user = filament()->auth()->user();

                return $record->status === 'processing' &&
                    $user->hasRole('admin');
            })
            ->requiresConfirmation()
            ->modalHeading('Siap Diambil')
            ->modalDescription(function ($record) {
                return 'Apakah barang untuk project: ' . $record->nameproject->nama_project . ' sudah siap diambil?';
            })
            ->action(function ($record) {
                $record->update([
                    'status' => 'ready_pickup',
                ]);

                $record->addStatusHistory(
                    'ready_pickup',
                    filament()->auth()->id(),
                    'Barang siap diambil'
                );

                PengajuanEmailProjectService::sendNotificationWithEmail($record, 'ready_pickup');

                Notification::make()
                    ->title('Berhasil')
                    ->body('Status berhasil diubah menjadi siap diambil.')
                    ->success()
                    ->send();
            });
    }

    public static function selesai()
    {
        return Action::make('selesai')
            ->label('Selesai')
            ->icon('heroicon-o-check-badge')
            ->color('success')
            ->visible(function ($record) {
                $user = filament()->auth()->user();
                return $record->status === 'ready_pickup' &&
                    $user->hasRole('admin');
            })
            ->form([
                TextInput::make('received_by')
                    ->label('Diterima Oleh')
                    ->required()
                    ->helperText('Masukkan nama penerima barang'),
                Textarea::make('catatan')
                    ->label('Catatan Penerimaan')
                    ->rows(3)
                    ->placeholder('Tambahkan catatan penerimaan...'),
            ])
            ->requiresConfirmation()
            ->modalHeading('Selesai')
            ->modalDescription(function ($record) {
                return 'Apakah pengajuan untuk project: ' . $record->nameproject->nama_project . ' sudah selesai?';
            })
            ->action(function ($record, array $data) {
                $record->update([
                    'status' => 'completed',
                    'received_by' => filament()->auth()->id(),
                    'received_by_name' => $data['received_by'],
                    'catatan' => $data['catatan'] ?? null,
                ]);

                $record->addStatusHistory(
                    'completed',
                    filament()->auth()->id(),
                    'Pengajuan selesai dan barang telah diserahkan kepada: ' . $data['received_by'] .
                        ($data['catatan'] ? '. Catatan: ' . $data['catatan'] : '')
                );

                PengajuanEmailProjectService::sendNotificationWithEmail($record, 'completed', $data['received_by']);

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
            self::terimaDanReview(),
            self::setujuiDanKirimKePengadaan(),
            self::tolakPengajuan(),
            self::setujuiPengadaan(),
            self::tolakPengadaan(),
            self::kirimKeDireksi(),
            self::approveDireksi(),
            self::rejectDireksi(),
            self::kirimKeKeuangan(),
            self::reviewKeuangan(),
            self::prosesKeuangan(),
            self::executeKeuangan(),
            self::kirimKembaliKePengadaan(),
            self::kirimKeAdmin(),
            self::mulaiProsesPengadaan(),
            self::siapDiambil(),
            self::selesai(),
        ];
    }
}
