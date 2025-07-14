<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Operasional - Menunggu Persetujuan Direksi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8fafc;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 24px;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .header p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .content {
            padding: 30px;
        }
        
        .alert {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
            border-left: 4px solid #f39c12;
        }
        
        .alert-icon {
            display: inline-block;
            margin-right: 8px;
            font-size: 18px;
        }
        
        .info-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 24px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 12px;
            align-items: center;
        }
        
        .info-row:last-child {
            margin-bottom: 0;
        }
        
        .info-label {
            font-weight: 600;
            color: #495057;
            min-width: 140px;
            display: inline-block;
        }
        
        .info-value {
            color: #212529;
            flex: 1;
        }
        
        .progress-section {
            margin-bottom: 24px;
        }
        
        .progress-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
        }
        
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 4px;
            transition: width 0.3s ease;
        }
        
        .progress-text {
            font-size: 14px;
            color: #6c757d;
            margin-top: 4px;
        }
        
        .items-section {
            margin-bottom: 24px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #495057;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .item-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 12px;
            transition: box-shadow 0.2s ease;
        }
        
        .item-card:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .item-card:last-child {
            margin-bottom: 0;
        }
        
        .item-name {
            font-weight: 600;
            color: #212529;
            font-size: 16px;
            margin-bottom: 8px;
        }
        
        .item-quantity {
            color: #667eea;
            font-weight: 500;
            margin-bottom: 8px;
        }
        
        .item-description {
            color: #6c757d;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .action-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin-bottom: 24px;
        }
        
        .action-title {
            font-size: 18px;
            font-weight: 600;
            color: #495057;
            margin-bottom: 12px;
        }
        
        .action-description {
            color: #6c757d;
            margin-bottom: 20px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s ease;
            margin: 0 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }
        
        .footer p {
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 8px;
        }
        
        .footer-links {
            margin-top: 16px;
        }
        
        .footer-links a {
            color: #667eea;
            text-decoration: none;
            margin: 0 12px;
            font-size: 14px;
        }
        
        .footer-links a:hover {
            text-decoration: underline;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        @media (max-width: 600px) {
            .container {
                margin: 0;
                border-radius: 0;
            }
            
            .header {
                padding: 20px;
            }
            
            .content {
                padding: 20px;
            }
            
            .info-row {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .info-label {
                margin-bottom: 4px;
                min-width: auto;
            }
            
            .btn {
                display: block;
                margin: 8px 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>📋 Pengajuan Operasional</h1>
            <p>Menunggu Persetujuan Direksi</p>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Alert -->
            <div class="alert">
                <span class="alert-icon">⚠️</span>
                <strong>Perhatian:</strong> Pengajuan operasional baru telah dikirim dan memerlukan persetujuan dari Direksi.
            </div>

            <!-- Progress Section -->
            <div class="progress-section">
                <div class="progress-label">Progress Pengajuan</div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: {{ $progressPercentage }}%"></div>
                </div>
                <div class="progress-text">{{ $progressPercentage }}% - {{ $statusText }}</div>
            </div>

            <!-- Informasi Pengajuan -->
            <div class="info-section">
                <div class="info-row">
                    <div class="info-label">Pengaju:</div>
                    <div class="info-value">{{ $pengaju->name }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Email Pengaju:</div>
                    <div class="info-value">{{ $pengaju->email }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Tanggal Pengajuan:</div>
                    <div class="info-value">{{ \Carbon\Carbon::parse($pengajuan->tanggal_pengajuan)->format('d F Y') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Tanggal Dibutuhkan:</div>
                    <div class="info-value">{{ \Carbon\Carbon::parse($pengajuan->tanggal_dibutuhkan)->format('d F Y') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Status:</div>
                    <div class="info-value">
                        <span class="status-badge status-warning">{{ $statusText }}</span>
                    </div>
                </div>
            </div>

            <!-- Detail Barang -->
            <div class="items-section">
                <h2 class="section-title">📦 Detail Barang yang Diajukan</h2>
                @if($detailBarang && is_array($detailBarang))
                    @foreach($detailBarang as $index => $item)
                        <div class="item-card">
                            <div class="item-name">{{ $item['nama_barang'] ?? 'Nama barang tidak tersedia' }}</div>
                            <div class="item-quantity">
                                <strong>Jumlah:</strong> {{ $item['jumlah_barang_diajukan'] ?? 0 }} Unit
                            </div>
                            <div class="item-description">
                                <strong>Keterangan:</strong> {{ $item['keterangan_barang'] ?? 'Tidak ada keterangan' }}
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="item-card">
                        <div class="item-description">Detail barang tidak tersedia</div>
                    </div>
                @endif
            </div>

            <!-- Informasi Tambahan -->
            <div class="info-section">
                <h3 style="margin-bottom: 12px; color: #495057;">📝 Informasi Tambahan</h3>
                <div class="info-row">
                    <div class="info-label">Dibuat:</div>
                    <div class="info-value">{{ \Carbon\Carbon::parse($pengajuan->created_at)->format('d F Y, H:i') }} WIB</div>
                </div>
                @if($pengajuan->uploaded_files)
                    <div class="info-row">
                        <div class="info-label">File Pendukung:</div>
                        <div class="info-value">
                            @php
                                $files = is_string($pengajuan->uploaded_files) ? json_decode($pengajuan->uploaded_files, true) : $pengajuan->uploaded_files;
                            @endphp
                            @if($files && is_array($files))
                                {{ count($files) }} file terlampir
                            @else
                                Tidak ada file
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Email ini dikirim secara otomatis oleh sistem. Mohon tidak membalas email ini.</p>
        </div>
    </div>
</body>
</html>