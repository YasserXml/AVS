<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Barang Project Disetujui</title>
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
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #10b981;
        }
        .header h1 {
            color: #10b981;
            margin: 0;
            font-size: 24px;
        }
        .status-badge {
            display: inline-block;
            background-color: #10b981;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 10px;
        }
        .info-section {
            margin-bottom: 25px;
        }
        .info-section h3 {
            color: #374151;
            margin-bottom: 15px;
            font-size: 18px;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 5px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        .info-item {
            background-color: #f9fafb;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #10b981;
        }
        .info-item label {
            font-weight: bold;
            color: #6b7280;
            font-size: 12px;
            text-transform: uppercase;
            display: block;
            margin-bottom: 5px;
        }
        .info-item span {
            color: #111827;
            font-size: 14px;
        }
        .full-width {
            grid-column: 1 / -1;
        }
        .barang-list {
            background-color: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }
        .barang-item {
            background-color: white;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 6px;
            border-left: 4px solid #3b82f6;
        }
        .barang-item:last-child {
            margin-bottom: 0;
        }
        .barang-name {
            font-weight: bold;
            color: #111827;
            font-size: 16px;
            margin-bottom: 8px;
        }
        .barang-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 10px;
        }
        .barang-detail {
            font-size: 14px;
        }
        .barang-detail label {
            font-weight: bold;
            color: #6b7280;
        }
        .barang-keterangan {
            background-color: #f3f4f6;
            padding: 10px;
            border-radius: 4px;
            font-size: 14px;
            color: #4b5563;
            margin-top: 10px;
        }
        .catatan-section {
            background-color: #fef3c7;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #f59e0b;
            margin-bottom: 25px;
        }
        .catatan-section h4 {
            color: #92400e;
            margin: 0 0 10px 0;
            font-size: 16px;
        }
        .catatan-section p {
            color: #78350f;
            margin: 0;
            font-style: italic;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }
        .footer p {
            margin: 5px 0;
        }
        .next-steps {
            background-color: #dbeafe;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #3b82f6;
            margin-bottom: 25px;
        }
        .next-steps h4 {
            color: #1e40af;
            margin: 0 0 10px 0;
            font-size: 16px;
        }
        .next-steps p {
            color: #1e3a8a;
            margin: 0;
        }
        
        @media (max-width: 600px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
            .barang-details {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>✅ Pengajuan Barang Project Disetujui</h1>
            <div class="status-badge">Disetujui Pengadaan</div>
        </div>

        <div class="info-section">
            <h3>📋 Informasi Pengajuan</h3>
            <div class="info-grid">
                <div class="info-item">
                    <label>Nama Project</label>
                    <span>{{ $pengajuan->nameproject->nama_project ?? 'Tidak ada' }}</span>
                </div>
                <div class="info-item">
                    <label>Tanggal Pengajuan</label>
                    <span>{{ \Carbon\Carbon::parse($pengajuan->tanggal_pengajuan)->format('d F Y') }}</span>
                </div>
                <div class="info-item">
                    <label>Tanggal Dibutuhkan</label>
                    <span>{{ $pengajuan->tanggal_dibutuhkan ? \Carbon\Carbon::parse($pengajuan->tanggal_dibutuhkan)->format('d F Y') : 'Tidak ditentukan' }}</span>
                </div>
                <div class="info-item">
                    <label>Yang Mengajukan</label>
                    <span>{{ $pengajuan->user->name ?? 'Tidak diketahui' }}</span>
                </div>
                <div class="info-item">
                    <label>Project Manager</label>
                    <span>{{ $pengajuan->nameproject->user->name ?? 'Tidak ada PM' }}</span>
                </div>
            </div>
        </div>

        @if($catatan)
        <div class="catatan-section">
            <h4>💭 Catatan dari Tim Pengadaan</h4>
            <p>{{ $catatan }}</p>
        </div>
        @endif
        
        <div class="info-section">
            <h3>📦 Detail Barang yang Diajukan</h3>
            <div class="barang-list">
                @if($pengajuan->detail_barang && is_array($pengajuan->detail_barang))
                    @foreach($pengajuan->detail_barang as $index => $barang)
                    <div class="barang-item">
                        <div class="barang-name">{{ $barang['nama_barang'] ?? 'Nama tidak tersedia' }}</div>
                        <div class="barang-details">
                            <div class="barang-detail">
                                <label>Jumlah:</label> {{ $barang['jumlah_barang_diajukan'] ?? 0 }} unit
                            </div>
                            <div class="barang-detail">
                                <label>Item ke:</label> {{ $index + 1 }}
                            </div>
                        </div>
                        @if(isset($barang['keterangan_barang']) && $barang['keterangan_barang'])
                        <div class="barang-keterangan">
                            <strong>Keterangan:</strong> {{ $barang['keterangan_barang'] }}
                        </div>
                        @endif
                    </div>
                    @endforeach
                @else
                    <p>Tidak ada detail barang yang tersedia.</p>
                @endif
            </div>
        </div>

        <div class="info-section">
            <h3>📊 Status Pengajuan</h3>
            <div class="info-grid">
                <div class="info-item">
                    <label>Status Saat Ini</label>
                    <span>{{ ucfirst(str_replace('_', ' ', $pengajuan->status)) }}</span>
                </div>
                <div class="info-item">
                    <label>Disetujui Oleh</label>
                    <span>{{ $approvedBy->name ?? 'Tim Pengadaan' }}</span>
                </div>
                @if($pengajuan->approved_at)
                <div class="info-item">
                    <label>Tanggal Disetujui</label>
                    <span>{{ \Carbon\Carbon::parse($pengajuan->approved_at)->format('d F Y, H:i') }}</span>
                </div>
                @endif
            </div>
        </div>

        <div class="footer">
            <p>Email ini dikirim secara otomatis oleh sistem. Mohon tidak membalas email ini.</p>
            <p>Jika ada pertanyaan, silakan hubungi Administrator sistem.</p>
            <p><em>Dikirim pada: {{ now()->format('d F Y, H:i') }} WIB</em></p>
        </div>
    </div>
</body>
</html>