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

    protected User $newUser;

    public function __construct(User $newUser)
    {
        $this->newUser = $newUser;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        // Buat URL verifikasi dengan token khusus
        $verifyUrl = URL::temporarySignedRoute(
            'admin.verify-user',
            now()->addDays(7), // Tautan berlaku selama 7 hari
            [
                'user_id' => $this->newUser->id,
                'admin_id' => $notifiable->id,
                'action' => 'verify'
            ]
        );

        // Buat URL tolak dengan token khusus
        $rejectUrl = URL::temporarySignedRoute(
            'admin.verify-user',
            now()->addDays(7), // Tautan berlaku selama 7 hari
            [
                'user_id' => $this->newUser->id,
                'admin_id' => $notifiable->id,
                'action' => 'reject'
            ]
        );

        return (new MailMessage)
            ->subject('Verifikasi Pengguna Baru')
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Ada pengguna baru yang mendaftar di sistem dan membutuhkan verifikasi Anda.')
            ->line('Detail Pengguna:')
            ->line('Nama: ' . $this->newUser->name)
            ->line('Email: ' . $this->newUser->email)
            ->line('Tanggal Pendaftaran: ' . $this->newUser->created_at->format('d-m-Y H:i'))
            ->action('Verifikasi Pengguna', $verifyUrl)
            ->line('Jika Anda ingin menolak pengguna ini, klik tautan di bawah ini:')
            ->action('Tolak Pengguna', $rejectUrl)
            ->line('Terima kasih atas perhatian Anda.');
    }

    public function toArray($notifiable): array
    {
        return [
            'user_id' => $this->newUser->id,
            'name' => $this->newUser->name,
            'email' => $this->newUser->email,
        ];
    }
}