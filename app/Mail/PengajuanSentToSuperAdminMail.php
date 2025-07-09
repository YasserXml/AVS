<?php

namespace App\Mail;

use App\Models\Pengajuanoprasional;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PengajuanSentToSuperAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pengajuan;

    public function __construct(Pengajuanoprasional $pengajuan)
    {
        $this->pengajuan = $pengajuan;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pengajuan Barang Dikirim ke Tim Pengadaan ',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'pengajuann.oprasionalmail.sent-to-superadmin',
            with: [
                'pengajuan' => $this->pengajuan,
                'namaPengaju' => $this->pengajuan->user->name,
                'tanggalPengajuan' => $this->pengajuan->tanggal_pengajuan->format('d F Y'),
                'tanggalDibutuhkan' => $this->pengajuan->tanggal_dibutuhkan ? $this->pengajuan->tanggal_dibutuhkan->format('d F Y') : 'Tidak ditentukan',
                'totalItem' => $this->pengajuan->total_item,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
