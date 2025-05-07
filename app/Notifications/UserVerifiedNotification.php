<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserVerifiedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected bool $isVerified;
    protected string $loginUrl;

    public function __construct(bool $isVerified)
    {
        $this->isVerified = $isVerified;
        $this->loginUrl = route('filament.admin.auth.login');
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->greeting('Halo ' . $notifiable->name . ',');

        if ($this->isVerified) {
            return $message
                ->subject('Akun Anda Telah Diverifikasi')
                ->line('Selamat! Akun Anda telah diverifikasi oleh admin.')
                ->line('Anda sekarang dapat mengakses sistem dengan melakukan login menggunakan email dan kata sandi Anda.')
                ->action('Login Sekarang', $this->loginUrl)
                ->line('Terima kasih telah bergabung dengan kami!');
        } else {
            return $message
                ->subject('Pendaftaran Akun Ditolak')
                ->line('Mohon maaf, pendaftaran akun Anda tidak disetujui oleh admin kami.')
                ->line('Jika Anda merasa ini adalah kesalahan atau memiliki pertanyaan, silakan hubungi tim dukungan kami.')
                ->line('Terima kasih atas pengertian Anda.');
        }
    }

    public function toArray($notifiable): array
    {
        return [
            'is_verified' => $this->isVerified,
        ];
    }
}