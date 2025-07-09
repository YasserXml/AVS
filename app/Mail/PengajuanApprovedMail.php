<?php

namespace App\Mail;

use App\Models\Pengajuanoprasional;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PengajuanApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pengajuan;
    public $catatan;

    /**
     * Create a new message instance.
     */
    public function __construct(Pengajuanoprasional $pengajuan, $catatan = null)
    {
        $this->pengajuan = $pengajuan;
        $this->catatan = $catatan;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pengajuan Barang Disetujui',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view:  'pengajuann.oprasionalmail.approved', 
            with: [
                'pengajuan' => $this->pengajuan,
                'catatan' => $this->catatan,
                'namaPengaju' => $this->pengajuan->user->name,
                'tanggalPengajuan' => $this->pengajuan->tanggal_pengajuan->format('d F Y'),
                'tanggalDibutuhkan' => $this->pengajuan->tanggal_dibutuhkan ? $this->pengajuan->tanggal_dibutuhkan->format('d F Y') : 'Tidak ditentukan',
                'tanggalDisetujui' => $this->pengajuan->approved_at ? $this->pengajuan->approved_at->format('d F Y H:i') : now()->format('d F Y H:i'),
                'disetujuiOleh' => $this->pengajuan->approvedBy->name ?? 'Sistem',
                'totalItem' => $this->pengajuan->total_item,
                'totalJenisBarang' => $this->pengajuan->total_jenis_barang,
                'statusLabel' => $this->pengajuan->status_label,
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
