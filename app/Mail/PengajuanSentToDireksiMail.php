<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PengajuanSentToDireksiMail extends Mailable
{
   use Queueable, SerializesModels;

    public $pengajuan;

    /**
     * Create a new message instance.
     */
    public function __construct($pengajuan)
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
            subject: 'Pengajuan Operasional Baru - Menunggu Persetujuan Direksi',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'pengajuann.oprasionalmail.sent-to-direksi',
            with: [
                'pengajuan' => $this->pengajuan,
                'pengaju' => $this->pengajuan->user,
                'detailBarang' => $this->pengajuan->detail_barang,
                'statusText' => $this->getStatusText($this->pengajuan->status),
                'progressPercentage' => $this->getProgressPercentage($this->pengajuan->status),
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

    /**
     * Get status text in Indonesian
     */
    private function getStatusText($status): string
    {
        $statusMap = [
            'pengajuan_terkirim' => 'Pengajuan Terkirim',
            'pending_admin_review' => 'Menunggu Review Admin',
            'diajukan_ke_superadmin' => 'Diajukan ke Tim Pengadaan',
            'superadmin_approved' => 'Disetujui Tim Pengadaan',
            'superadmin_rejected' => 'Ditolak Tim Pengadaan',
            'pengajuan_dikirim_ke_direksi' => 'Menunggu Persetujuan Direksi',
            'approved_by_direksi' => 'Disetujui Direksi',
            'reject_direksi' => 'Ditolak Direksi',
            'pengajuan_dikirim_ke_keuangan' => 'Dikirim ke Keuangan',
            'pending_keuangan' => 'Menunggu Review Keuangan',
            'process_keuangan' => 'Sedang Diproses Keuangan',
            'execute_keuangan' => 'Selesai Diproses Keuangan',
            'pengajuan_dikirim_ke_pengadaan' => 'Dikirim ke Pengadaan',
            'pengajuan_dikirim_ke_admin' => 'Dikirim ke Admin',
            'processing' => 'Sedang Diproses',
            'ready_pickup' => 'Siap Diambil',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan'
        ];

        return $statusMap[$status] ?? 'Status Tidak Diketahui';
    }

    /**
     * Get progress percentage based on status
     */
    private function getProgressPercentage($status): int
    {
        $progressMap = [
            'pengajuan_terkirim' => 10,
            'pending_admin_review' => 20,
            'diajukan_ke_superadmin' => 30,
            'superadmin_approved' => 40,
            'pengajuan_dikirim_ke_direksi' => 50,
            'approved_by_direksi' => 60,
            'pengajuan_dikirim_ke_keuangan' => 70,
            'pending_keuangan' => 75,
            'process_keuangan' => 80,
            'execute_keuangan' => 85,
            'pengajuan_dikirim_ke_pengadaan' => 90,
            'pengajuan_dikirim_ke_admin' => 92,
            'processing' => 95,
            'ready_pickup' => 98,
            'completed' => 100,
            'superadmin_rejected' => 0,
            'reject_direksi' => 0,
            'cancelled' => 0
        ];

        return $progressMap[$status] ?? 0;
    }
}
