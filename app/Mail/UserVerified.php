<?php

namespace App\Mail;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserVerified extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Akun Anda Telah Diverifikasi',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Pemetaan nilai divisi ke nama yang lebih manusiawi
        $divisiMapping = [
            'user_sekretariat' => 'Sekretariat',
            'user_hrd_ga' => 'HRD & GA',
            'purchasing' => 'Purchasing',
            'keuangan' => 'Keuangan',
            'user_akuntansi' => 'Akuntansi',
            'user_bisnis_marketing' => 'Bisnis & Marketing',
            'user_system_engineer' => 'System Engineer',
            'user_rnd' => 'RnD',
            'user_game_programming' => 'Game Programming',
            'user_pmo' => 'PMO',
            'user_3d' => '3D',
            'user_mekanik' => 'Mekanik',
        ];

        // Dapatkan nama role pengguna
        $userRoles = $this->user->getRoleNames();
        $divisiNama = 'Tidak ada divisi';

        // Cari role yang cocok dengan pemetaan divisi
        foreach ($userRoles as $role) {
            if (isset($divisiMapping[$role])) {
                $divisiNama = $divisiMapping[$role];
                break;
            }
        }


        return new Content(
            view: 'emails.user-verified',
            with: [
                'userName' => $this->user->name,
                'userEmail' => $this->user->email,
                'userDivisi' => $divisiNama,
                'verifiedDate' => Carbon::parse($this->user->email_verified_at)->format('d/m/Y H:i'),
                'loginUrl' => route('filament.admin.auth.login'),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
