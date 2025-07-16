<?php

namespace App\Mail;

use App\Models\Pengajuanproject;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PengajuanProjectSentToKeuanganMail extends Mailable
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
            from: new Address(
                address: config('mail.from.address'),
                name: config('mail.from.name')
            ),
            subject: 'Pengajuan Project Dikirim ke Keuangan ',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'pengajuann.projectmail.sent-to-keuangan',
            with: [
                'pengajuan' => $this->pengajuan,
                'projectName' => $this->pengajuan->nameproject->nama_project ?? 'Tidak diketahui',
                'pengajuName' => $this->pengajuan->user->name ?? 'Tidak diketahui',
                'pmName' => $this->pengajuan->nameproject->user->name ?? 'Tidak diketahui',
                'tanggalPengajuan' => $this->pengajuan->tanggal_pengajuan->format('d F Y'),
                'tanggalDibutuhkan' => $this->pengajuan->tanggal_dibutuhkan ? $this->pengajuan->tanggal_dibutuhkan->format('d F Y') : 'Tidak ditentukan',
                'detailBarang' => $this->pengajuan->detail_barang ?? [],
                'catatan' => $this->pengajuan->catatan,
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
