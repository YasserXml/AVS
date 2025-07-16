<?php

namespace App\Mail;

use App\Models\Pengajuanproject;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PengajuanProjectApprovedDireksiMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pengajuan;
    public $approvedBy;

    /**
     * Create a new message instance.
     */
    public function __construct(Pengajuanproject $pengajuan, $approvedBy)
    {
        $this->pengajuan = $pengajuan;
        $this->approvedBy = $approvedBy;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pengajuan Project Disetujui Direksi ',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'pengajuann.projectmail.approved-direksi',
            with: [
                'pengajuan' => $this->pengajuan,
                'approvedBy' => $this->approvedBy,
                'projectName' => $this->pengajuan->nameproject->nama_project ?? 'N/A',
                'pengajuName' => $this->pengajuan->user->name ?? 'N/A',
                'tanggalPengajuan' => $this->pengajuan->tanggal_pengajuan,
                'tanggalDibutuhkan' => $this->pengajuan->tanggal_dibutuhkan,
                'detailBarang' => $this->pengajuan->detail_barang,
                'catatan' => $this->pengajuan->catatan,
                'approvedAt' => $this->pengajuan->approved_at,
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
