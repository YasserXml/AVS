<?php

namespace App\Mail;

use App\Models\Pengajuanproject;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PengajuanProjectSentToPengadaanMail extends Mailable
{
     use Queueable, SerializesModels;

    public Pengajuanproject $pengajuan;

    /**
     * Create a new message instance.
     */
    public function __construct(Pengajuanproject $pengajuan)
    {
        $this->pengajuan = $pengajuan;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pengajuan Project Dikirim ke Tim Pengadaan ',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'pengajuann.projectmail.sent-to-pengadaan',
            with: [
                'pengajuan' => $this->pengajuan,
                'project' => $this->pengajuan->nameproject,
                'pengaju' => $this->pengajuan->user,
                'detailBarang' => $this->pengajuan->detail_barang,
                'pm' => $this->pengajuan->nameproject->user ?? null,
            ]
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
