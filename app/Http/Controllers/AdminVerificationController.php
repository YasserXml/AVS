<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\UserVerifiedNotification;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;

class AdminVerificationController extends Controller
{
    public function verifyUser(Request $request, User $user)
    {
        // Validasi token
        if (sha1($user->email) !== $request->token) {
            return redirect()->route('filament.admin.pages.dashboard')
                ->with('error', 'Token verifikasi tidak valid');
        }

        // Update status verifikasi
        $user->admin_verified = true;
        $user->save();

        // Kirim notifikasi ke user
        $user->notify(new UserVerifiedNotification());

        Notification::make()
            ->title('Pengguna Berhasil Diverifikasi')
            ->body('Pengguna ' . $user->name . ' berhasil diverifikasi dan sekarang dapat masuk ke sistem.')
            ->success()
            ->send();

        return redirect()->route('filament.admin.pages.dashboard')
            ->with('success', 'Pengguna berhasil diverifikasi');
    }

    public function rejectUser(Request $request, User $user)
    {
        // Validasi token
        if (sha1($user->email) !== $request->token) {
            return redirect()->route('filament.admin.pages.dashboard')
                ->with('error', 'Token verifikasi tidak valid');
        }

        // Hapus user
        $user->delete();

        Notification::make()
            ->title('Pendaftaran Pengguna Ditolak')
            ->body('Pengguna ' . $user->name . ' telah ditolak dan dihapus dari sistem.')
            ->success()
            ->send();

        return redirect()->route('filament.admin.pages.dashboard')
            ->with('success', 'Pengguna berhasil ditolak');
    }
}