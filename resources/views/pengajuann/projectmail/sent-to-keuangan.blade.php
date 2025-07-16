<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Barang Project Dikirim ke Keuangan</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
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
        .info-card {
            background-color: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .info-row {
            display: flex;
            margin-bottom: 15px;
            align-items: flex-start;
        }
        .info-row:last-child {
            margin-bottom: 0;
        }
        .info-label {
            font-weight: 600;
            color: #495057;
            width: 140px;
            flex-shrink: 0;
        }
        .info-value {
            color: #212529;
            flex: 1;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            background-color: #ffc107;
            color: #212529;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #495057;
            margin: 30px 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }
        .barang-item {
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .barang-item:last-child {
            margin-bottom: 0;
        }
        .barang-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .barang-name {
            font-weight: 600;
            color: #495057;
            font-size: 16px;
        }
        .barang-quantity {
            background-color: #e9ecef;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        .barang-desc {
            color: #6c757d;
            font-size: 14px;
            margin-top: 8px;
        }
        .action-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 20px 0;
            text-align: center;
            transition: all 0.3s ease;
        }
        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }
        .footer p {
            margin: 0;
            color: #6c757d;
            font-size: 14px;
        }
        .alert {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
            color: #856404;
        }
        .alert-icon {
            display: inline-block;
            margin-right: 8px;
            font-weight: bold;
        }
        
        @media (max-width: 600px) {
            .container {
                margin: 10px;
                border-radius: 4px;
            }
            .header {
                padding: 20px;
            }
            .content {
                padding: 20px;
            }
            .info-row {
                flex-direction: column;
            }
            .info-label {
                width: 100%;
                margin-bottom: 5px;
            }
            .barang-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .barang-quantity {
                margin-top: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 Pengajuan Barang Project Dikirim ke Keuangan</h1>
            <p>Notifikasi untuk Tim Keuangan</p>
        </div>

        <div class="content">
            <div class="info-card">
                <div class="info-row">
                    <div class="info-label">Status:</div>
                    <div class="info-value">
                        <span class="status-badge">{{ str_replace('_', ' ', $pengajuan->status) }}</span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Nama Project:</div>
                    <div class="info-value"><strong>{{ $projectName }}</strong></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Pengaju:</div>
                    <div class="info-value">{{ $pengajuName }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Project Manager:</div>
                    <div class="info-value">{{ $pmName }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Tanggal Pengajuan:</div>
                    <div class="info-value">{{ $tanggalPengajuan }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Tanggal Dibutuhkan:</div>
                    <div class="info-value">{{ $tanggalDibutuhkan }}</div>
                </div>
            </div>

            @if($catatan)
            <div class="section-title">💬 Catatan</div>
            <div class="info-card">
                <p style="margin: 0; font-style: italic; color: #6c757d;">{{ $catatan }}</p>
            </div>
            @endif

            <div class="section-title">📦 Detail Barang</div>
            @if(!empty($detailBarang))
                @foreach($detailBarang as $index => $barang)
                <div class="barang-item">
                    <div class="barang-header">
                        <div class="barang-name">{{ $barang['nama_barang'] ?? 'Nama tidak tersedia' }}</div>
                        <div class="barang-quantity">{{ $barang['jumlah_barang_diajukan'] ?? 0 }} Unit</div>
                    </div>
                    @if(!empty($barang['keterangan_barang']))
                    <div class="barang-desc">
                        <strong>Spesifikasi:</strong> {{ $barang['keterangan_barang'] }}
                    </div>
                    @endif
                </div>
                @endforeach
            @else
                <div class="info-card">
                    <p style="margin: 0; color: #6c757d; font-style: italic;">Tidak ada detail barang yang tersedia.</p>
                </div>
            @endif
        </div>

        <div class="footer">
            <p>
                Email ini dikirim secara otomatis oleh sistem.<br>
                Untuk pertanyaan, silakan hubungi administrator sistem.
            </p>
            <p style="margin-top: 10px; font-size: 12px; color: #adb5bd;">
                © {{ date('Y') }} {{ config('app.name') }}. Semua hak cipta dilindungi.
            </p>
        </div>
    </div>
</body>
</html>