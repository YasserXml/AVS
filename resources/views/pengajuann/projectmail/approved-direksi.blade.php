<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Barang Project Disetujui Direksi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
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
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .info-section h3 {
            color: #1e293b;
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 18px;
            border-bottom: 2px solid #10b981;
            padding-bottom: 5px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }
        .info-item {
            background-color: white;
            padding: 12px;
            border-radius: 6px;
            border-left: 4px solid #10b981;
        }
        .info-item strong {
            color: #374151;
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
        }
        .info-item span {
            color: #6b7280;
            font-size: 16px;
        }
        .barang-list {
            background-color: white;
            border-radius: 6px;
            overflow: hidden;
        }
        .barang-item {
            padding: 15px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .barang-item:last-child {
            border-bottom: none;
        }
        .barang-info {
            flex: 1;
        }
        .barang-name {
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 5px;
        }
        .barang-desc {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 5px;
        }
        .barang-qty {
            background-color: #dbeafe;
            color: #1e40af;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .catatan-section {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .catatan-section h4 {
            color: #92400e;
            margin-top: 0;
            margin-bottom: 10px;
        }
        .catatan-text {
            color: #78350f;
            font-style: italic;
        }
        .next-steps {
            background-color: #dbeafe;
            border: 1px solid #3b82f6;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }
        .next-steps h3 {
            color: #1e40af;
            margin-top: 0;
            margin-bottom: 10px;
        }
        .next-steps p {
            color: #1e3a8a;
            margin: 5px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }
        .timestamp {
            background-color: #f3f4f6;
            padding: 10px;
            border-radius: 6px;
            text-align: center;
            font-size: 14px;
            color: #6b7280;
            margin-top: 20px;
        }
        @media (max-width: 600px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
            .barang-item {
                flex-direction: column;
                align-items: flex-start;
            }
            .barang-qty {
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">

        <div class="info-section">
            <h3>📋 Informasi Pengajuan</h3>
            <div class="info-grid">
                <div class="info-item">
                    <strong>Nama Project:</strong>
                    <span>{{ $projectName }}</span>
                </div>
                <div class="info-item">
                    <strong>Pengaju:</strong>
                    <span>{{ $pengajuName }}</span>
                </div>
                <div class="info-item">
                    <strong>Tanggal Pengajuan:</strong>
                    <span>{{ \Carbon\Carbon::parse($tanggalPengajuan)->format('d F Y') }}</span>
                </div>
                <div class="info-item">
                    <strong>Tanggal Dibutuhkan:</strong>
                    <span>{{ $tanggalDibutuhkan ? \Carbon\Carbon::parse($tanggalDibutuhkan)->format('d F Y') : 'Belum ditentukan' }}</span>
                </div>
                <div class="info-item">
                    <strong>Disetujui Oleh:</strong>
                    <span>{{ $approvedBy->name ?? 'Direksi' }}</span>
                </div>
            </div>
        </div>

        @if($detailBarang && count($detailBarang) > 0)
        <div class="info-section">
            <h3>📦 Detail Barang yang Disetujui</h3>
            <div class="barang-list">
                @foreach($detailBarang as $index => $barang)
                <div class="barang-item">
                    <div class="barang-info">
                        <div class="barang-name">{{ $barang['nama_barang'] ?? 'Nama barang tidak tersedia' }}</div>
                        <div class="barang-desc">{{ $barang['keterangan_barang'] ?? 'Tidak ada keterangan' }}</div>
                    </div>
                    <div class="barang-qty">{{ $barang['jumlah_barang_diajukan'] ?? 0 }} Unit</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($catatan)
        <div class="catatan-section">
            <h4>📝 Catatan Direksi</h4>
            <div class="catatan-text">{{ $catatan }}</div>
        </div>
        @endif

        <div class="next-steps">
            <h3>🚀 Langkah Selanjutnya</h3>
            <p>➡️ Pengajuan akan dikirim ke tim keuangan untuk proses selanjutnya</p>
            <p>📊 Anda dapat memantau progress pengajuan melalui sistem</p>
        </div>

        <div class="timestamp">
            <strong>Waktu Persetujuan:</strong> 
            {{ $approvedAt ? \Carbon\Carbon::parse($approvedAt)->format('d F Y, H:i') : 'Belum tersedia' }} WIB
        </div>

        <div class="footer">
            <p>Email ini dikirim secara otomatis oleh sistem manajemen pengajuan project.</p>
            <p>Mohon jangan membalas email ini.</p>
        </div>
    </div>
</body>
</html>