<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\UserVerificationNotification;
use App\Notifications\UserVerifiedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;

class AdminVerificationController extends Controller
{
    public function verifyUser(Request $request)
    {
        // Validasi signature URL
        if (!$request->hasValidSignature()) {
            return redirect()->route('filament.admin.auth.login')
                ->with('error', 'Tautan verifikasi tidak valid atau telah kadaluarsa.');
        }

        // Ambil data dari request
        $userId = $request->user_id;
        $adminId = $request->admin_id;
        $action = $request->action;

        // Cari user yang akan diverifikasi
        $user = User::find($userId);
        $admin = User::find($adminId);

        if (!$user || !$admin) {
            Log::error('Verifikasi gagal: User atau Admin tidak ditemukan', [
                'user_id' => $userId,
                'admin_id' => $adminId
            ]);
            
            return redirect()->route('filament.admin.auth.login')
                ->with('error', 'Terjadi kesalahan. User atau Admin tidak ditemukan.');
        }

        // Proses verifikasi berdasarkan action
        if ($action === 'verify') {
            // Verifikasi user
            $user->update([
                'admin_verified' => true,
                'verified_by' => $admin->id,
                'verified_at' => now()
            ]);

            // Kirim notifikasi ke user
            $user->notify(new UserVerifiedNotification(true));

            // Tampilkan notifikasi sukses
            Notification::make()
                ->title('Verifikasi Berhasil')
                ->body('Pengguna ' . $user->name . ' telah berhasil diverifikasi.')
                ->success()
                ->send();

            return redirect()->route('filament.admin.pages.dashboard')
                ->with('success', 'Pengguna berhasil diverifikasi dan notifikasi telah dikirim.');

        } elseif ($action === 'reject') {
            // Kirim notifikasi penolakan ke user
            $user->notify(new UserVerifiedNotification(false));

            // Hapus user yang ditolak (opsional, bisa juga hanya ditandai sebagai ditolak)
            // $user->delete(); // Uncomment jika ingin langsung menghapus

            // Atau tandai sebagai ditolak
            $user->update([
                'admin_verified' => false,
                'rejected_by' => $admin->id,
                'rejected_at' => now()
            ]);

            // Tampilkan notifikasi sukses
            Notification::make()
                ->title('Penolakan Berhasil')
                ->body('Pengguna ' . $user->name . ' telah ditolak dan diberitahu melalui email.')
                ->success()
                ->send();

            return redirect()->route('filament.admin.pages.dashboard')
                ->with('success', 'Pengguna telah ditolak dan notifikasi telah dikirim.');
        }

        return redirect()->route('filament.admin.auth.login')
            ->with('error', 'Terjadi kesalahan. Action tidak valid.');
    }
}