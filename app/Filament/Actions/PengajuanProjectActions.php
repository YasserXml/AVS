<?php
namespace App\Filament\Actions;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;

class PengajuanProjectActions
{
    /**
     * Action untuk PM menerima dan mulai review pengajuan
     */
    public static function terimaDanReview()
    {
        return Action::make('terima_dan_review')
            ->label('Terima & Review')
            ->icon('heroicon-o-eye')
            ->color('primary')
            ->visible(function ($record) {
                $user = filament()->auth()->user();
                
                // Hanya PM dari project yang bisa melihat action ini
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

                Notification::make()
                    ->title('Berhasil')
                    ->body('Pengajuan berhasil diterima dan sedang dalam review.')
                    ->success()
                    ->send();
            });
    }

    /**
     * Action untuk PM menyetujui dan mengirim ke pengadaan
     */
    public static function setujuiDanKirimKePengadaan()
    {
        return Action::make('setujui_dan_kirim_ke_pengadaan')
            ->label('Setujui & Kirim ke Pengadaan')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('success')
            ->visible(function ($record) {
                $user = filament()->auth()->user();
                
                // Hanya PM dari project yang bisa melihat action ini
                return $record->status === 'pending_pm_review' && 
                       $record->nameproject && 
                       $record->nameproject->user_id === $user->id;
            })
            ->form([
                Textarea::make('catatan_pm')
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
                    'pm_approved_by' => filament()->auth()->id(),
                    'pm_approved_at' => now(),
                    'pm_notes' => $data['catatan_pm'] ?? null,
                ]);

                $record->addStatusHistory(
                    'disetujui_pm_dikirim_ke_pengadaan',
                    filament()->auth()->id(),
                    'Pengajuan disetujui PM dan dikirim ke pengadaan' . 
                    ($data['catatan_pm'] ? '. Catatan: ' . $data['catatan_pm'] : '')
                );

                Notification::make()
                    ->title('Berhasil')
                    ->body('Pengajuan berhasil disetujui dan dikirim ke tim pengadaan.')
                    ->success()
                    ->send();
            });
    }

    /**
     * Action untuk PM menolak pengajuan
     */
    public static function tolakPengajuan()
    {
        return Action::make('tolak_pengajuan')
            ->label('Tolak')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->visible(function ($record) {
                $user = filament()->auth()->user();
                
                // Hanya PM dari project yang bisa melihat action ini
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
                    'rejected_by_role' => 'pm',
                ]);

                $record->addStatusHistory(
                    'ditolak_pm',
                    filament()->auth()->id(),
                    'Pengajuan ditolak oleh PM: ' . $data['alasan_penolakan']
                );

                Notification::make()
                    ->title('Pengajuan Ditolak')
                    ->body('Pengajuan berhasil ditolak.')
                    ->warning()
                    ->send();
            });
    }

    /**
     * Action untuk Pengadaan (Super Admin) menyetujui pengajuan
     */
    public static function setujuiPengadaan()
    {
        return Action::make('setujui_pengadaan')
            ->label('Setujui')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->visible(function ($record) {
                $user = filament()->auth()->user();
                
                // Hanya Super Admin (Pengadaan) yang bisa melihat action ini
                return $record->status === 'disetujui_pm_dikirim_ke_pengadaan' && 
                       $user->hasRole('super_admin');
            })
            ->form([
                Textarea::make('catatan_pengadaan')
                    ->label('Catatan Pengadaan')
                    ->placeholder('Tambahkan catatan persetujuan dari tim pengadaan...')
                    ->rows(3)
                    ->helperText('Catatan ini akan dikirim ke PM dan pengaju.'),
                
                DatePicker::make('estimasi_pengadaan')
                    ->label('Estimasi Waktu Pengadaan')
                    ->helperText('Perkiraan kapan barang akan tersedia')
                    ->native(false)
                    ->displayFormat('d F Y')
                    ->prefixIcon('heroicon-o-calendar'),
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
                    'procurement_notes' => $data['catatan_pengadaan'] ?? null,
                    'estimated_procurement_date' => $data['estimasi_pengadaan'] ?? null,
                ]);

                $record->addStatusHistory(
                    'disetujui_pengadaan',
                     filament()->auth()->id(),
                    'Pengajuan disetujui oleh Tim Pengadaan' . 
                    ($data['catatan_pengadaan'] ? '. Catatan: ' . $data['catatan_pengadaan'] : '') .
                    ($data['estimasi_pengadaan'] ? '. Estimasi: ' . $data['estimasi_pengadaan'] : '')
                );

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
                
                // Hanya Super Admin (Pengadaan) yang bisa melihat action ini
                return $record->status === 'disetujui_pm_dikirim_ke_pengadaan' && 
                       $user->hasRole('super_admin');
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
                    'rejected_by_role' => 'pengadaan',
                    'rejection_category' => $data['kategori_penolakan'],
                ]);

                $record->addStatusHistory(
                    'ditolak_pengadaan',
                    filament()->auth()->id(),
                    'Pengajuan ditolak oleh Tim Pengadaan (' . $data['kategori_penolakan'] . '): ' . $data['alasan_penolakan_pengadaan']
                );

                Notification::make()
                    ->title('Pengajuan Ditolak')
                    ->body('Pengajuan berhasil ditolak oleh Tim Pengadaan.')
                    ->warning()
                    ->send();
            });
    }

    public static function mulaiProsesPengadaan()
    {
        return Action::make('mulai_proses_pengadaan')
            ->label('Mulai Proses Pengadaan')
            ->icon('heroicon-o-cog')
            ->color('warning')
            ->visible(function ($record) {
                $user = filament()->auth()->user();
                
                // Hanya Super Admin (Pengadaan) yang bisa melihat action ini
                return $record->status === 'disetujui_pengadaan' && 
                       $user->hasRole('super_admin');
            })
            ->form([
                TextInput::make('nomor_po')
                    ->label('Nomor PO/PR')
                    ->placeholder('Masukkan nomor Purchase Order atau Purchase Request')
                    ->helperText('Nomor referensi untuk tracking pengadaan.'),
                
                Textarea::make('catatan_proses')
                    ->label('Catatan Proses')
                    ->placeholder('Tambahkan catatan tentang proses pengadaan...')
                    ->rows(3),
            ])
            ->requiresConfirmation()
            ->modalHeading('Mulai Proses Pengadaan')
            ->modalDescription(function ($record) {
                return 'Apakah Anda yakin ingin memulai proses pengadaan untuk project: ' . $record->nameproject->nama_project . '?';
            })
            ->action(function ($record, array $data) {
                $record->update([
                    'status' => 'dalam_proses_pengadaan',
                    'po_number' => $data['nomor_po'] ?? null,
                    'procurement_process_notes' => $data['catatan_proses'] ?? null,
                    'procurement_started_at' => now(),
                ]);

                $record->addStatusHistory(
                    'dalam_proses_pengadaan',
                    filament()->auth()->id(),
                    'Proses pengadaan dimulai' . 
                    ($data['nomor_po'] ? '. PO/PR: ' . $data['nomor_po'] : '') .
                    ($data['catatan_proses'] ? '. Catatan: ' . $data['catatan_proses'] : '')
                );

                Notification::make()
                    ->title('Berhasil')
                    ->body('Proses pengadaan berhasil dimulai.')
                    ->success()
                    ->send();
            });
    }

    /**
     * Action untuk Pengadaan menyelesaikan proses pengadaan
     */
    public static function selesaiProsesPengadaan()
    {
        return Action::make('selesai_proses_pengadaan')
            ->label('Selesai - Siap Diserahkan')
            ->icon('heroicon-o-check-badge')
            ->color('success')
            ->visible(function ($record) {
                $user = filament()->auth()->user();
                
                // Hanya Super Admin (Pengadaan) yang bisa melihat action ini
                return $record->status === 'dalam_proses_pengadaan' && 
                       $user->hasRole('super_admin');
            })
            ->form([
                Textarea::make('catatan_selesai')
                    ->label('Catatan Penyelesaian')
                    ->placeholder('Tambahkan catatan tentang penyelesaian pengadaan...')
                    ->rows(3)
                    ->helperText('Informasi tentang barang yang sudah tersedia dan siap diserahkan.'),
                
                TextInput::make('lokasi_penyerahan')
                    ->label('Lokasi Penyerahan')
                    ->placeholder('Dimana barang bisa diambil/diserahkan')
                    ->helperText('Lokasi dimana PM atau tim project bisa mengambil barang.'),
            ])
            ->requiresConfirmation()
            ->modalHeading('Selesai - Siap Diserahkan')
            ->modalDescription(function ($record) {
                return 'Apakah pengadaan untuk project: ' . $record->nameproject->nama_project . ' sudah selesai dan siap diserahkan?';
            })
            ->action(function ($record, array $data) {
                $record->update([
                    'status' => 'siap_diserahkan',
                    'procurement_completed_at' => now(),
                    'completion_notes' => $data['catatan_selesai'] ?? null,
                    'handover_location' => $data['lokasi_penyerahan'] ?? null,
                ]);

                $record->addStatusHistory(
                    'siap_diserahkan',
                    filament()->auth()->id(),
                    'Pengadaan selesai dan siap diserahkan' . 
                    ($data['lokasi_penyerahan'] ? '. Lokasi: ' . $data['lokasi_penyerahan'] : '') .
                    ($data['catatan_selesai'] ? '. Catatan: ' . $data['catatan_selesai'] : '')
                );

                Notification::make()
                    ->title('Berhasil')
                    ->body('Pengadaan selesai dan siap diserahkan.')
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
            self::mulaiProsesPengadaan(),
            self::selesaiProsesPengadaan(),
        ];
    }
}