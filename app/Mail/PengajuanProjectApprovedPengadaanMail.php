<?php

namespace App\Mail;

use App\Models\Pengajuanproject;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PengajuanProjectApprovedPengadaanMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pengajuan;
    public $approvedBy;
    public $catatan;

    /**
     * Create a new message instance.
     */
    public function __construct(Pengajuanproject $pengajuan, $approvedBy = null, $catatan = null)
    {
        $this->pengajuan = $pengajuan;
        $this->approvedBy = $approvedBy;
        $this->catatan = $catatan;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pengajuan Project Disetujui Tim Pengadaan ',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'pengajuann.projectmail.approved-pengadaan',
            with: [
                'pengajuan' => $this->pengajuan,
                'approvedBy' => $this->approvedBy,
                'detailBarang' => $this->pengajuan->detail_barang ?? [],
                'catatan' => $this->catatan,
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
