<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserVerifiedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Akun Anda Telah Diverifikasi')
            ->greeting('Halo ' . $notifiable->name . '!')
            ->line('Akun Anda telah diverifikasi oleh administrator.')
            ->line('Sekarang Anda dapat masuk ke aplikasi dengan email dan password yang telah Anda daftarkan.')
            ->action('Login Sekarang', url('/login'))
            ->line('Terima kasih telah menggunakan aplikasi kami!');
    }
}