<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Barang Project Dikirim ke Pengadaan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .email-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
        }
        .header h1 {
            color: #2c3e50;
            margin: 0;
            font-size: 24px;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            background-color: #28a745;
            color: white;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 10px;
        }
        .info-section {
            margin-bottom: 25px;
        }
        .info-title {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 5px;
        }
        .info-row {
            display: flex;
            margin-bottom: 8px;
        }
        .info-label {
            font-weight: bold;
            width: 150px;
            color: #495057;
        }
        .info-value {
            flex: 1;
            color: #333;
        }
        .barang-item {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            border-left: 4px solid #007bff;
        }
        .barang-name {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .barang-detail {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }
        .action-section {
            background-color: #e3f2fd;
            padding: 20px;
            border-radius: 5px;
            margin-top: 25px;
            text-align: center;
        }
        .action-title {
            font-size: 16px;
            font-weight: bold;
            color: #1976d2;
            margin-bottom: 10px;
        }
        .action-text {
            color: #666;
            font-size: 14px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        .highlight {
            background-color: #fff3cd;
            padding: 2px 4px;
            border-radius: 3px;
        }
        .urgent {
            color: #dc3545;
            font-weight: bold;
        }
        .files-section {
            margin-top: 15px;
        }
        .file-item {
            background-color: #f1f3f4;
            padding: 8px 12px;
            margin: 5px 0;
            border-radius: 4px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>🔔 Pengajuan Project Dikirim ke Tim Pengadaan</h1>
            <div class="status-badge">DISETUJUI PM</div>
        </div>

        <div class="info-section">
            <div class="info-title">📋 Informasi Pengajuan</div>
            <div class="info-row">
                <div class="info-label">Tanggal Pengajuan:</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($pengajuan->tanggal_pengajuan)->format('d F Y') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Tanggal Dibutuhkan:</div>
                <div class="info-value">
                    @if($pengajuan->tanggal_dibutuhkan)
                        <span class="highlight">{{ \Carbon\Carbon::parse($pengajuan->tanggal_dibutuhkan)->format('d F Y') }}</span>
                        @php
                            $hariSisa = \Carbon\Carbon::parse($pengajuan->tanggal_dibutuhkan)->diffInDays(now());
                        @endphp
                        @if($hariSisa <= 7)
                            <span class="urgent">({{ $hariSisa }} hari lagi)</span>
                        @endif
                    @else
                        Tidak ditentukan
                    @endif
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Status:</div>
                <div class="info-value">
                    <span style="color: #28a745; font-weight: bold;">Disetujui PM & Dikirim ke Pengadaan</span>
                </div>
            </div>
        </div>

        <div class="info-section">
            <div class="info-title">🏢 Informasi Project</div>
            <div class="info-row">
                <div class="info-label">Nama Project:</div>
                <div class="info-value"><strong>{{ $project->nama_project }}</strong></div>
            </div>
            <div class="info-row">
                <div class="info-label">Project Manager:</div>
                <div class="info-value">
                    @if($pm)
                        {{ $pm->name }}
                    @else
                        Tidak ada PM ditugaskan
                    @endif
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Pengaju:</div>
                <div class="info-value">{{ $pengaju->name }}</div>
            </div>
        </div>

        @if($pengajuan->catatan)
        <div class="info-section">
            <div class="info-title">📝 Catatan PM</div>
            <div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; font-style: italic;">
                "{{ $pengajuan->catatan }}"
            </div>
        </div>
        @endif

        <div class="info-section">
            <div class="info-title">📦 Detail Barang yang Diajukan</div>
            @if($detailBarang && is_array($detailBarang))
                @foreach($detailBarang as $index => $barang)
                <div class="barang-item">
                    <div class="barang-name">{{ $barang['nama_barang'] ?? 'Nama barang tidak tersedia' }}</div>
                    <div class="barang-detail">
                        <strong>Jumlah:</strong> {{ $barang['jumlah_barang_diajukan'] ?? 0 }} Unit
                    </div>
                    @if(isset($barang['keterangan_barang']) && $barang['keterangan_barang'])
                    <div class="barang-detail">
                        <strong>Spesifikasi:</strong> {{ $barang['keterangan_barang'] }}
                    </div>
                    @endif
                    @if(isset($barang['file_barang']) && $barang['file_barang'])
                    <div class="files-section">
                        <strong>File Pendukung:</strong>
                        @if(is_array($barang['file_barang']))
                            @foreach($barang['file_barang'] as $file)
                                <div class="file-item">📎 {{ basename($file) }}</div>
                            @endforeach
                        @else
                            <div class="file-item">📎 {{ basename($barang['file_barang']) }}</div>
                        @endif
                    </div>
                    @endif
                </div>
                @endforeach
            @else
                <div style="color: #666; font-style: italic;">Detail barang tidak tersedia</div>
            @endif
        </div>

        @if($pengajuan->uploaded_files)
        <div class="info-section">
            <div class="info-title">📎 File Pendukung Project</div>
            @php
                $files = is_string($pengajuan->uploaded_files) ? json_decode($pengajuan->uploaded_files, true) : $pengajuan->uploaded_files;
            @endphp
            @if($files && is_array($files))
                @foreach($files as $file)
                <div class="file-item">📄 {{ basename($file) }}</div>
                @endforeach
            @else
                <div style="color: #666; font-style: italic;">Tidak ada file pendukung</div>
            @endif
        </div>
        @endif

        <div class="action-section">
            <div class="action-title">🚀 Tindakan Selanjutnya</div>
            <div class="action-text">
                Pengajuan ini telah disetujui oleh Project Manager dan kini menunggu review dari Tim Pengadaan. 
                Silakan login ke sistem untuk melakukan review dan tindakan selanjutnya.
            </div>
        </div>

        <div class="footer">
            <p>Email ini dikirim secara otomatis oleh sistem. Mohon tidak membalas email ini.</p>
            <p>Tanggal: {{ now()->format('d F Y, H:i') }} WIB</p>
        </div>
    </div>
</body>
</html>