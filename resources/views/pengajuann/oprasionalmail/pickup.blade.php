{{-- 3. Perbaikan pickup.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barang Siap Diambil</title>
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
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .status-badge {
            background-color: #3b82f6;
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 20px;
        }
        .info-box {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #3b82f6;
        }
        .info-row {
            margin-bottom: 10px;
        }
        .label {
            font-weight: bold;
            color: #374151;
        }
        .value {
            color: #6b7280;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }
        .action-box {
            background-color: #fef3c7;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #f59e0b;
        }
        .highlight {
            background-color: #ecfdf5;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border: 1px solid #10b981;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="color: #3b82f6; margin: 0;">📦 Barang Siap Diambil</h1>
            <div class="status-badge">SIAP DIAMBIL</div>
        </div>

        <div class="highlight">
            <h3 style="color: #10b981; margin-top: 0;">✅ Barang Anda sudah siap untuk diambil!</h3>
            <p>Silakan datang ke divisi inventory untuk mengambil barang yang telah Anda ajukan.</p>
        </div>

        <div class="info-box">
            <div class="info-row">
                <span class="label">Nama Pengaju:</span>

                <span class="value">{{ $namaPengaju }}</span>
            </div>
            <div class="info-row">
                <span class="label">Tanggal Pengajuan:</span>
                <span class="value">{{ $tanggalPengajuan }}</span>
            </div>
            <div class="info-row">
                <span class="label">Tanggal Dibutuhkan:</span>
                <span class="value">{{ $tanggalDibutuhkan }}</span>
            </div>
            <div class="info-row">
                <span class="label">Status diubah pada:</span>
                <span class="value">{{ \Carbon\Carbon::now()->format('d F Y H:i') }}</span>
            </div>
            @if(isset($totalItem))
            <div class="info-row">
                <span class="label">Total Item:</span>
                <span class="value">{{ $totalItem }} Item</span>
            </div>
            @endif
        </div>

        <div class="action-box">
            <h4 style="margin-top: 0;">⚠️ Langkah Selanjutnya:</h4>
            <ul>
                <li>Silakan datang ke divisi inventory untuk mengambil barang</li>
                <li>Konfirmasi pengambilan dengan petugas yang ada di divisi inventory</li>
            </ul>
        </div>

        <div class="footer">
            <p>Email ini dikirim secara otomatis oleh sistem. Mohon tidak membalas email ini.</p>
            <p>Jika ada pertanyaan, silakan hubungi divisi inventory.</p>
        </div>
    </div>
</body>
</html>