{{-- 2. Perbaikan rejected.blade.php --}}
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Ditolak</title>
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
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .status-badge {
            background-color: #ef4444;
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
            border-left: 4px solid #ef4444;
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

        .reason-box {
            background-color: #fef2f2;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #ef4444;
        }

        .next-steps {
            background-color: #f0f9ff;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #3b82f6;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1 style="color: #ef4444; margin: 0;">❌ Pengajuan Ditolak</h1>
            <div class="status-badge">DITOLAK</div>
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
                <span class="label">Ditolak pada:</span>
                <span class="value">{{ $tanggalDitolak }}</span>
            </div>
            <div class="info-row">
                <span class="label">Ditolak oleh:</span>
                <span class="value">{{ $ditolakOleh }} ({{ $peranPenolak }})</span>
            </div>
        </div>

        <p>Mohon maaf, pengajuan barang Anda telah<strong>ditolak</strong>.</p>

        @if ($alasan)
            <div class="reason-box">
                <h4 style="color: #ef4444; margin-top: 0;">❌ Alasan Penolakan:</h4>
                <p style="margin-bottom: 0;">{{ $alasan }}</p>
            </div>
        @endif

        <div class="next-steps">
            <h4 style="color: #3b82f6; margin-top: 0;">📋 Langkah Selanjutnya:</h4>
            <ul>
                <li>Tinjau kembali alasan penolakan di atas</li>
                <li>Perbaiki pengajuan sesuai dengan feedback yang diberikan</li>
                <li>Anda dapat mengajukan kembali dengan perbaikan yang diperlukan</li>
                <li>Hubungi tim pengadaan jika memerlukan klarifikasi lebih lanjut</li>
            </ul>
        </div>

        <div class="footer">
            <p>Email ini dikirim secara otomatis oleh sistem. Mohon tidak membalas email ini.</p>
        </div>
    </div>
</body>

</html>
