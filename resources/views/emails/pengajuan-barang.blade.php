<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Barang Baru</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin: 0;
            padding: 20px 0;
            min-height: 100vh;
        }
        
        .email-wrapper {
            max-width: 650px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            background: linear-gradient(135deg, #AF0909 0%, #8B0000 100%);
            padding: 30px 20px;
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
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="white" opacity="0.1"><polygon points="0,0 1000,0 1000,80 0,100"/></svg>');
            background-size: cover;
        }
        
        .logo-container {
            background-color: #ffffff;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin: 0 auto 15px;
            padding: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            position: relative;
        }
        
        .logo-container img {
            max-width: 100%;
            height: auto;
            max-height: 50px;
        }
        
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            position: relative;
        }
        
        .header p {
            color: rgba(255, 255, 255, 0.9);
            margin: 8px 0 0 0;
            font-size: 14px;
            font-weight: 400;
            position: relative;
        }
        
        .divider {
            height: 6px;
            background: linear-gradient(to right, #AF0909 0%, #69606d 50%, #455056 100%);
        }
        
        .content {
            padding: 30px;
            background-color: #ffffff;
        }
        
        .status-badge {
            display: inline-block;
            background: linear-gradient(135deg, #FF6B35, #AF0909);
            color: white;
            font-size: 13px;
            padding: 8px 16px;
            border-radius: 25px;
            margin-bottom: 20px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 10px rgba(175, 9, 9, 0.3);
        }
        
        .info-box {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            padding: 25px;
            margin: 20px 0;
            border-left: 5px solid #AF0909;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .info-row {
            display: flex;
            margin-bottom: 12px;
            align-items: center;
        }
        
        .info-label {
            font-weight: 600;
            color: #455056;
            min-width: 140px;
            font-size: 14px;
        }
        
        .info-value {
            color: #212529;
            font-size: 14px;
            flex: 1;
        }
        
        .status-pending {
            background: linear-gradient(135deg, #ffc107, #e0a800);
            color: #000;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        .keterangan-box {
            background: linear-gradient(135deg, #e7f3ff 0%, #cce7ff 100%);
            padding: 20px;
            border-radius: 12px;
            margin: 20px 0;
            border-left: 5px solid #007bff;
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.1);
        }
        
        .keterangan-box strong {
            color: #004085;
            font-size: 16px;
        }
        
        .keterangan-box p {
            margin: 10px 0 0 0;
            color: #004085;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .items-section {
            margin-top: 30px;
        }
        
        .items-header {
            background: linear-gradient(135deg, #AF0909 0%, #8B0000 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 12px 12px 0 0;
            font-size: 18px;
            font-weight: 600;
            text-align: center;
        }
        
        .items-container {
            border: 2px solid #AF0909;
            border-top: none;
            border-radius: 0 0 12px 12px;
            overflow: hidden;
        }
        
        .item {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border-bottom: 1px solid #dee2e6;
            padding: 20px;
            transition: all 0.3s ease;
        }
        
        .item:hover {
            background: linear-gradient(135deg, #e9ecef 0%, #f8f9fa 100%);
            transform: translateX(5px);
        }
        
        .item:last-child {
            border-bottom: none;
        }
        
        .item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .item-title {
            font-weight: 600;
            color: #AF0909;
            font-size: 16px;
            flex: 1;
        }
        
        .item-quantity {
            background: linear-gradient(135deg, #28a745, #20c55e);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        .item-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
            font-size: 14px;
        }
        
        .item-detail {
            display: flex;
            flex-direction: column;
        }
        
        .item-detail strong {
            color: #455056;
            font-weight: 600;
            margin-bottom: 2px;
        }
        
        .item-detail span {
            color: #212529;
        }
        
        .warning {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-top: 15px;
            font-size: 13px;
            border-left: 4px solid #dc3545;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .warning-icon {
            font-size: 16px;
            flex-shrink: 0;
        }
        
        .action-notice {
            background: linear-gradient(135deg, #d1ecf1 0%, #b8daff 100%);
            color: #0c5460;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            margin: 25px 0;
            border-left: 5px solid #17a2b8;
            box-shadow: 0 2px 10px rgba(23, 162, 184, 0.2);
        }
        
        .action-notice strong {
            font-size: 16px;
            display: block;
            margin-bottom: 5px;
        }
        
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #AF0909, #8B0000);
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            margin: 15px 0 0 0;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(175, 9, 9, 0.3);
        }
        
        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(175, 9, 9, 0.4);
        }
        
        .footer {
            background: linear-gradient(135deg, #455056 0%, #2c3e50 100%);
            text-align: center;
            padding: 25px;
            color: #ffffff;
        }
        
        .footer p {
            margin: 5px 0;
            font-size: 14px;
        }
        
        .footer-title {
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 10px;
        }
        
        .footer-subtitle {
            opacity: 0.8;
            font-size: 13px;
        }
        
        .footer-timestamp {
            font-size: 12px;
            opacity: 0.7;
            margin-top: 10px;
        }
        
        @media (max-width: 600px) {
            .email-wrapper {
                margin: 0 10px;
                border-radius: 12px;
            }
            
            .content {
                padding: 20px;
            }
            
            .item-details {
                grid-template-columns: 1fr;
                gap: 8px;
            }
            
            .item-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="header">
            <div class="logo-container">
                <img src="{{ $message->embed(public_path('images/Logo(1).webp')) }}" alt="Company Logo">
            </div>
            <h1>Pengajuan Barang Baru</h1>
            <p>Notifikasi pengajuan barang yang membutuhkan persetujuan</p>
        </div>

        <div class="divider"></div>

        <div class="content">
            <span class="status-badge">Menunggu Persetujuan</span>

            <div class="info-box">
                <div class="info-row">
                    <span class="info-label">Nama Pengaju:</span>
                    <span class="info-value"><strong>{{ $pengaju->name }}</strong></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $pengaju->email }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tanggal:</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($pengajuanItems->first()->tanggal_pengajuan)->format('d F Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Diperuntukan:</span>
                    <span class="info-value">
                        @if($pengajuanItems->first()->status_barang === 'project')
                            Project: <strong>{{ $pengajuanItems->first()->nama_project }}</strong>
                        @else
                            Operasional Kantor
                        @endif
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Total Barang:</span>
                    <span class="info-value"><strong>{{ $totalBarang }} item</strong></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value">
                        <span class="status-pending">Menunggu Persetujuan</span>
                    </span>
                </div>
            </div>

            @if($pengajuanItems->first()->keterangan)
            <div class="keterangan-box">
                <strong>💬 Keterangan Pengaju:</strong>
                <p>{{ $pengajuanItems->first()->keterangan }}</p>
            </div>
            @endif

            <div class="items-section">
                <div class="items-header">
                    📦 Daftar Barang Diajukan ({{ $totalBarang }} Item)
                </div>
                <div class="items-container">
                    @foreach($pengajuanItems as $index => $item)
                    <div class="item">
                        <div class="item-header">
                            <div class="item-title">
                                {{ $index + 1 }}. {{ $item->barang->nama_barang ?? 'Barang Tidak Ditemukan' }}
                            </div>
                            <div class="item-quantity">
                                {{ $item->Jumlah_barang_diajukan }} Unit
                            </div>
                        </div>
                        
                        <div class="item-details">
                            <div class="item-detail">
                                <strong>🔖 Kode Barang:</strong>
                                <span>{{ $item->barang->kode_barang ?? '-' }}</span>
                            </div>
                            <div class="item-detail">
                                <strong>📂 Kategori:</strong>
                                <span>{{ $item->barang->kategori->nama_kategori ?? '-' }}</span>
                            </div>
                            <div class="item-detail">
                                <strong>📊 Stok Tersedia:</strong>
                                <span>{{ $item->barang->jumlah_barang ?? 0 }} unit</span>
                            </div>
                            @if($item->barang && $item->barang->serial_number)
                            <div class="item-detail">
                                <strong>🔢 Serial Number:</strong>
                                <span>{{ $item->barang->serial_number }}</span>
                            </div>
                            @endif
                        </div>

                        @if($item->barang && $item->barang->jumlah_barang < $item->Jumlah_barang_diajukan)
                        <div class="warning">
                            <span class="warning-icon">⚠️</span>
                            <div>
                                <strong>Perhatian:</strong> Stok tidak mencukupi! 
                                Diajukan: {{ $item->Jumlah_barang_diajukan }} unit, Tersedia: {{ $item->barang->jumlah_barang }} unit
                            </div>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="divider"></div>

        <div class="footer">
            <p class="footer-title">{{ config('app.name', 'Sistem Manajemen Barang') }}</p>
            <p class="footer-subtitle">Email otomatis - Mohon tidak membalas email ini</p>
            <p class="footer-timestamp">Dikirim pada: {{ now()->format('d F Y, H:i') }} WIB</p>
        </div>
    </div>
</body>
</html>