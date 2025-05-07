<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\UserVerifiedNotification;
use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class AdminVerificationController extends Controller
{
    public function verifyUser(Request $request, $userId)
    {
        try {
            Log::info('Verifikasi pengguna dipanggil untuk userId: ' . $userId);
            
            // Cari user berdasarkan ID
            $user = User::findOrFail($userId);
            
            Log::info('Pengguna ditemukan: ' . $user->email);
            
            // Update status verifikasi dan tandai email sebagai terverifikasi
            $user->admin_verified = true;
            
            // Verifikasi email jika belum diverifikasi
            if (!$user->hasVerifiedEmail()) {
                $user->email_verified_at = now();
            }
            
            $user->save();
            
            Log::info('Status admin_verified dan email_verified_at diperbarui');

            // Pastikan pengguna memiliki role
            $this->ensureUserHasRole($user);

            // Kirim notifikasi ke user via database dan email
            try {
                $user->notify(new UserVerifiedNotification());
                Log::info('Notifikasi verifikasi berhasil dikirim ke pengguna');
            } catch (\Exception $notifyEx) {
                Log::error('Gagal mengirim notifikasi verifikasi: ' . $notifyEx->getMessage());
            }

            // Redirect ke halaman users dengan pesan sukses
            return redirect()->route('filament.admin.resources.users.index')
                ->with('success', 'Pengguna ' . $user->name . ' berhasil diverifikasi');
                
        } catch (\Exception $e) {
            Log::error('Error verifikasi pengguna: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()->route('filament.admin.pages.dashboard')
                ->with('error', 'Terjadi kesalahan saat verifikasi pengguna: ' . $e->getMessage());
        }
    }

    public function rejectUser(Request $request, $userId)
    {
        try {
            Log::info('Penolakan pengguna dipanggil untuk userId: ' . $userId);
            
            // Cari user berdasarkan ID
            $user = User::findOrFail($userId);
            
            Log::info('Pengguna ditemukan: ' . $user->email);
            
            // Simpan informasi pengguna untuk notifikasi
            $userName = $user->name;
            $userEmail = $user->email;
            
            // Hapus user
            $user->delete();
            
            Log::info('Pengguna berhasil dihapus');

            // Redirect ke halaman users dengan pesan sukses
            return redirect()->route('filament.admin.resources.users.index')
                ->with('success', 'Pengguna ' . $userName . ' (' . $userEmail . ') telah ditolak dan dihapus dari sistem');
                
        } catch (\Exception $e) {
            Log::error('Error menolak pengguna: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()->route('filament.admin.pages.dashboard')
                ->with('error', 'Terjadi kesalahan saat menolak pengguna: ' . $e->getMessage());
        }
    }

    /**
     * Memastikan pengguna memiliki role
     * 
     * @param \App\Models\User $user
     * @return void
     */
    protected function ensureUserHasRole(User $user): void
    {
        try {
            // Cek apakah user sudah memiliki role
            if ($user->roles()->count() > 0) {
                Log::info('Pengguna sudah memiliki role');
                return;
            }

            // Cari role 'user' dari Spatie Permission
            $userRole = Role::where('name', 'user')->first();
            
            if (!$userRole) {
                Log::warning('Role "user" tidak ditemukan, mencoba mendapatkan default role dari Shield');
                
                // Alternatif: Mendapatkan role dari Shield
                if (class_exists('Utils')) {
                    $userRoleName = Utils::getPanelUserRoleName();
                    $userRole = Role::where('name', $userRoleName)->first();
                }
                
                // Jika masih tidak ditemukan, buat role 'user'
                if (!$userRole) {
                    Log::warning('Membuat role "user" baru karena tidak ditemukan');
                    $userRole = Role::create(['name' => 'user', 'guard_name' => 'web']);
                }
            }
            
            // Assign role ke user
            $user->assignRole($userRole);
            Log::info('Role "' . $userRole->name . '" berhasil diberikan kepada pengguna');
            
        } catch (\Exception $e) {
            Log::error('Gagal memberikan role kepada pengguna: ' . $e->getMessage());
        }
    }
}