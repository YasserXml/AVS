<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserVerifiedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        //
    }

    
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $loginUrl = route('filament.admin.auth.login');

        return (new MailMessage)
            ->subject('Akun Anda Telah Diverifikasi')
            ->greeting('Halo ' . $notifiable->name)
            ->line('Selamat! Akun Anda telah diverifikasi oleh administrator.')
            ->line('Sekarang Anda dapat login ke sistem dengan email dan password Anda.')
            ->action('Login Sekarang', $loginUrl)
            ->line('Terima kasih telah menggunakan aplikasi kami!');
    }

    
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Akun Diverifikasi',
            'message' => 'Akun Anda telah diverifikasi oleh administrator dan sudah dapat digunakan.',
            'action_url' => route('filament.admin.auth.login'),
        ];
    }
}