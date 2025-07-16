<?php

namespace App\Mail;

use App\Models\Pengajuanproject;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PengajuanProjectRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pengajuan;
    public $project;
    public $user;
    public $rejectedBy;
    public $rejectionType;

    /**
     * Create a new message instance.
     */
    public function __construct(Pengajuanproject $pengajuan)
    {
        $this->pengajuan = $pengajuan;
        $this->project = $pengajuan->nameproject;
        $this->user = $pengajuan->user;
        $this->rejectedBy = $pengajuan->rejectedByUser ?? null;
        $this->rejectionType = $this->determineRejectionType($pengajuan->status);
    }

    /**
     * Determine rejection type based on status
     */
    private function determineRejectionType($status)
    {
        switch ($status) {
            case 'ditolak_pm':
                return 'Project Manager';
            case 'ditolak_pengadaan':
                return 'Tim Pengadaan';
            case 'reject_direksi':
                return 'Direksi';
            default:
                return 'System';
        }
    }

    /**
     * Get rejection type label for Indonesian
     */
    private function getRejectionTypeLabel()
    {
        switch ($this->rejectionType) {
            case 'Project Manager':
                return 'Project Manager';
            case ' Pengadaan':
                return ' Pengadaan';
            case 'Direksi':
                return 'Direksi';
            default:
                return 'System';
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pengajuan Project Ditolak - ' . $this->project->nama_project,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'pengajuann.projectmail.rejected',
            with: [
                'pengajuan' => $this->pengajuan,
                'project' => $this->project,
                'user' => $this->user,
                'rejectedBy' => $this->rejectedBy,
                'rejectionType' => $this->getRejectionTypeLabel(),
                'nomorPengajuan' => $this->pengajuan->nomor_pengajuan,
                'namaProject' => $this->project->nama_project,
                'tanggalPengajuan' => $this->pengajuan->tanggal_pengajuan,
                'tanggalDibutuhkan' => $this->pengajuan->tanggal_dibutuhkan,
                'detailBarang' => $this->pengajuan->detail_barang,
                'namaPengaju' => $this->user->name,
                'alasanPenolakan' => $this->pengajuan->reject_reason,
                'tanggalDitolak' => $this->pengajuan->rejected_at,
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
