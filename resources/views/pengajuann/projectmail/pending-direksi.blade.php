<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Project Dipending</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }

        .content {
            padding: 30px 20px;
        }

        .alert {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .alert-title {
            font-weight: 600;
            color: #92400e;
            margin-bottom: 5px;
        }

        .alert-text {
            color: #a16207;
            margin: 0;
        }

        .info-section {
            background-color: #f8fafc;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
        }

        .info-row {
            display: flex;
            margin-bottom: 12px;
            align-items: flex-start;
        }

        .info-row:last-child {
            margin-bottom: 0;
        }

        .info-label {
            font-weight: 600;
            color: #374151;
            min-width: 140px;
            margin-right: 10px;
        }

        .info-value {
            color: #6b7280;
            flex: 1;
        }

        .barang-list {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            margin-top: 15px;
        }

        .barang-item {
            padding: 15px;
            border-bottom: 1px solid #e5e7eb;
        }

        .barang-item:last-child {
            border-bottom: none;
        }

        .barang-name {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 5px;
        }

        .barang-details {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 8px;
        }

        .barang-qty {
            background-color: #dbeafe;
            color: #1e40af;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .pending-reason {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }

        .pending-reason h3 {
            color: #dc2626;
            margin: 0 0 10px 0;
            font-size: 16px;
        }

        .pending-reason p {
            color: #7f1d1d;
            margin: 0;
            line-height: 1.5;
        }

        .footer {
            background-color: #f9fafb;
            padding: 20px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            border-top: 1px solid #e5e7eb;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin-top: 20px;
        }

        .btn:hover {
            background-color: #2563eb;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>⏳ Pengajuan Project Dipending</h1>
        </div>

        <div class="content">
            <div class="alert">
                <div class="alert-title">Pengajuan Dipending</div>
                <p class="alert-text">Pengajuan project telah dipending oleh Direksi dan memerlukan tindak lanjut.</p>
            </div>

            <p>Kami ingin menginformasikan bahwa pengajuan project telah dipending oleh Direksi dengan detail sebagai
                berikut:</p>

            <div class="info-section">
                <div class="info-row">
                    <span class="info-label">Nama Project:</span>
                    <span class="info-value"><strong>{{ $namaProject }}</strong></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tanggal Pengajuan:</span>
                    <span class="info-value">{{ $tanggalPengajuan }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tanggal Dibutuhkan:</span>
                    <span class="info-value">{{ $tanggalDibutuhkan }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value"><span
                            style="background-color: #fef3c7; color: #92400e; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">PENDING
                            DIREKSI</span></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tanggal Pending:</span>
                    <span
                        class="info-value"><strong>{{ \Carbon\Carbon::parse($tanggalPending)->format('d F Y') }}</strong></span>
                </div>
            </div>

            <div class="pending-reason">
                <h3>💬 Alasan Pending</h3>
                <p>{{ $alasanPending }}</p>
            </div>

            @if (!empty($detailBarang))
                <h3 style="color: #374151; margin-bottom: 15px;">📦 Detail Barang yang Diajukan</h3>
                <div class="barang-list">
                    @foreach ($detailBarang as $index => $barang)
                        <div class="barang-item">
                            <div class="barang-name">{{ $barang['nama_barang'] ?? 'Nama barang tidak tersedia' }}</div>
                            <div class="barang-details">{{ $barang['keterangan_barang'] ?? 'Tidak ada keterangan' }}
                            </div>
                            <span class="barang-qty">{{ $barang['jumlah_barang_diajukan'] ?? 0 }} Unit</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <p style="margin-top: 20px; color: #6b7280; font-size: 14px;">
                Email ini dikirim secara otomatis oleh sistem. Harap jangan membalas email ini.
            </p>
        </div>
    </div>
</body>

</html>
