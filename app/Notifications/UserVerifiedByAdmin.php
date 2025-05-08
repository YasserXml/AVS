<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\HtmlString;

class UserVerifiedByAdmin extends Notification implements ShouldQueue
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
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
{
    return (new MailMessage)
        ->subject('Akun Anda Telah Diverifikasi')
        ->view('emails.user-verified', ['notifiable' => $notifiable]);
}

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Akun Anda Telah Diverifikasi',
            'message' => 'Selamat! Akun Anda telah berhasil diverifikasi oleh administrator.',
            'icon' => 'heroicon-o-check-circle',
            'time' => now()->format('d/m/Y H:i'),
            'url' => route('filament.admin.auth.login'),
        ];
    }
}