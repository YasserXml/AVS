<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserVerifiedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $loginUrl = route('filament.admin.auth.login');

        return (new MailMessage)
            ->subject('Akun Anda Telah Diverifikasi')
            ->greeting('Halo ' . $notifiable->name . '!')
            ->line('Akun Anda telah diverifikasi oleh admin.')
            ->line('Sekarang Anda dapat masuk ke sistem menggunakan email dan kata sandi Anda.')
            ->action('Login Sekarang', $loginUrl)
            ->line('Terima kasih telah menggunakan aplikasi kami!');
    }
}