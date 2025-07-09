<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Dikirim ke Pengadaan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, 'Roboto', 'Helvetica Neue', sans-serif;
            line-height: 1.6;
            color: #2d3748;
            background-color: #f7fafc;
            padding: 20px;
        }
        
        .email-container {
            max-width: 650px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 35px 40px;
            text-align: center;
            position: relative;
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" fill-opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" fill-opacity="0.1"/><circle cx="25" cy="75" r="1" fill="white" fill-opacity="0.1"/><circle cx="75" cy="25" r="1" fill="white" fill-opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .header h1 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }
        
        .status-badge {
            background-color: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            display: inline-block;
            position: relative;
            z-index: 1;
        }
        
        .content {
            padding: 40px;
        }
        
        .info-section {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
            position: relative;
        }
        
        .info-section::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: linear-gradient(to bottom, #667eea, #764ba2);
            border-radius: 0 2px 2px 0;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        
        .info-label {
            font-size: 12px;
            font-weight: 600;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-value {
            font-size: 14px;
            color: #2d3748;
            font-weight: 500;
        }
        
        .status-section {
            background: linear-gradient(135deg, #e6fffa 0%, #b2f5ea 100%);
            border: 1px solid #81e6d9;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .status-section h3 {
            color: #234e52;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .status-description {
            color: #2c7a7b;
            font-size: 14px;
        }
        
        .items-section {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .item-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 15px;
            position: relative;
            transition: all 0.2s ease;
        }
        
        .item-card:last-child {
            margin-bottom: 0;
        }
        
        .item-card:hover {
            border-color: #cbd5e0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .item-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }
        
        .item-name {
            font-size: 14px;
            font-weight: 600;
            color: #1a202c;
            line-height: 1.4;
        }
        
        .item-quantity {
            background-color: #667eea;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }
        
        .item-description {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 12px;
            font-size: 13px;
            color: #4a5568;
            line-height: 1.5;
        }
        
        .item-description strong {
            color: #2d3748;
        }
        
        .action-section {
            background: linear-gradient(135deg, #fef5e7 0%, #fed7aa 100%);
            border: 1px solid #f6ad55;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .action-section h4 {
            color: #c05621;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .action-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .action-list li {
            padding: 8px 0;
            padding-left: 20px;
            position: relative;
            color: #744210;
            font-size: 14px;
        }
        
        .action-list li::before {
            content: '→';
            position: absolute;
            left: 0;
            color: #ed8936;
            font-weight: bold;
        }
        
        .message-section {
            background-color: #f8fafc;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin-bottom: 30px;
            font-size: 14px;
            line-height: 1.6;
            color: #4a5568;
        }
        
        .footer {
            background-color: #f7fafc;
            border-top: 1px solid #e2e8f0;
            padding: 25px 40px;
            text-align: center;
            color: #718096;
            font-size: 12px;
            line-height: 1.5;
        }
        
        .footer p {
            margin-bottom: 8px;
        }
        
        .footer p:last-child {
            margin-bottom: 0;
        }
        
        .divider {
            height: 1px;
            background: linear-gradient(to right, transparent, #e2e8f0, transparent);
            margin: 25px 0;
        }
        
        @media (max-width: 600px) {
            .email-container {
                margin: 10px;
                border-radius: 8px;
            }
            
            .header {
                padding: 25px 20px;
            }
            
            .content {
                padding: 25px 20px;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .item-header {
                flex-direction: column;
                gap: 10px;
            }
            
            .item-quantity {
                align-self: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>📤 Pengajuan Dikirim ke Tim Pengadaan</h1>
            <div class="status-badge">Dalam Proses</div>
        </div>

        <div class="content">
            <div class="info-section">
                <div class="section-title">
                    📋 Informasi Pengajuan
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Pengaju</span>
                        <span class="info-value">{{ $namaPengaju }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Tanggal Pengajuan</span>
                        <span class="info-value">{{ $tanggalPengajuan }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Tanggal Dibutuhkan</span>
                        <span class="info-value">{{ $tanggalDibutuhkan }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Dikirim pada</span>
                        <span class="info-value">{{ \Carbon\Carbon::now()->format('d F Y H:i') }}</span>
                    </div>
                    @if(isset($totalItem))
                    <div class="info-item">
                        <span class="info-label">Total Item</span>
                        <span class="info-value">{{ $totalItem }} Item</span>
                    </div>
                    @endif
                </div>
            </div>

            <div class="status-section">
                <h3>✅ Status Pengajuan</h3>
                <p class="status-description">Pengajuan telah berhasil dikirim ke Tim Pengadaan untuk ditinjau</p>
            </div>

            <div class="items-section">
                <div class="section-title">
                    📦 Detail Barang yang Diajukan
                </div>
                
                @if($pengajuan->detail_barang && count($pengajuan->detail_barang) > 0)
                    @foreach($pengajuan->detail_barang as $index => $item)
                        <div class="item-card">
                            <div class="item-header">
                                <div class="item-name">
                                    {{ $index + 1 }}. {{ $item['nama_barang'] ?? 'Nama tidak tersedia' }}
                                </div>
                                <div class="item-quantity">
                                    {{ $item['jumlah_barang_diajukan'] ?? 0 }} Unit
                                </div>
                            </div>
                            
                            @if(isset($item['keterangan_barang']) && !empty($item['keterangan_barang']))
                                <div class="item-description">
                                    <strong>Spesifikasi:</strong><br>
                                    {{ $item['keterangan_barang'] }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                @else
                    <div class="item-card">
                        <p style="color: #718096; font-style: italic; text-align: center;">
                            Tidak ada detail barang yang tersedia.
                        </p>
                    </div>
                @endif
            </div>

        </div>

        <div class="footer">
            <p><strong>Email Otomatis Sistem Pengadaan</strong></p>
            <p>Email ini dikirim secara otomatis oleh sistem. Mohon tidak membalas email ini.</p>
            <p>Silakan akses sistem untuk memberikan keputusan pada pengajuan ini.</p>
        </div>
    </div>
</body>
</html>