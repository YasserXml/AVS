<?php
namespace App\Services;

use App\Models\User;
use App\Notifications\NewUserRegisteredNotification;
use App\Notifications\NewUserRegistration;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Notification as FacadesNotification;
use Spatie\Permission\Models\Role;

class AdminNotificationService
{
    public static function sendNewUserRegisteredNotification(User $user, bool $fromSocialLogin = false)
    {
        // Dapatkan semua admin atau super admin
        $adminUsers = User::whereHas('roles', fn ($query) => 
                $query->whereIn('name', ['super_admin', 'admin'])
            )->get();
        
        if ($adminUsers->isEmpty()) {
            // Fallback jika tidak ada admin
            return;
        }

        // Kirim notifikasi ke semua admin
        FacadesNotification::send($adminUsers, new NewUserRegistration($user, $fromSocialLogin));
    }
}