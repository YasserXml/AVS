<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\AdminVerifiedUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Filament\Notifications\Notification as FilamentNotification;

class UserVerificationController extends Controller
{
    /**
     * Verifikasi pengguna dari link email
     *
     * @param Request $request
     * @param int $id ID pengguna yang akan diverifikasi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verify(Request $request, $id)
    {
        // Temukan pengguna berdasarkan ID
        $user = User::findOrFail($id);
        
        // Jika pengguna sudah diverifikasi, redirect dengan pesan
        if ($user->admin_verified && $user->hasVerifiedEmail()) {
            return redirect()->route('filament.admin.auth.login')
                ->with('status', 'Akun Anda sudah diverifikasi sebelumnya.');
        }
        
        // Verifikasi pengguna
        $user->admin_verified = true;
        
        // Jika email belum diverifikasi, verifikasi sekarang
        if (!$user->hasVerifiedEmail()) {
            $user->email_verified_at = Carbon::now();
        }
        
        $user->save();
        
        // Kirim notifikasi database ke semua admin
        $adminUsers = User::whereHas('roles', fn ($query) => 
            $query->whereIn('name', ['super_admin', 'admin'])
        )->get();
        
        foreach ($adminUsers as $admin) {
            // Gunakan notifikasi system untuk memberitahu admin
            $admin->notify(new AdminVerifiedUser($user, null));
        }
        
        // Redirect ke halaman login dengan pesan sukses
        return redirect()->route('filament.admin.auth.login')
            ->with('status', 'Akun Anda berhasil diverifikasi. Silakan login untuk melanjutkan.');
    }
}