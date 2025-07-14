<?php

namespace App\Mail;

use App\Models\Pengajuanoprasional;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PengajuanSentToPengadaanMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pengajuan;

    /**
     * Create a new message instance.
     */
    public function __construct(Pengajuanoprasional $pengajuan)
    {
        $this->pengajuan = $pengajuan;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pengajuan Operasional Dikirim ke Tim Pengadaan - ' . $this->pengajuan->nomor_pengajuan,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'pengajuann.oprasionalmail.sent-to-pengadaan',
            with: [
                'pengajuan' => $this->pengajuan,
                'pengaju' => $this->pengajuan->user,
                'detailBarang' => $this->pengajuan->detail_barang,
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
