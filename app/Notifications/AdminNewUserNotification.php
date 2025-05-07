<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class AdminNewUserNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected User $newUser
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $appUrl = config('app.url');
        
        $verifyUrl = URL::temporarySignedRoute(
            'admin.verify-user',
            now()->addHours(24),
            ['userId' => $this->newUser->id],
            false
        );
        
        // Konversi URL relatif ke absolut dengan domain yang benar
        $verifyUrl = str_replace('http://127.0.0.1:8000', $appUrl, $verifyUrl);
        
        return [
            'icon' => 'heroicon-o-user-plus',
            'title' => 'Pendaftaran Pengguna Baru',
            'message' => "Pengguna baru {$this->newUser->name} ({$this->newUser->email}) telah mendaftar dan membutuhkan verifikasi.",
            'user_id' => $this->newUser->id,
            'action_url' => $verifyUrl,
        ];
    }
}