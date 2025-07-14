<?php

namespace App\Mail;

use App\Models\Pengajuanoprasional;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PengajuanRejectMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pengajuan;
    public $alasan; 
 
    /**
     * Create a new message instance.
     */
    public function __construct(Pengajuanoprasional $pengajuan, $alasan = null)
    {
        $this->pengajuan = $pengajuan;
        $this->alasan = $alasan;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pengajuan Barang Ditolak ',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'pengajuann.oprasionalmail.rejected',
            with: [
                'pengajuan' => $this->pengajuan,
                'alasan' => $this->alasan ?? $this->pengajuan->reject_reason ?? 'Tidak ada alasan yang diberikan',
                'namaPengaju' => $this->pengajuan->user->name,
                'tanggalPengajuan' => $this->pengajuan->tanggal_pengajuan->format('d F Y'),
                'tanggalDitolak' => $this->pengajuan->rejected_at ? $this->pengajuan->rejected_at->format('d F Y H:i') : now()->format('d F Y H:i'),
                'ditolakOleh' => $this->pengajuan->rejectedBy->name ?? 'Sistem',
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
