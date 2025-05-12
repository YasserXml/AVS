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

        // Dapatkan nama divisi yang lebih manusiawi atau gunakan nilai asli jika tidak ditemukan
        $divisiNama = $divisiMapping[$this->newUser->divisi_role] ?? $this->newUser->divisi_role;

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