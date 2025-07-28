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
use Illuminate\Support\Facades\URL;

class NewUserRegistrationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $newUser;
    public $admin;
    public $verificationUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(User $newUser, User $admin)
    {
        $this->newUser = $newUser;
        $this->admin = $admin;

        // Buat URL terverifikasi untuk verifikasi langsung yang aman
        $this->verificationUrl = URL::temporarySignedRoute(
            'user.verify',
            now()->addDays(7), // URL akan kedaluwarsa setelah 7 hari
            [
                'id' => $this->newUser->id,
                'hash' => sha1($this->newUser->email),
            ]
        );
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pendaftaran Pengguna Baru',
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
        $userRoles = $this->newUser->getRoleNames();
        $divisiNama = 'Tidak ada divisi';

        // Cari role yang cocok dengan pemetaan divisi
        foreach ($userRoles as $role) {
            if (isset($divisiMapping[$role])) {
                $divisiNama = $divisiMapping[$role];
                break;
            }
        }

        return new Content(
            view: 'emails.user-registration',
            with: [
                'adminName' => $this->admin->name,
                'userName' => $this->newUser->name,
                'userEmail' => $this->newUser->email,
                'userDivisi' => $divisiNama,
                'registrationDate' => Carbon::parse($this->newUser->created_at)->format('d/m/Y H:i'),
                'verificationUrl' => $this->verificationUrl,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments(): array
    {
        return [];
    }
}
