<?php

namespace App\Services;

use App\Mail\PengajuanKePengadaanMail;
use App\Mail\PengajuanProjectApprovedDireksiMail;
use App\Mail\PengajuanProjectApprovedPengadaanMail;
use App\Mail\PengajuanProjectPendingDireksiMail;
use App\Mail\PengajuanProjectReadyPickUpMail;
use App\Mail\PengajuanProjectRejectedMail;
use App\Mail\PengajuanProjectSentToAdminMail;
use App\Mail\PengajuanProjectSentToDireksiMail;
use App\Mail\PengajuanProjectSentToKeuanganMail;
use App\Mail\PengajuanProjectSentToPengadaanMail;
use App\Mail\PengajuanProjectSentToPmMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class PengajuanEmailProjectService
{
    public static function sendNotificationWithEmail($record, $status, $additionalData = null)
    {
        $pengaju = $record->user;

        if ($pengaju && $pengaju->email) {
            switch ($status) {
                case 'disetujui_pm_dikirim_ke_pengadaan':
                    Mail::to($pengaju->email)->queue(new PengajuanProjectSentToPengadaanMail($record, 'Project Manager', $additionalData));
                    break;
                case 'ditolak_pm':
                    Mail::to($pengaju->email)->queue(new PengajuanProjectRejectedMail($record, 'Project Manager', $additionalData));
                    break;
                case 'disetujui_pengadaan':
                    Mail::to($pengaju->email)->queue(new PengajuanProjectApprovedPengadaanMail($record, 'Tim Pengadaan', $additionalData));
                    break;
                case 'ditolak_pengadaan':
                    Mail::to($pengaju->email)->queue(new PengajuanProjectRejectedMail($record, 'Tim Pengadaan', $additionalData));
                    break;
                case 'approved_by_direksi':
                    Mail::to($pengaju->email)->queue(new PengajuanProjectApprovedDireksiMail($record, 'Direksi', $additionalData));
                    break;
                case 'reject_direksi':
                    Mail::to($pengaju->email)->queue(new PengajuanProjectRejectedMail($record, 'Direksi', $additionalData));
                    break;
                case 'pending_direksi':
                    Mail::to($pengaju->email)->queue(new PengajuanProjectPendingDireksiMail(
                        $record,
                        $additionalData ?? 'Pengajuan dipending oleh direksi',
                        $record->tanggal_pending ?? now()->toDateString()
                    ));
                    break;
                case 'ready_pickup':
                    Mail::to($pengaju->email)->queue(new PengajuanProjectReadyPickUpMail($record));
                    break;
            }
        }

        PengajuanProjectNotificationService::sendDatabaseNotification($record, $status, $additionalData);
    }

    public static function sendEmailToPm($record)
    {
        try {
            // Kirim email ke Project Manager yang memiliki project ini 
            $projectManager = $record->nameproject->user ?? null;

            if ($projectManager && $projectManager->email) {
                Mail::to($projectManager->email)->queue(new PengajuanProjectSentToPmMail($record));
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function sendEmailToPengadaan($record)
    {
        try {
            $pengadaan = User::whereHas('roles', function ($query) {
                $query->where('name', 'purchasing');
            })->get();

            foreach ($pengadaan as $peng) {
                if ($peng->email) {
                    Mail::to($peng->email)->queue(new PengajuanProjectSentToPengadaanMail($record));
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function sendEmailToDireksi($record)
    {
        try {
            $direksi = User::whereHas('roles', function ($query) {
                $query->where('name', 'direktur_keuangan');
            })->get();

            foreach ($direksi as $dir) {
                if ($dir->email) {
                    Mail::to($dir->email)->queue(new PengajuanProjectSentToDireksiMail($record));
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function sendEmailToKeuangan($record)
    {
        try {
            $keuangan = User::whereHas('roles', function ($query) {
                $query->where('name', 'keuangan');
            })->get();

            foreach ($keuangan as $keu) {
                if ($keu->email) {
                    Mail::to($keu->email)->queue(new PengajuanProjectSentToKeuanganMail($record));
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function sendEmailPendingDireksiToKeuangan($record, $alasanPending, $tanggalPending)
    {
        try {
            $keuangan = User::whereHas('roles', function ($query) {
                $query->where('name', 'keuangan');
            })->get();

            foreach ($keuangan as $keu) {
                if ($keu->email) {
                    Mail::to($keu->email)->queue(new PengajuanProjectPendingDireksiMail(
                        $record,
                        $alasanPending,
                        $tanggalPending
                    ));
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function sendEmailToAdmin($record)
    {
        try {
            $admins = User::whereHas('roles', function ($query) {
                $query->where('name', 'admin');
            })->get();

            foreach ($admins as $admin) {
                if ($admin->email) {
                    Mail::to($admin->email)->queue(new PengajuanProjectSentToAdminMail($record));
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
