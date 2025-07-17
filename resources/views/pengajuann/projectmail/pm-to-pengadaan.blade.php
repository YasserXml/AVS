<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Barang Project</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #3b82f6;
        }
        .header h1 {
            color: #3b82f6;
            margin: 0;
            font-size: 24px;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 10px 0;
        }
        .status-pending { background-color: #fef3c7; color: #92400e; }
        .status-approved { background-color: #d1fae5; color: #065f46; }
        .status-rejected { background-color: #fee2e2; color: #991b1b; }
        .status-processing { background-color: #dbeafe; color: #1e40af; }
        .status-completed { background-color: #d1fae5; color: #065f46; }
        .info-section {
            background-color: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: bold;
            color: #4a5568;
            min-width: 150px;
        }
        .info-value {
            color: #2d3748;
            flex: 1;
            text-align: right;
        }
        .barang-list {
            background-color: #f7fafc;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
        }
        .barang-item {
            padding: 10px;
            border-left: 4px solid #3b82f6;
            margin-bottom: 10px;
            background-color: white;
        }
        .barang-item:last-child {
            margin-bottom: 0;
        }
        .barang-name {
            font-weight: bold;
            color: #2d3748;
        }
        .barang-detail {
            color: #4a5568;
            font-size: 14px;
            margin-top: 5px;
        }
        .message-section {
            background-color: #fef5e7;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #f59e0b;
        }
        .catatan-section {
            background-color: #f0f9ff;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
            border-left: 4px solid #0ea5e9;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            color: #6b7280;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin: 10px 0;
        }
        .btn:hover {
            background-color: #2563eb;
        }
    </style>
</head>
<body>
    <div class="email-container">

        <div class="message-section">
            <h3>{{ $this->getActionMessage($action) }}</h3>
            <p>{{ $this->getActionDescription($action) }}</p>
        </div>

        <div class="info-section">
            <h3>📋 Informasi Pengajuan</h3>
            
            <div class="info-row">
                <span class="info-label">Project:</span>
                <span class="info-value">{{ $pengajuan->nameproject->nama_project }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Project Manager:</span>
                <span class="info-value">{{ $pengajuan->nameproject->user->name }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Yang Mengajukan:</span>
                <span class="info-value">{{ $pengajuan->user->name }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Tanggal Pengajuan:</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($pengajuan->tanggal_pengajuan)->format('d F Y') }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Tanggal Dibutuhkan:</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($pengajuan->tanggal_dibutuhkan)->format('d F Y') }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Status Saat Ini:</span>
                <span class="info-value">{{ $this->getStatusText($pengajuan->status) }}</span>
            </div>
        </div>

        @if($pengajuan->detail_barang)
        <div class="info-section">
            <h3>📦 Detail Barang</h3>
            <div class="barang-list">
                @foreach(json_decode($pengajuan->detail_barang, true) as $index => $barang)
                <div class="barang-item">
                    <div class="barang-name">{{ $barang['nama_barang'] ?? 'Tidak ada nama' }}</div>
                    <div class="barang-detail">
                        <strong>Jumlah:</strong> {{ $barang['jumlah_barang_diajukan'] ?? 0 }} unit<br>
                        <strong>Keterangan:</strong> {{ $barang['keterangan_barang'] ?? 'Tidak ada keterangan' }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($catatan)
        <div class="catatan-section">
            <h4>💬 Catatan:</h4>
            <p>{{ $catatan }}</p>
        </div>
        @endif

        @if($pengajuan->reject_reason)
        <div class="catatan-section" style="background-color: #fef2f2; border-left-color: #ef4444;">
            <h4>❌ Alasan Penolakan:</h4>
            <p>{{ $pengajuan->reject_reason }}</p>
        </div>
        @endif

        <div class="footer">
            <p>Email ini dikirim secara otomatis oleh Sistem Pengajuan Pengadaan.</p>
            <p>Jika Anda memerlukan bantuan, silakan hubungi administrator sistem.</p>
            <p><small>Dikirim pada: {{ now()->format('d F Y, H:i') }} WIB</small></p>
        </div>
    </div>
</body>
</html>

@php
    function getStatusClass($status) {
        $classes = [
            'pengajuan_terkirim' => 'status-pending',
            'pending_pm_review' => 'status-pending',
            'disetujui_pm_dikirim_ke_pengadaan' => 'status-approved',
            'ditolak_pm' => 'status-rejected',
            'disetujui_pengadaan' => 'status-approved',
            'ditolak_pengadaan' => 'status-rejected',
            'pengajuan_dikirim_ke_direksi' => 'status-processing',
            'approved_by_direksi' => 'status-approved',
            'reject_direksi' => 'status-rejected',
            'pengajuan_dikirim_ke_keuangan' => 'status-processing',
            'pending_keuangan' => 'status-pending',
            'process_keuangan' => 'status-processing',
            'execute_keuangan' => 'status-approved',
            'pengajuan_dikirim_ke_pengadaan_final' => 'status-processing',
            'pengajuan_dikirim_ke_admin' => 'status-processing',
            'processing' => 'status-processing',
            'ready_pickup' => 'status-approved',
            'completed' => 'status-completed',
        ];
        
        return $classes[$status] ?? 'status-pending';
    }

    function getStatusText($status) {
        $texts = [
            'pengajuan_terkirim' => 'Pengajuan Terkirim',
            'pending_pm_review' => 'Menunggu Review PM',
            'disetujui_pm_dikirim_ke_pengadaan' => 'Disetujui PM',
            'ditolak_pm' => 'Ditolak PM',
            'disetujui_pengadaan' => 'Disetujui Pengadaan',
            'ditolak_pengadaan' => 'Ditolak Pengadaan',
            'pengajuan_dikirim_ke_direksi' => 'Dikirim ke Direksi',
            'approved_by_direksi' => 'Disetujui Direksi',
            'reject_direksi' => 'Ditolak Direksi',
            'pengajuan_dikirim_ke_keuangan' => 'Dikirim ke Keuangan',
            'pending_keuangan' => 'Menunggu Review Keuangan',
            'process_keuangan' => 'Proses Keuangan',
            'execute_keuangan' => 'Keuangan Selesai',
            'pengajuan_dikirim_ke_pengadaan_final' => 'Kembali ke Pengadaan',
            'pengajuan_dikirim_ke_admin' => 'Dikirim ke Admin',
            'processing' => 'Sedang Diproses',
            'ready_pickup' => 'Siap Diambil',
            'completed' => 'Selesai',
        ];
        
        return $texts[$status] ?? 'Status Tidak Diketahui';
    }

    function getActionMessage($action) {
        $messages = [
            'pengajuan_terkirim' => 'Pengajuan Baru Telah Diterima',
            'pending_pm_review' => 'Pengajuan Sedang Direview oleh PM',
            'disetujui_pm_dikirim_ke_pengadaan' => 'Pengajuan Telah Disetujui PM',
            'ditolak_pm' => 'Pengajuan Ditolak oleh PM',
            'disetujui_pengadaan' => 'Pengajuan Disetujui Tim Pengadaan',
            'ditolak_pengadaan' => 'Pengajuan Ditolak Tim Pengadaan',
            'pengajuan_dikirim_ke_direksi' => 'Pengajuan Dikirim ke Direksi',
            'approved_by_direksi' => 'Pengajuan Disetujui Direksi',
            'reject_direksi' => 'Pengajuan Ditolak Direksi',
            'pengajuan_dikirim_ke_keuangan' => 'Pengajuan Dikirim ke Keuangan',
            'pending_keuangan' => 'Review Keuangan Dimulai',
            'process_keuangan' => 'Proses Keuangan Sedang Berlangsung',
            'execute_keuangan' => 'Proses Keuangan Telah Selesai',
            'pengajuan_dikirim_ke_pengadaan_final' => 'Pengajuan Kembali ke Pengadaan',
            'pengajuan_dikirim_ke_admin' => 'Pengajuan Dikirim ke Admin',
            'processing' => 'Proses Pengadaan Telah Dimulai',
            'ready_pickup' => 'Barang Sudah Siap Diambil',
            'completed' => 'Pengajuan Telah Selesai',
        ];
        
        return $messages[$action] ?? 'Notifikasi Pengajuan';
    }

    function getActionDescription($action) {
        $descriptions = [
            'pengajuan_terkirim' => 'Pengajuan Anda telah berhasil dikirim dan akan segera direview oleh Project Manager.',
            'pending_pm_review' => 'Pengajuan sedang dalam proses review oleh Project Manager. Mohon tunggu konfirmasi selanjutnya.',
            'disetujui_pm_dikirim_ke_pengadaan' => 'Pengajuan Anda telah disetujui oleh Project Manager dan dikirim ke tim pengadaan untuk proses selanjutnya.',
            'ditolak_pm' => 'Pengajuan Anda ditolak oleh Project Manager. Silakan lihat alasan penolakan dan ajukan kembali jika diperlukan.',
            'disetujui_pengadaan' => 'Pengajuan Anda telah disetujui oleh tim pengadaan dan akan diteruskan ke tahap selanjutnya.',
            'ditolak_pengadaan' => 'Pengajuan Anda ditolak oleh tim pengadaan. Silakan lihat alasan penolakan untuk informasi lebih lanjut.',
            'pengajuan_dikirim_ke_direksi' => 'Pengajuan telah dikirim ke direksi untuk persetujuan tingkat atas.',
            'approved_by_direksi' => 'Pengajuan Anda telah disetujui oleh direksi dan akan diteruskan ke keuangan.',
            'reject_direksi' => 'Pengajuan Anda ditolak oleh direksi. Silakan lihat alasan penolakan.',
            'pengajuan_dikirim_ke_keuangan' => 'Pengajuan telah dikirim ke tim keuangan untuk proses selanjutnya.',
            'pending_keuangan' => 'Tim keuangan telah mulai melakukan review terhadap pengajuan Anda.',
            'process_keuangan' => 'Pengajuan Anda sedang dalam proses oleh tim keuangan.',
            'execute_keuangan' => 'Proses keuangan telah selesai dan pengajuan akan diteruskan ke tahap selanjutnya.',
            'pengajuan_dikirim_ke_pengadaan_final' => 'Pengajuan kembali ke tim pengadaan untuk proses final.',
            'pengajuan_dikirim_ke_admin' => 'Pengajuan telah dikirim ke admin untuk proses pengadaan.',
            'processing' => 'Proses pengadaan barang telah dimulai oleh tim admin.',
            'ready_pickup' => 'Barang yang Anda ajukan sudah siap untuk diambil.',
            'completed' => 'Pengajuan Anda telah selesai dan barang telah diserahkan.',
        ];
        
        return $descriptions[$action] ?? 'Terdapat perubahan status pada pengajuan Anda.';
    }
@endphp