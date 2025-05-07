<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class NewUserRegistrationEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public User $user)
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pendaftaran Pengguna Baru: ' . $this->user->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Generate signed URLs for verify and reject actions
        // URLs ini hanya valid dalam waktu tertentu (24 jam)
        $appUrl = config('app.url');
        
        // Buat absolute URL (parameter keempat = true) untuk mencegah masalah domain
        $verifyUrl = URL::temporarySignedRoute(
            'admin.verify-user',
            now()->addHours(24),
            ['userId' => $this->user->id],
            true // parameter absolute = true untuk mendapatkan URL lengkap
        );
        
        $rejectUrl = URL::temporarySignedRoute(
            'admin.reject-user',
            now()->addHours(24),
            ['userId' => $this->user->id],
            true // parameter absolute = true untuk mendapatkan URL lengkap
        );
        
        // Jika app.url tetap menggunakan localhost, ganti dengan domain yang diinginkan
        if (strpos($appUrl, 'localhost') !== false || strpos($appUrl, '127.0.0.1') !== false) {
            $productionDomain = env('PRODUCTION_URL', 'https://avsimulator.com');
            $verifyUrl = str_replace($appUrl, $productionDomain, $verifyUrl);
            $rejectUrl = str_replace($appUrl, $productionDomain, $rejectUrl);
        }

        return new Content(
            markdown: 'emails.new-user-registration',
            with: [
                'user' => $this->user,
                'verifyUrl' => $verifyUrl,
                'rejectUrl' => $rejectUrl,
                'userManagementUrl' => route('filament.admin.resources.users.index', [], true),
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