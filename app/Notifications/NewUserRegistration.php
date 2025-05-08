<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

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
        return (new MailMessage)
            ->subject('Pendaftaran Pengguna Baru')
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Ada pendaftaran pengguna baru yang memerlukan verifikasi.')
            ->line('Nama: ' . $this->newUser->name)
            ->line('Email: ' . $this->newUser->email)
            ->line('Tanggal Pendaftaran: ' . Carbon::parse($this->newUser->created_at)->format('d/m/Y H:i'))
            ->action('Verifikasi Sekarang', route('filament.admin.resources.users.index'))
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