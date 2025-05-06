<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class AdminNewUserNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected User $newUser;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $newUser)
    {
        $this->newUser = $newUser;
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
        $verificationUrl = route('filament.admin.resources.users.verify', $this->newUser->id);

        return (new MailMessage)
            ->subject('Pendaftaran Pengguna Baru - Perlu Verifikasi')
            ->greeting('Halo Admin!')
            ->line('Seorang pengguna baru telah mendaftar dan memerlukan verifikasi Anda.')
            ->line(new HtmlString('Nama: <strong>' . $this->newUser->name . '</strong>'))
            ->line(new HtmlString('Email: <strong>' . $this->newUser->email . '</strong>'))
            ->line(new HtmlString('Tanggal Pendaftaran: <strong>' . $this->newUser->created_at->format('d-m-Y H:i') . '</strong>'))
            ->action('Verifikasi Sekarang', $verificationUrl)
            ->line('Terima kasih telah menggunakan aplikasi kami!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Pendaftaran Pengguna Baru',
            'message' => 'Pengguna baru "' . $this->newUser->name . '" memerlukan verifikasi',
            'user_id' => $this->newUser->id,
            'icon' => 'heroicon-o-user-plus',
            'url' => route('filament.admin.resources.users.verify', $this->newUser->id),
        ];
    }
}