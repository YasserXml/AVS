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
            'divisi_manager_hrd' => 'Manager HRD',
            'divisi_hrd_ga' => 'HRD & GA',
            'divisi_keuangan' => 'Keuangan',
            'divisi_software' => 'Software',
            'divisi_purchasing' => 'Purchasing',
            'divisi_elektro' => 'Elektro',
            'divisi_r&d' => 'R&D',
            'divisi_3d' => '3D',
            'divisi_mekanik' => 'Mekanik',
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
