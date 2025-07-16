<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Barang Project Memerlukan Persetujuan Direksi</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }

        .container {
            background-color: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
        }

        .header h1 {
            color: #dc3545;
            margin: 0;
            font-size: 24px;
        }

        .header p {
            color: #6c757d;
            margin: 5px 0 0 0;
        }

        .info-section {
            margin-bottom: 25px;
        }

        .info-section h3 {
            color: #495057;
            margin-bottom: 15px;
            font-size: 18px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 5px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }

        .info-item {
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
            border-left: 4px solid #007bff;
        }

        .info-item strong {
            color: #495057;
            display: block;
            margin-bottom: 5px;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            background-color: #ffc107;
            color: #212529;
        }

        .barang-list {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .barang-item {
            background-color: white;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 10px;
            border-left: 4px solid #28a745;
        }

        .barang-item:last-child {
            margin-bottom: 0;
        }

        .barang-item h4 {
            margin: 0 0 10px 0;
            color: #495057;
        }

        .barang-detail {
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .action-section {
            background-color: #e7f3ff;
            padding: 20px;
            border-radius: 6px;
            text-align: center;
            margin-top: 30px;
        }

        .action-section h3 {
            color: #0056b3;
            margin-bottom: 10px;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin: 10px 5px;
        }

        .btn-approve {
            background-color: #28a745;
        }

        .btn-reject {
            background-color: #dc3545;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #6c757d;
            font-size: 14px;
        }

        .note {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }

        .note strong {
            color: #856404;
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
            <h1>🏢 Pengajuan Project Memerlukan Persetujuan Direksi</h1>
        </div>

        <div class="info-section">
            <h3>📋 Informasi Pengajuan</h3>
            <div class="info-grid">
                <div class="info-item">
                    <strong>Tanggal Pengajuan:</strong>
                    {{ \Carbon\Carbon::parse($pengajuan->tanggal_pengajuan)->format('d F Y') }}
                </div>
                <div class="info-item">
                    <strong>Tanggal Dibutuhkan:</strong>
                    {{ $pengajuan->tanggal_dibutuhkan ? \Carbon\Carbon::parse($pengajuan->tanggal_dibutuhkan)->format('d F Y') : 'Tidak ditentukan' }}
                </div>
                <div class="info-item">
                    <strong>Status:</strong>
                    <span class="status-badge">{{ ucfirst(str_replace('_', ' ', $pengajuan->status)) }}</span>
                </div>
                <div class="info-item">
                    <strong>Pengaju:</strong>
                    {{ $pengaju->name }}
                </div>
            </div>
        </div>

        <div class="info-section">
            <h3>🏗️ Informasi Project</h3>
            <div class="info-grid">
                <div class="info-item">
                    <strong>Nama Project:</strong>
                    {{ $project->nama_project }}
                </div>
                <div class="info-item">
                    <strong>Project Manager:</strong>
                    {{ $pm ? $pm->name : 'Belum ditentukan' }}
                </div>
            </div>
        </div>

        @if (!empty($detailBarang))
            <div class="info-section">
                <h3>📦 Detail Barang yang Diajukan</h3>
                <div class="barang-list">
                    @foreach ($detailBarang as $index => $barang)
                        <div class="barang-item">
                            <h4>{{ $barang['nama_barang'] ?? 'Nama tidak tersedia' }}</h4>
                            <div class="barang-detail">
                                <strong>Jumlah:</strong> {{ $barang['jumlah_barang_diajukan'] ?? 0 }} unit
                            </div>
                            @if (!empty($barang['keterangan_barang']))
                                <div class="barang-detail">
                                    <strong>Keterangan:</strong> {{ $barang['keterangan_barang'] }}
                                </div>
                            @endif
                            @if (!empty($barang['file_barang']))
                                <div class="barang-detail">
                                    <strong>File Pendukung:</strong> {{ count($barang['file_barang']) }} file terlampir
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if (!empty($uploadedFiles))
            <div class="info-section">
                <h3>📎 File Pendukung Project</h3>
                <div class="info-item">
                    <strong>Jumlah File:</strong> {{ count($uploadedFiles) }} file terlampir
                </div>
            </div>
        @endif

        @if ($pengajuan->catatan)
            <div class="info-section">
                <h3>📝 Catatan</h3>
                <div class="note">
                    <strong>Catatan:</strong> {{ $pengajuan->catatan }}
                </div>
            </div>
        @endif

        <div class="footer">
            <p>
                Email ini dikirim secara otomatis oleh sistem.<br>
                Jika Anda memiliki pertanyaan, silakan hubungi administrator sistem.
            </p>
            <p>
                <strong>{{ config('app.name') }}</strong><br>
                {{ now()->format('d F Y H:i:s') }}
            </p>
        </div>
    </div>
</body>

</html>
