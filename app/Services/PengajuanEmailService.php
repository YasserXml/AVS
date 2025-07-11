<?php

namespace App\Services;

use App\Mail\PengajuanApprovedMail;
use App\Mail\PengajuanKeuanganExecuteMail;
use App\Mail\PengajuanReadyPickupMail;
use App\Mail\PengajuanRejectMail;
use App\Mail\PengajuanSentToAdminMail;
use App\Mail\PengajuanSentToDireksiMail;
use App\Mail\PengajuanSentToKeuanganMail;
use App\Mail\PengajuanSentToPengadaanMail;
use App\Mail\PengajuanSentToSuperAdminMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class PengajuanEmailService
{
    public static function sendNotificationWithEmail($record, $status, $additionalData = null)
    {
        $pengaju = $record->user;

        if ($pengaju && $pengaju->email) {
            switch ($status) {
                case 'approved':
                case 'superadmin_approved':
                    Mail::to($pengaju->email)->queue(new PengajuanApprovedMail($record, 'Tim Pengadaan', $additionalData));
                    break;
                case 'rejected':
                case 'superadmin_rejected':
                    Mail::to($pengaju->email)->queue(new PengajuanRejectMail($record, 'Tim Pengadaan', $additionalData));
                    break;
                case 'approved_direksi':
                    Mail::to($pengaju->email)->queue(new PengajuanApprovedMail($record, 'Direksi', $additionalData));
                    break;
                case 'rejected_direksi':
                    Mail::to($pengaju->email)->queue(new PengajuanRejectMail($record, 'Direksi', $additionalData));
                    break;
                case 'execute_keuangan':
                    Mail::to($pengaju->email)->queue(new PengajuanKeuanganExecuteMail($record, $additionalData));
                    break;
                case 'ready_pickup':
                    Mail::to($pengaju->email)->queue(new PengajuanReadyPickupMail($record));
                    break;
            }
        }

        PengajuanNotificationService::sendDatabaseNotification($record, $status, $additionalData);
    }

    public static function sendEmailToSuperAdmin($record)
    {
        try {
            $superadmins = User::whereHas('roles', function ($query) {
                $query->where('name', 'super_admin');
            })->get();

            if ($superadmins->isEmpty()) {
                return;
            }

            foreach ($superadmins as $superadmin) {
                if ($superadmin->email) {
                    Mail::to($superadmin->email)->queue(new PengajuanSentToSuperAdminMail($record));
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
                    Mail::to($dir->email)->queue(new PengajuanSentToDireksiMail($record));
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
                    Mail::to($keu->email)->queue(new PengajuanSentToKeuanganMail($record));
                }
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
                    Mail::to($peng->email)->queue(new PengajuanSentToPengadaanMail($record));
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
                    Mail::to($admin->email)->queue(new PengajuanSentToAdminMail($record));
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
