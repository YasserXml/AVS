<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class NewUserRegistration extends Notification implements ShouldQueue
{
    use Queueable;

    protected $newUser;

    public function __construct(User $newUser)
    {
        $this->newUser = $newUser;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        // Buat URL terverifikasi untuk verifikasi langsung yang aman
        $verificationUrl = URL::temporarySignedRoute(
            'user.verify',
            now()->addDays(7), // URL akan kedaluwarsa setelah 7 hari
            [
                'id' => $this->newUser->id,
                'hash' => sha1($this->newUser->email),
            ]
        );

        return (new MailMessage)
            ->subject('Pendaftaran Pengguna Baru')
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Ada pendaftaran pengguna baru yang memerlukan verifikasi.')
            ->line('Nama: ' . $this->newUser->name)
            ->line('Email: ' . $this->newUser->email)
            ->line('Tanggal Pendaftaran: ' . Carbon::parse($this->newUser->created_at)->format('d/m/Y H:i'))
            ->action('Verifikasi Sekarang', $verificationUrl) // Gunakan URL verifikasi langsung
            ->line('Tombol ini akan langsung memverifikasi pengguna tanpa perlu membuka panel admin.')
            ->line('Terima kasih telah membantu menjaga keamanan sistem!');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'icon' => 'heroicon-o-user-plus',
            'title' => 'Pendaftaran Pengguna Baru',
            'message' => "👤 {$this->newUser->name} telah mendaftar dan menunggu verifikasi.",
            'time' => Carbon::now('Asia/Jakarta')->format('d/m/Y H:i'),
            'user_id' => $this->newUser->id,
        ];
    }
}