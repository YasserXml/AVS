<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Barang Project </title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 0;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 30px;
        }
        .alert {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .alert-icon {
            font-size: 18px;
            margin-right: 8px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }
        .info-item {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #667eea;
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
        .section {
            margin-bottom: 30px;
        }
        .section h3 {
            color: #495057;
            font-size: 18px;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e9ecef;
        }
        .barang-item {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 10px;
            border-left: 4px solid #28a745;
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
        }
        .barang-qty {
            background-color: #28a745;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        .barang-description {
            color: #6c757d;
            font-size: 14px;
            margin-top: 8px;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background-color: #ffc107;
            color: #212529;
        }
        .action-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            text-align: center;
            margin-top: 20px;
            transition: transform 0.2s ease;
        }
        .action-button:hover {
            transform: translateY(-2px);
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-radius: 0 0 8px 8px;
            color: #6c757d;
            font-size: 14px;
        }
        .timeline-item {
            padding: 10px 0;
            border-left: 2px solid #e9ecef;
            padding-left: 15px;
            margin-left: 10px;
            position: relative;
        }
        .timeline-item:before {
            content: '';
            position: absolute;
            left: -6px;
            top: 15px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #667eea;
        }
        .timeline-date {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 5px;
        }
        .timeline-content {
            font-size: 14px;
            color: #495057;
        }
        @media (max-width: 600px) {
            .info-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            .content {
                padding: 20px;
            }
            .barang-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .barang-qty {
                margin-top: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔔 Pengajuan Barang Project</h1>
        </div>

        <div class="content">

            <div class="info-grid">
                <div class="info-item">
                    <strong>Nama Project</strong>
                    <div>{{ $project->nama_project ?? 'Tidak ada project' }}</div>
                </div>
                <div class="info-item">
                    <strong>Yang Mengajukan</strong>
                    <div>{{ $pengaju->name ?? 'Tidak diketahui' }}</div>
                </div>
                <div class="info-item">
                    <strong>Tanggal Pengajuan</strong>
                    <div>{{ \Carbon\Carbon::parse($pengajuan->tanggal_pengajuan)->format('d F Y') }}</div>
                </div>
                <div class="info-item">
                    <strong>Tanggal Dibutuhkan</strong>
                    <div>{{ $pengajuan->tanggal_dibutuhkan ? \Carbon\Carbon::parse($pengajuan->tanggal_dibutuhkan)->format('d F Y') : 'Tidak ditentukan' }}</div>
                </div>
                <div class="info-item">
                    <strong>Status Saat Ini</strong>
                    <div><span class="status-badge">{{ ucfirst(str_replace('_', ' ', $pengajuan->status)) }}</span></div>
                </div>
            </div>

            @if($pengajuan->catatan)
            <div class="section">
                <h3>📝 Catatan</h3>
                <div style="background-color: #f8f9fa; padding: 15px; border-radius: 6px; font-style: italic; color: #495057;">
                    "{{ $pengajuan->catatan }}"
                </div>
            </div>
            @endif

            @if(!empty($detailBarang))
            <div class="section">
                <h3>📦 Detail Barang Yang Diajukan</h3>
                @foreach($detailBarang as $barang)
                <div class="barang-item">
                    <div class="barang-header">
                        <div class="barang-name">{{ $barang['nama_barang'] ?? 'Nama tidak tersedia' }}</div>
                        <div class="barang-qty">{{ $barang['jumlah_barang_diajukan'] ?? 0 }} Unit</div>
                    </div>
                    @if(isset($barang['keterangan_barang']))
                    <div class="barang-description">
                        {{ $barang['keterangan_barang'] }}
                    </div>
                    @endif
                    @if(isset($barang['file_barang']) && !empty($barang['file_barang']))
                    <div style="margin-top: 10px; font-size: 12px; color: #6c757d;">
                        📎 File terlampir: {{ is_array($barang['file_barang']) ? count($barang['file_barang']) : 1 }} file
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @endif

            @if(!empty($uploadedFiles))
            <div class="section">
                <h3>📎 File Pendukung Project</h3>
                <div style="background-color: #f8f9fa; padding: 15px; border-radius: 6px;">
                    <div style="color: #495057; font-size: 14px;">
                        Terdapat {{ count($uploadedFiles) }} file pendukung yang telah diupload untuk project ini.
                    </div>
                </div>
            </div>
            @endif

            @if(!empty($statusHistory))
            <div class="section">
                <h3>📊 Riwayat Status</h3>
                @foreach(array_reverse($statusHistory) as $history)
                <div class="timeline-item">
                    <div class="timeline-date">
                        {{ \Carbon\Carbon::parse($history['created_at'])->format('d F Y, H:i') }}
                    </div>
                    <div class="timeline-content">
                        <strong>{{ ucfirst(str_replace('_', ' ', $history['status'])) }}</strong>
                        @if(isset($history['notes']))
                        <div style="margin-top: 5px; color: #6c757d; font-size: 13px;">
                            {{ $history['notes'] }}
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <div class="footer">
            <p>Email ini dikirim secara otomatis oleh sistem.</p>
            <p>Jika Anda memiliki pertanyaan, silakan hubungi administrator sistem.</p>
            <p style="margin-top: 15px; font-size: 12px; color: #adb5bd;">
                © {{ date('Y') }} Sistem Pengajuan Project. Semua hak dilindungi.
            </p>
        </div>
    </div>
</body>
</html>