<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Barang Project Baru</title>
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
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }
        .content {
            padding: 30px;
        }
        .info-section {
            background-color: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 20px 0;
            border-radius: 0 4px 4px 0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }
        .info-row:last-child {
            margin-bottom: 0;
        }
        .info-label {
            font-weight: 600;
            color: #495057;
            min-width: 140px;
        }
        .info-value {
            color: #212529;
            flex: 1;
            text-align: right;
        }
        .barang-section {
            margin: 25px 0;
        }
        .barang-item {
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .barang-item h4 {
            color: #495057;
            margin: 0 0 10px 0;
            font-size: 16px;
        }
        .barang-detail {
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 8px;
        }
        .barang-spek {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            border-left: 3px solid #28a745;
            margin-top: 10px;
        }
        .status-badge {
            display: inline-block;
            background-color: #ffc107;
            color: #212529;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #dee2e6;
        }
        .footer p {
            margin: 0;
            color: #6c757d;
            font-size: 14px;
        }
        .action-note {
            background-color: #e3f2fd;
            border: 1px solid #2196f3;
            color: #0d47a1;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .action-note strong {
            color: #1976d2;
        }
        .file-list {
            background-color: #f8f9fa;
            border-radius: 4px;
            padding: 10px;
            margin-top: 10px;
        }
        .file-item {
            color: #495057;
            font-size: 14px;
            margin-bottom: 5px;
        }
        .file-item:last-child {
            margin-bottom: 0;
        }
        .file-item::before {
            content: "📎 ";
            margin-right: 5px;
        }
        @media (max-width: 600px) {
            .info-row {
                flex-direction: column;
            }
            .info-value {
                text-align: left;
                margin-top: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>🚀 Pengajuan Barang Project Baru</h1>
            <p>Telah dibuat pengajuan barang project yang memerlukan persetujuan</p>
        </div>

        <div class="content">
            <div class="info-section">
                <div class="info-row">
                    <span class="info-label">Yang Mengajukan:</span>
                    <span class="info-value">{{ $pengaju->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Nama Project:</span>
                    <span class="info-value"><strong>{{ $project->nama_project }}</strong></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Project Manager:</span>
                    <span class="info-value">{{ $projectManager->name ?? 'Tidak ditugaskan' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tanggal Pengajuan:</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($pengajuan->tanggal_pengajuan)->format('d F Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tanggal Dibutuhkan:</span>
                    <span class="info-value">{{ $pengajuan->tanggal_dibutuhkan ? \Carbon\Carbon::parse($pengajuan->tanggal_dibutuhkan)->format('d F Y') : 'Tidak ditentukan' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value">
                        <span class="status-badge">{{ ucfirst(str_replace('_', ' ', $pengajuan->status)) }}</span>
                    </span>
                </div>
            </div>

            @if($projectManager)
            <div class="action-note">
                <strong>Tindakan yang Diperlukan:</strong><br>
                Pengajuan ini memerlukan review dan persetujuan dari Project Manager (<strong>{{ $projectManager->name }}</strong>). 
                Silakan login ke sistem untuk melakukan review pengajuan.
            </div>
            @endif

            @if(count($detailBarang) > 0)
            <div class="barang-section">
                <h3 style="color: #495057; margin-bottom: 20px;">📦 Detail Barang yang Diajukan</h3>
                
                @foreach($detailBarang as $index => $barang)
                <div class="barang-item">
                    <h4>{{ $barang['nama_barang'] ?? 'Nama barang tidak tersedia' }}</h4>
                    <div class="barang-detail">
                        <strong>Jumlah:</strong> {{ $barang['jumlah_barang_diajukan'] ?? 0 }} Unit
                    </div>
                    
                    @if(isset($barang['keterangan_barang']) && !empty($barang['keterangan_barang']))
                    <div class="barang-spek">
                        <strong>Spesifikasi:</strong><br>
                        {{ $barang['keterangan_barang'] }}
                    </div>
                    @endif

                    @if(isset($barang['file_barang']) && !empty($barang['file_barang']) && is_array($barang['file_barang']))
                    <div class="file-list">
                        <strong>File Pendukung:</strong>
                        @foreach($barang['file_barang'] as $file)
                        <div class="file-item">{{ basename($file) }}</div>
                        @endforeach
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @endif

            @if(count($uploadedFiles) > 0)
            <div class="barang-section">
                <h3 style="color: #495057; margin-bottom: 15px;">📄 File Pendukung Project</h3>
                <div class="file-list">
                    @foreach($uploadedFiles as $file)
                    <div class="file-item">{{ basename($file) }}</div>
                    @endforeach
                </div>
            </div>
            @endif

            @if($pengajuan->catatan)
            <div class="info-section">
                <h4 style="color: #495057; margin-bottom: 10px;">💬 Catatan Tambahan</h4>
                <p style="margin: 0; color: #6c757d;">{{ $pengajuan->catatan }}</p>
            </div>
            @endif
        </div>

        <div class="footer">
            <p>Email ini dikirim otomatis oleh sistem. Mohon tidak membalas email ini.</p>
            <p style="margin-top: 10px;">
                <em>{{ config('app.name') }}</em>
            </p>
        </div>
    </div>
</body>
</html>