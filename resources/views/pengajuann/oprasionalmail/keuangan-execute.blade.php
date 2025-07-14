<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Operasional Dikirim ke Pengadaan</title>
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
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e8f4fd;
        }
        .header h1 {
            color: #1e3a8a;
            margin: 0;
            font-size: 24px;
        }
        .status-badge {
            background-color: #3b82f6;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            display: inline-block;
            margin-top: 10px;
        }
        .info-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
            border-left: 4px solid #3b82f6;
        }
        .info-row {
            display: flex;
            margin-bottom: 10px;
        }
        .info-row:last-child {
            margin-bottom: 0;
        }
        .info-label {
            font-weight: bold;
            min-width: 150px;
            color: #555;
        }
        .info-value {
            flex: 1;
            color: #333;
        }
        .items-section {
            margin: 20px 0;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .items-table th,
        .items-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .items-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #555;
        }
        .items-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .workflow-section {
            background-color: #e8f4fd;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
            border-left: 4px solid #3b82f6;
        }
        .workflow-section h3 {
            margin-top: 0;
            color: #1e3a8a;
        }
        .workflow-step {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }
        .workflow-step.completed {
            color: #16a34a;
        }
        .workflow-step.current {
            color: #3b82f6;
            font-weight: bold;
        }
        .workflow-step.pending {
            color: #6b7280;
        }
        .step-icon {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 10px;
            text-align: center;
            line-height: 20px;
            font-size: 12px;
            color: white;
        }
        .step-icon.completed {
            background-color: #16a34a;
        }
        .step-icon.current {
            background-color: #3b82f6;
        }
        .step-icon.pending {
            background-color: #6b7280;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 12px;
        }
        .action-required {
            background-color: #fef3c7;
            border: 1px solid #fbbf24;
            color: #92400e;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .action-required h3 {
            margin-top: 0;
            color: #92400e;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin-top: 10px;
        }
        .btn:hover {
            background-color: #2563eb;
        }
        @media only screen and (max-width: 600px) {
            body {
                padding: 10px;
            }
            .email-container {
                padding: 20px;
            }
            .info-row {
                flex-direction: column;
            }
            .info-label {
                min-width: auto;
                margin-bottom: 5px;
            }
            .items-table {
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>Pengajuan Operasional</h1>
            <div class="status-badge">Dikirim ke Pengadaan</div>
        </div>

        <div class="info-section">
            <div class="info-row">
                <span class="info-label">Tanggal Pengajuan:</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($pengajuan->tanggal_pengajuan)->format('d F Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Tanggal Dibutuhkan:</span>
                <span class="info-value">{{ $pengajuan->tanggal_dibutuhkan ? \Carbon\Carbon::parse($pengajuan->tanggal_dibutuhkan)->format('d F Y') : 'Tidak ditentukan' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Pengaju:</span>
                <span class="info-value">{{ $pengajuan->user->name ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Status:</span>
                <span class="info-value">Dikirim ke Pengadaan</span>
            </div>
        </div>

        <div class="workflow-section">
            <h3>Alur Persetujuan:</h3>
            <div class="workflow-step completed">
                <span class="step-icon completed">✓</span>
                Pengajuan Dikirim
            </div>
            <div class="workflow-step completed">
                <span class="step-icon completed">✓</span>
                Review Admin
            </div>
            <div class="workflow-step completed">
                <span class="step-icon completed">✓</span>
                Persetujuan Tim Pengadaan
            </div>
            <div class="workflow-step completed">
                <span class="step-icon completed">✓</span>
                Persetujuan Direksi
            </div>
            <div class="workflow-step completed">
                <span class="step-icon completed">✓</span>
                Proses Keuangan
            </div>
            <div class="workflow-step current">
                <span class="step-icon current">→</span>
                Proses Pengadaan
            </div>
            <div class="workflow-step pending">
                <span class="step-icon pending">○</span>
                Proses Admin
            </div>
            <div class="workflow-step pending">
                <span class="step-icon pending">○</span>
                Selesai
            </div>
        </div>

        @if($pengajuan->detail_barang)
            <div class="items-section">
                <h3>Detail Barang/Jasa yang Diminta:</h3>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Barang/Jasa</th>
                            <th>Jumlah</th>
                            <th>Satuan</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(json_decode($pengajuan->detail_barang, true) as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item['nama_barang'] ?? 'N/A' }}</td>
                                <td>{{ $item['jumlah'] ?? 'N/A' }}</td>
                                <td>{{ $item['satuan'] ?? 'N/A' }}</td>
                                <td>{{ $item['keterangan'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="action-required">
            <h3>Tindakan Diperlukan:</h3>
            <p>Pengajuan operasional ini telah selesai diproses oleh tim keuangan dan kini berada di tahap pengadaan. Silakan login ke sistem untuk melakukan tindakan selanjutnya.</p>
            <ul>
                <li>Mengirim ke admin untuk finalisasi</li>
            </ul>
        </div>

        <div class="footer">
            <p>Email ini dikirim secara otomatis oleh sistem.</p>
            <p>Jika ada pertanyaan, silakan hubungi admin sistem.</p>
        </div>
    </div>
</body>
</html>