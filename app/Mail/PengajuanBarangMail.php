<?php

namespace App\Mail;

use App\Models\Pengajuan;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PengajuanBarangMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pengajuanItems;
    public $pengaju;
    public $totalBarang;

    /**
     * Create a new message instance.
     */
    public function __construct($pengajuanItems, User $pengaju)
    {
        $this->pengajuanItems = $pengajuanItems;
        $this->pengaju = $pengaju;
        $this->totalBarang = count($pengajuanItems);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pengajuan Barang Baru - ' . $this->pengaju->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.pengajuan-barang',
            with: [
                'pengajuanItems' => $this->pengajuanItems,
                'pengaju' => $this->pengaju,
                'totalBarang' => $this->totalBarang,
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