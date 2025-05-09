<?php
namespace App\Services;

use App\Models\User;
use App\Notifications\NewUserRegistration;
use Illuminate\Support\Facades\Log;

class AdminNotificationService
{
    public static function sendNewUserRegisteredNotification(User $user, bool $fromSocialLogin = false)
    {
        // Dapatkan semua admin atau super admin
        $adminUsers = User::whereHas('roles', fn ($query) => 
                $query->whereIn('name', ['super_admin', 'admin'])
            )->get();
        
        if ($adminUsers->isEmpty()) {
            Log::warning('Tidak ada admin ditemukan untuk notifikasi pendaftaran pengguna baru');
            return;
        }

        // Log untuk debugging
        Log::info('Mengirim notifikasi pendaftaran baru ke admin', [
            'new_user_id' => $user->id,
            'admin_count' => $adminUsers->count()
        ]);

        // Kirim notifikasi ke semua admin
        foreach ($adminUsers as $admin) {
            $admin->notify(new NewUserRegistration($user));
        }
    }
}