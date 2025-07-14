<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Operasional Dikirim ke Keuangan</title>
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
        .email-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            margin: -30px -30px 30px -30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .info-box {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .info-row {
            display: flex;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .info-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        .info-label {
            font-weight: bold;
            width: 150px;
            color: #555;
        }
        .info-value {
            flex: 1;
            color: #333;
        }
        .status-badge {
            display: inline-block;
            background: #28a745;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .detail-barang {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 15px;
            margin: 15px 0;
        }
        .detail-barang h3 {
            color: #856404;
            margin-top: 0;
            font-size: 16px;
        }
        .barang-item {
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 10px;
        }
        .barang-item:last-child {
            margin-bottom: 0;
        }
        .alert {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
        }
        .alert-info {
            color: #0c5460;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 0 0 8px 8px;
            margin: 30px -30px -30px -30px;
            text-align: center;
            border-top: 1px solid #e9ecef;
            font-size: 14px;
            color: #666;
        }
        .button {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin: 15px 0;
        }
        .button:hover {
            background: #0056b3;
        }
        .urgent {
            background: #dc3545;
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            .email-container {
                padding: 20px;
            }
            .header {
                margin: -20px -20px 20px -20px;
            }
            .footer {
                margin: 20px -20px -20px -20px;
            }
            .info-row {
                flex-direction: column;
            }
            .info-label {
                width: 100%;
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>📋 Pengajuan Dikirim ke Keuangan</h1>
        </div>

        <div class="alert alert-info">
            <strong>📨 Notifikasi:</strong> Pengajuan operasional telah dikirim ke tim keuangan dan memerlukan review Anda.
        </div>

        <div class="info-box">
            <div class="info-row">
                <div class="info-label">Yang Mengajukan:</div>
                <div class="info-value">{{ $user->name }} ({{ $user->email }})</div>
            </div>
            <div class="info-row">
                <div class="info-label">Tanggal Pengajuan:</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($pengajuan->tanggal_pengajuan)->format('d/m/Y') }}</div>
            </div>
            @if($pengajuan->tanggal_dibutuhkan)
            <div class="info-row">
                <div class="info-label">Tanggal Dibutuhkan:</div>
            </div>
            @endif
            <div class="info-row">
                <div class="info-label">Status:</div>
                <div class="info-value">
                    <span class="status-badge">Dikirim ke Keuangan</span>
                </div>
            </div>
        </div>

        @if($detailBarang && count($detailBarang) > 0)
        <div class="detail-barang">
            <h3>📦 Detail Barang yang Diajukan:</h3>
            @foreach($detailBarang as $index => $barang)
            <div class="barang-item">
                <strong>{{ $index + 1 }}. {{ $barang['nama_barang'] ?? 'Tidak disebutkan' }}</strong>
                @if(isset($barang['spesifikasi']))
                    <br><em>Spesifikasi: {{ $barang['spesifikasi'] }}</em>
                @endif
                @if(isset($barang['jumlah']))
                    <br>Jumlah: {{ $barang['jumlah'] }} {{ $barang['satuan'] ?? 'unit' }}
                @endif
                @if(isset($barang['perkiraan_harga']))
                    <br>Perkiraan Harga: Rp {{ number_format($barang['perkiraan_harga'], 0, ',', '.') }}
                @endif
                @if(isset($barang['keterangan']))
                    <br>Keterangan: {{ $barang['keterangan'] }}
                @endif
            </div>
            @endforeach
        </div>
        @endif

        <div class="alert alert-info">
            <strong>🔍 Tindakan yang Diperlukan:</strong>
            <br>
            • Silakan login ke sistem untuk melakukan review pengajuan ini
            <br>
            • Berikan catatan jika diperlukan
        </div>

        <div class="footer">
            <p><strong>Catatan Penting:</strong></p>
            <p>
                • Email ini dikirim secara otomatis dari sistem<br>
                • Mohon tidak membalas email ini<br>
                • Untuk pertanyaan, silakan hubungi admin sistem<br>
            </p>
        </div>
    </div>
</body>
</html>