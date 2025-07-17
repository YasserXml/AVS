<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Barang Project Ditolak</title>
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
        .container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #dc3545;
        }
        .header h1 {
            color: #dc3545;
            margin: 0;
            font-size: 24px;
        }
        .status-badge {
            display: inline-block;
            background: #dc3545;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 10px;
        }
        .info-section {
            margin-bottom: 25px;
        }
        .info-section h3 {
            color: #495057;
            margin-bottom: 15px;
            font-size: 18px;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 5px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        .info-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #007bff;
        }
        .info-item label {
            display: block;
            font-weight: bold;
            color: #495057;
            margin-bottom: 5px;
            font-size: 14px;
        }
        .info-item span {
            color: #6c757d;
            font-size: 14px;
        }
        .barang-list {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .barang-item {
            background: white;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            border-left: 4px solid #6c757d;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .barang-item:last-child {
            margin-bottom: 0;
        }
        .barang-name {
            font-weight: bold;
            color: #495057;
            margin-bottom: 5px;
        }
        .barang-qty {
            color: #6c757d;
            font-weight: bold;
            margin-bottom: 8px;
        }
        .barang-desc {
            color: #6c757d;
            font-size: 14px;
            line-height: 1.4;
        }
        .alert {
            background: #f8d7da;
            color: #721c24;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #dc3545;
            margin: 20px 0;
        }
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border-left-color: #ffc107;
        }
        .rejection-reason {
            background: #f8d7da;
            color: #721c24;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #dc3545;
            margin: 20px 0;
        }
        .rejection-reason h4 {
            margin-top: 0;
            color: #dc3545;
            font-size: 16px;
        }
        .rejection-details {
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            margin-top: 15px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            background: #007bff;
            color: white !important;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 10px 0;
            text-align: center;
        }
        .button:hover {
            background: #0056b3;
        }
        .button-secondary {
            background: #6c757d;
        }
        .button-secondary:hover {
            background: #545b62;
        }
        .next-steps {
            background: #cce5ff;
            color: #004085;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #007bff;
            margin: 20px 0;
        }
        .next-steps h4 {
            margin-top: 0;
            color: #007bff;
        }
        .next-steps ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .next-steps li {
            margin-bottom: 5px;
        }
        @media (max-width: 600px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>❌ Pengajuan Barang Project Ditolak</h1>
            <div class="status-badge">Ditolak</div>
        </div>

        <div class="alert">
            <strong>Maaf!</strong> Pengajuan barang project Anda telah ditolak oleh <strong>{{ $rejectionType }}</strong>.
        </div>

        <div class="info-section">
            <h3>📋 Informasi Pengajuan</h3>
            <div class="info-grid">
                <div class="info-item">
                    <label>Nama Project:</label>
                    <span>{{ $namaProject }}</span>
                </div>
                <div class="info-item">
                    <label>Yang Mengajukan:</label>
                    <span>{{ $namaPengaju }}</span>
                </div>
                <div class="info-item">
                    <label>Tanggal Pengajuan:</label>
                    <span>{{ \Carbon\Carbon::parse($tanggalPengajuan)->format('d F Y') }}</span>
                </div>
                <div class="info-item">
                    <label>Tanggal Dibutuhkan:</label>
                    <span>{{ \Carbon\Carbon::parse($tanggalDibutuhkan)->format('d F Y') }}</span>
                </div>
                <div class="info-item">
                    <label>Ditolak Oleh:</label>
                    <span><strong>{{ $rejectionType }}</strong></span>
                </div>
            </div>
        </div>

        @if($tanggalDitolak)
            <div class="info-section">
                <h3>📅 Waktu Penolakan</h3>
                <div class="info-item">
                    <label>Tanggal & Waktu:</label>
                    <span>{{ \Carbon\Carbon::parse($tanggalDitolak)->format('d F Y H:i') }}</span>
                </div>
            </div>
        @endif

        @if($alasanPenolakan)
            <div class="rejection-reason">
                <h4>📝 Alasan Penolakan</h4>
                <div class="rejection-details">
                    {{ $alasanPenolakan }}
                </div>
            </div>
        @endif

        <div class="info-section">
            <h3>📦 Detail Barang yang Diajukan</h3>
            <div class="barang-list">
                @if($detailBarang && is_array($detailBarang))
                    @foreach($detailBarang as $index => $barang)
                        <div class="barang-item">
                            <div class="barang-name">{{ $barang['nama_barang'] ?? 'Nama tidak tersedia' }}</div>
                            <div class="barang-qty">Jumlah: {{ $barang['jumlah_barang_diajukan'] ?? 0 }} Unit</div>
                            @if(isset($barang['keterangan_barang']) && $barang['keterangan_barang'])
                                <div class="barang-desc">{{ $barang['keterangan_barang'] }}</div>
                            @endif
                        </div>
                    @endforeach
                @else
                    <div class="barang-item">
                        <div class="barang-name">Detail barang tidak tersedia</div>
                    </div>
                @endif
            </div>
        </div>

        <div class="next-steps">
            <h4>🔄 Langkah Selanjutnya</h4>
            <p>Anda dapat melakukan tindakan berikut:</p>
        </div>

        <div class="info-section">
            <h3>📊 Riwayat Status</h3>
            <div class="barang-list">
                @if($statusHistory && is_array($statusHistory))
                    @foreach(array_reverse($statusHistory) as $history)
                        <div class="barang-item">
                            <div class="barang-name">{{ $history['status'] ?? 'Status tidak tersedia' }}</div>
                            <div class="barang-qty">{{ \Carbon\Carbon::parse($history['created_at'])->format('d F Y H:i') }}</div>
                            @if(isset($history['note']) && $history['note'])
                                <div class="barang-desc">{{ $history['note'] }}</div>
                            @endif
                        </div>
                    @endforeach
                @else
                    <div class="barang-item">
                        <div class="barang-name">Riwayat status tidak tersedia</div>
                    </div>
                @endif
            </div>
        </div>

        <div class="footer">
            <p>Email ini dikirim secara otomatis oleh sistem manajemen project.<br>
            Jika Anda memiliki pertanyaan, silakan hubungi {{ $rejectionType }}.</p>
            <p><small>Dikirim pada: {{ now()->format('d F Y H:i') }}</small></p>
        </div>
    </div>
</body>
</html>