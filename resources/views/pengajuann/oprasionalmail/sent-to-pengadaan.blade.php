<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Operasional Dikirim ke Tim Pengadaan</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .email-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .email-header p {
            margin: 5px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }
        .email-body {
            padding: 30px;
        }
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .info-box h3 {
            margin: 0 0 10px 0;
            color: #007bff;
            font-size: 16px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 20px 0;
        }
        .info-item {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 6px;
            border-left: 3px solid #28a745;
        }
        .info-item strong {
            color: #495057;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-item div {
            margin-top: 5px;
            font-size: 14px;
            color: #212529;
        }
        .barang-list {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            margin: 20px 0;
        }
        .barang-header {
            background: #f8f9fa;
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
            font-weight: 600;
            color: #495057;
        }
        .barang-item {
            padding: 15px;
            border-bottom: 1px solid #f1f3f4;
        }
        .barang-item:last-child {
            border-bottom: none;
        }
        .barang-name {
            font-weight: 600;
            color: #212529;
            margin-bottom: 5px;
        }
        .barang-qty {
            color: #007bff;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .barang-desc {
            color: #6c757d;
            font-size: 14px;
            line-height: 1.4;
        }
        .action-box {
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            border-radius: 6px;
            padding: 20px;
            margin: 25px 0;
            text-align: center;
        }
        .btn-primary {
            background: #007bff;
            color: white;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 6px;
            display: inline-block;
            font-weight: 600;
            margin: 10px 0;
            box-shadow: 0 2px 4px rgba(0,123,255,0.2);
        }
        .btn-primary:hover {
            background: #0056b3;
        }
        .email-footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #dee2e6;
            color: #6c757d;
            font-size: 12px;
        }
        .status-badge {
            display: inline-block;
            background: #ffc107;
            color: #212529;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        @media (max-width: 600px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
            .email-body {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>Pengajuan Operasional</h1>
            <p>Dikirim ke Tim Pengadaan</p>
        </div>
        
        <div class="email-body">
            <div class="info-box">
                <h3>📋 Informasi Pengajuan</h3>
                <p><strong>Nomor Pengajuan:</strong> {{ $pengajuan->nomor_pengajuan }}</p>
                <p><strong>Status:</strong> <span class="status-badge">Dikirim ke Tim Pengadaan</span></p>
            </div>

            <div class="info-grid">
                <div class="info-item">
                    <strong>Pengaju</strong>
                    <div>{{ $pengaju->name }}</div>
                </div>
                <div class="info-item">
                    <strong>Tanggal Pengajuan</strong>
                    <div>{{ \Carbon\Carbon::parse($pengajuan->tanggal_pengajuan)->locale('id')->isoFormat('DD MMMM YYYY') }}</div>
                </div>
                <div class="info-item">
                    <strong>Tanggal Dibutuhkan</strong>
                    <div>{{ \Carbon\Carbon::parse($pengajuan->tanggal_dibutuhkan)->locale('id')->isoFormat('DD MMMM YYYY') }}</div>
                </div>
                <div class="info-item">
                    <strong>Jumlah Item</strong>
                    <div>{{ count($detailBarang) }} item</div>
                </div>
            </div>

            <div class="barang-list">
                <div class="barang-header">
                    📦 Detail Barang yang Diajukan
                </div>
                @foreach($detailBarang as $index => $barang)
                <div class="barang-item">
                    <div class="barang-name">{{ $barang['nama_barang'] }}</div>
                    <div class="barang-qty">Jumlah: {{ $barang['jumlah_barang_diajukan'] }} unit</div>
                    <div class="barang-desc">{{ $barang['keterangan_barang'] }}</div>
                </div>
                @endforeach
            </div>
        </div>
        
        <div class="email-footer">
            <p>Email ini dikirim secara otomatis.</p>
            <p>Harap tidak membalas email ini. Jika ada pertanyaan, silakan hubungi admin sistem.</p>
        </div>
    </div>
</body>
</html>