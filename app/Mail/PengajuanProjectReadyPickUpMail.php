<?php

namespace App\Mail;

use App\Models\Pengajuanproject;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PengajuanProjectReadyPickUpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pengajuan;
    public $project;
    public $user;

    /**
     * Create a new message instance.
     */
    public function __construct(Pengajuanproject $pengajuan)
    {
        $this->pengajuan = $pengajuan;
        $this->project = $pengajuan->nameproject;
        $this->user = $pengajuan->user;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pengajuan Project Siap Diambil - ' . $this->project->nama_project,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'pengajuann.projectmail.ready-pickup',
            with: [
                'pengajuan' => $this->pengajuan,
                'project' => $this->project,
                'user' => $this->user,
                'nomorPengajuan' => $this->pengajuan->nomor_pengajuan,
                'namaProject' => $this->project->nama_project,
                'tanggalPengajuan' => $this->pengajuan->tanggal_pengajuan,
                'tanggalDibutuhkan' => $this->pengajuan->tanggal_dibutuhkan,
                'detailBarang' => $this->pengajuan->detail_barang,
                'namaPengaju' => $this->user->name,
                'statusHistory' =>  $this->pengajuan->status_history ?? [],
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
