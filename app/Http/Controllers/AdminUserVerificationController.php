<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\UserVerificationStatusEmail;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AdminUserVerificationController extends Controller
{
    /**
     * Verifikasi pengguna
     */
    public function verifyUser(Request $request, $userId)
    {
        // Validasi signature URL
        if (!$request->hasValidSignature()) {
            return redirect()->route('filament.admin.resources.users.index')
                ->with('error', 'Link verifikasi tidak valid atau sudah kadaluarsa.');
        }

        try {
            $user = User::findOrFail($userId);
            
            // Update status verifikasi
            $user->update([
                'admin_verified' => true,
                'email_verified_at' => $user->email_verified_at ?? now(), // Verifikasi email juga jika belum
            ]);
            
            // Kirim email ke pengguna bahwa akunnya telah diverifikasi
            try {
                Mail::to($user->email)->send(new UserVerificationStatusEmail($user, true));
            } catch (\Exception $e) {
                Log::error('Gagal mengirim email verifikasi: ' . $e->getMessage());
            }
            
            // Notifikasi sukses
            Notification::make()
                ->title('Verifikasi Berhasil')
                ->body('Pengguna ' . $user->name . ' berhasil diverifikasi.')
                ->success()
                ->send();
                
            return redirect()->route('filament.admin.resources.users.index')
                ->with('success', 'Pengguna berhasil diverifikasi.');
                
        } catch (\Exception $e) {
            Log::error('Gagal memverifikasi pengguna: ' . $e->getMessage());
            
            return redirect()->route('filament.admin.resources.users.index')
                ->with('error', 'Terjadi kesalahan saat verifikasi pengguna.');
        }
    }
    
    /**
     * Tolak pendaftaran pengguna
     */
    public function rejectUser(Request $request, $userId)
    {
        // Validasi signature URL
        if (!$request->hasValidSignature()) {
            return redirect()->route('filament.admin.resources.users.index')
                ->with('error', 'Link penolakan tidak valid atau sudah kadaluarsa.');
        }

        try {
            $user = User::findOrFail($userId);
            
            // Simpan email untuk notifikasi
            $userEmail = $user->email;
            $userName = $user->name;
            
            // Kirim email ke pengguna bahwa akunnya ditolak sebelum dihapus
            try {
                Mail::to($userEmail)->send(new UserVerificationStatusEmail($user, false));
            } catch (\Exception $e) {
                Log::error('Gagal mengirim email penolakan: ' . $e->getMessage());
            }
            
            // Hapus pengguna
            $user->delete();
            
            // Notifikasi sukses
            Notification::make()
                ->title('Penolakan Berhasil')
                ->body('Pengguna ' . $userName . ' telah ditolak dan dihapus dari sistem.')
                ->success()
                ->send();
                
            return redirect()->route('filament.admin.resources.users.index')
                ->with('success', 'Pengguna berhasil ditolak dan dihapus.');
                
        } catch (\Exception $e) {
            Log::error('Gagal menolak pengguna: ' . $e->getMessage());
            
            return redirect()->route('filament.admin.resources.users.index')
                ->with('error', 'Terjadi kesalahan saat menolak pengguna.');
        }
    }
}