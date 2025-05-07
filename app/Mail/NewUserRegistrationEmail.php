<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewUserRegistrationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $newUser;
    public $submitter;

    public function __construct(User $newUser, User $submitter)
    {
        $this->newUser = $newUser;
        $this->submitter = $submitter;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pendaftaran Pengguna Baru: ' . $this->newUser->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.new-user-notification',
            with: [
                'newUser' => $this->newUser,
                'verifyUrl' => route('admin.user.verify', [
                    'user' => $this->newUser->id,
                    'token' => sha1($this->newUser->email),
                ]),
                'rejectUrl' => route('admin.user.reject', [
                    'user' => $this->newUser->id,
                    'token' => sha1($this->newUser->email),
                ]),
            ],
        );
    }
}
