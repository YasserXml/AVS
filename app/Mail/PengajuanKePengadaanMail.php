<?php

namespace App\Mail;

use App\Models\Pengajuanproject;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PengajuanKePengadaanMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pengajuan;
    public $action;
    public $recipient;
    public $catatan;

    /**
     * Create a new message instance.
     */
    public function __construct(Pengajuanproject $pengajuan, string $action, $recipient = null, $catatan = null)
    {
        $this->pengajuan = $pengajuan;
        $this->action = $action;
        $this->recipient = $recipient;
        $this->catatan = $catatan;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subjects = [
            'pengajuan_terkirim' => 'Pengajuan Baru untuk Project: ' . $this->pengajuan->nameproject->nama_project,
            'pending_pm_review' => 'Pengajuan Sedang Direview - Project: ' . $this->pengajuan->nameproject->nama_project,
            'disetujui_pm_dikirim_ke_pengadaan' => 'Pengajuan Disetujui PM - Project: ' . $this->pengajuan->nameproject->nama_project,
            'ditolak_pm' => 'Pengajuan Ditolak PM - Project: ' . $this->pengajuan->nameproject->nama_project,
            'disetujui_pengadaan' => 'Pengajuan Disetujui Pengadaan - Project: ' . $this->pengajuan->nameproject->nama_project,
            'ditolak_pengadaan' => 'Pengajuan Ditolak Pengadaan - Project: ' . $this->pengajuan->nameproject->nama_project,
            'pengajuan_dikirim_ke_direksi' => 'Pengajuan Dikirim ke Direksi - Project: ' . $this->pengajuan->nameproject->nama_project,
            'approved_by_direksi' => 'Pengajuan Disetujui Direksi - Project: ' . $this->pengajuan->nameproject->nama_project,
            'reject_direksi' => 'Pengajuan Ditolak Direksi - Project: ' . $this->pengajuan->nameproject->nama_project,
            'pengajuan_dikirim_ke_keuangan' => 'Pengajuan Dikirim ke Keuangan - Project: ' . $this->pengajuan->nameproject->nama_project,
            'pending_keuangan' => 'Review Keuangan Dimulai - Project: ' . $this->pengajuan->nameproject->nama_project,
            'process_keuangan' => 'Proses Keuangan Berlangsung - Project: ' . $this->pengajuan->nameproject->nama_project,
            'execute_keuangan' => 'Proses Keuangan Selesai - Project: ' . $this->pengajuan->nameproject->nama_project,
            'pengajuan_dikirim_ke_pengadaan_final' => 'Pengajuan Kembali ke Pengadaan - Project: ' . $this->pengajuan->nameproject->nama_project,
            'pengajuan_dikirim_ke_admin' => 'Pengajuan Dikirim ke Admin - Project: ' . $this->pengajuan->nameproject->nama_project,
            'processing' => 'Proses Pengadaan Dimulai - Project: ' . $this->pengajuan->nameproject->nama_project,
            'ready_pickup' => 'Barang Siap Diambil - Project: ' . $this->pengajuan->nameproject->nama_project,
            'completed' => 'Pengajuan Selesai - Project: ' . $this->pengajuan->nameproject->nama_project,
        ];

        return new Envelope(
            subject: $subjects[$this->action] ?? 'Notifikasi Pengajuan Pengadaan',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'pengajuann.projectmail.pm-to-pengadaan',
            with: [
                'pengajuan' => $this->pengajuan,
                'action' => $this->action,
                'recipient' => $this->recipient,
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
