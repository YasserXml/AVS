<?php
namespace App\Services;

use App\Mail\NewUserRegistrationMail;
use App\Mail\UserVerifiedByAdminMail;
use App\Models\User;
use Carbon\Carbon;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail;

class AdminNotificationService
{
     /**
     * Kirim notifikasi pendaftaran pengguna baru ke semua admin
     *
     * @param User $newUser
     * @return void
     */
    public static function sendNewUserRegistrationNotifications(User $newUser)
    {
        // Ambil semua admin
        $adminUsers = User::whereHas('roles', fn ($query) => 
            $query->whereIn('name', ['super_admin', 'admin'])
        )->get();
        
        // Buat URL verifikasi
        $verificationUrl = route('admin.verify-user', [
            'user' => $newUser->id,
            'hash' => sha1($newUser->email),
        ]);
        
        foreach ($adminUsers as $admin) {
            // Kirim notifikasi Filament
            Notification::make()
                ->title('Pendaftaran Pengguna Baru')
                ->icon('heroicon-o-user-plus')
                ->iconColor('primary')
                ->body("👤 {$newUser->name} telah mendaftar dan menunggu verifikasi.")
                ->actions([
                    Action::make('verify')
                        ->label('Verifikasi')
                        ->url($verificationUrl)
                        ->button(),
                ])
                ->sendToDatabase($admin);
            
            // Kirim email
            Mail::to($admin->email)->queue(new NewUserRegistrationMail($newUser, $admin));
        }
    }
    
    /**
     * Kirim notifikasi verifikasi pengguna ke pengguna yang bersangkutan
     *
     * @param User $user
     * @return void
     */
    public static function sendUserVerifiedNotification(User $user)
    {
        // Kirim notifikasi Filament
        Notification::make()
            ->title('Akun Anda Telah Diverifikasi')
            ->success()
            ->icon('heroicon-o-check-circle')
            ->body('Selamat! Akun Anda telah berhasil diverifikasi oleh administrator.')
            ->actions([
                Action::make('login')
                    ->label('Login Sekarang')
                    ->url(route('filament.admin.auth.login'))
                    ->button(),
            ])
            ->sendToDatabase($user);
        
        // Kirim email
        Mail::to($user->email)->queue(new UserVerifiedByAdminMail($user));
    }
}