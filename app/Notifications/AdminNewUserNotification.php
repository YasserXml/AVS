<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminNewUserNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $newUser;

    public function __construct(User $newUser)
    {
        $this->newUser = $newUser;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database']; // Kirim melalui email dan simpan di database
    }

    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = route('admin.user.verify', [
            'user' => $this->newUser->id,
            'token' => sha1($this->newUser->email),
        ]);

        return (new MailMessage)
            ->subject('Permintaan Verifikasi Pengguna Baru')
            ->greeting('Halo Admin,')
            ->line('Ada pengguna baru yang mendaftar di sistem dan membutuhkan verifikasi Anda.')
            ->line('Detail Pengguna:')
            ->line('Nama: ' . $this->newUser->name)
            ->line('Email: ' . $this->newUser->email)
            ->line('Waktu Pendaftaran: ' . $this->newUser->created_at->format('d M Y H:i'))
            ->action('Verifikasi Pengguna', $verificationUrl)
            ->line('Jika Anda tidak ingin menyetujui pendaftaran ini, tidak diperlukan tindakan lebih lanjut.')
            ->salutation('Terima kasih, ' . config('app.name'));
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => 'Pengguna baru mendaftar dan membutuhkan verifikasi',
            'user_id' => $this->newUser->id,
            'user_name' => $this->newUser->name,
            'user_email' => $this->newUser->email,
        ];
    }
}