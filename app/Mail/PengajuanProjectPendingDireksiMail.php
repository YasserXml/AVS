<?php

namespace App\Mail;

use App\Models\Pengajuanproject;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PengajuanProjectPendingDireksiMail extends Mailable
{
    use Queueable, SerializesModels;

    /** 
     * Create a new message instance.
     */
    public function __construct(
        public Pengajuanproject $pengajuan,
        public string $alasanPending,
        public string $tanggalPending
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pengajuan Project Dipending oleh Direksi ',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'pengajuann.projectmail.pending-direksi',
            with: [
                'pengajuan' => $this->pengajuan,
                'catatan' => $this->pengajuan->catatan,
                'tanggalPending' => $this->pengajuan->tanggal_pending,
                'namaProject' => $this->pengajuan->nameproject->nama_project ?? 'Project tidak ditemukan',
                'namaPengaju' => $this->pengajuan->user->name ?? 'Pengaju tidak ditemukan',
                'tanggalPengajuan' => $this->pengajuan->tanggal_pengajuan?->format('d F Y'),
                'tanggalDibutuhkan' => $this->pengajuan->tanggal_dibutuhkan?->format('d F Y'),
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
