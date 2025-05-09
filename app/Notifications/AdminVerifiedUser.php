<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class AdminVerifiedUser extends Notification implements ShouldQueue
{
    use Queueable;

    protected $verifiedUser;
    protected $adminVerifier;

    public function __construct(User $verifiedUser, ?User $adminVerifier)
    {
        $this->verifiedUser = $verifiedUser;
        $this->adminVerifier = $adminVerifier;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $message = $this->adminVerifier 
            ? "Pengguna {$this->verifiedUser->name} telah diverifikasi oleh {$this->adminVerifier->name}."
            : "Pengguna {$this->verifiedUser->name} telah diverifikasi melalui link email.";
            
        return [
            'icon' => 'heroicon-o-check-circle',
            'title' => 'Pengguna Diverifikasi',
            'message' => $message,
            'time' => Carbon::now('Asia/Jakarta')->format('d/m/Y H:i'),
            'user_id' => $this->verifiedUser->id,
        ];
    }
}