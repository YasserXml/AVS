<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Barang Baru</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; line-height: 1.6; color: #1f2937; background-color: #f8fafc;">
    
    <!-- Main Container -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f8fafc;">
        <tr>
            <td style="padding: 20px 10px;">
                
                <!-- Email Wrapper -->
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #dc2626; padding: 40px 30px; text-align: center; border-radius: 12px 12px 0 0;">
                            <!-- Logo Container -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin: 0 auto 20px;">
                                <tr>
                                    <td style="width: 80px; height: 80px; background-color: #ffffff; border-radius: 50%; padding: 15px; text-align: center;">
                                        <div style="width: 50px; height: 50px; background-color: #dc2626; border-radius: 8px; display: inline-block; line-height: 50px; color: white; font-size: 24px; font-weight: bold;">🏢</div>
                                    </td>
                                </tr>
                            </table>
                            
                            <h1 style="color: #ffffff; margin: 0 0 10px 0; font-size: 28px; font-weight: bold;">Pengajuan Barang Baru</h1>
                            <p style="color: rgba(255, 255, 255, 0.9); margin: 0; font-size: 16px;">Dari {{ $pengaju->name }}</p>
                        </td>
                    </tr>
                    
                    <!-- Status Badge -->
                    <tr>
                        <td style="padding: 30px 30px 0 30px;">
                            <div style="background-color: #f59e0b; color: white; display: inline-block; padding: 12px 20px; border-radius: 25px; font-size: 14px; font-weight: bold; text-transform: uppercase;">
                                ⏳ MENUNGGU PERSETUJUAN
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Info Box -->
                    <tr>
                        <td style="padding: 30px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f8fafc; border: 2px solid #e5e7eb; border-radius: 12px; padding: 25px;">
                                <tr>
                                    <td>
                                        <!-- Info Grid - Left Column -->
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="48%" style="float: left;">
                                            <tr>
                                                <td style="padding: 8px 0; border-bottom: 1px solid #f3f4f6;">
                                                    <strong style="color: #374151;">👤 Nama Pengaju:</strong><br>
                                                    <span style="color: #111827; font-weight: 600;">{{ $pengaju->name }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; border-bottom: 1px solid #f3f4f6;">
                                                    <strong style="color: #374151;">📧 Email:</strong><br>
                                                    <span style="color: #111827;">{{ $pengaju->email }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0;">
                                                    <strong style="color: #374151;">📅 Tanggal:</strong><br>
                                                    <span style="color: #111827;">{{ \Carbon\Carbon::parse($pengajuanItems->first()->tanggal_pengajuan)->format('d M Y') }}</span>
                                                </td>
                                            </tr>
                                        </table>
                                        
                                        <!-- Info Grid - Right Column -->
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="48%" style="float: right;">
                                            <tr>
                                                <td style="padding: 8px 0; border-bottom: 1px solid #f3f4f6;">
                                                    <strong style="color: #374151;">🎯 Diperuntukan:</strong><br>
                                                    <span style="color: #111827; font-weight: 600;">
                                                        @if($pengajuanItems->first()->status_barang === 'project')
                                                            Project: {{ $pengajuanItems->first()->nama_project }}
                                                        @else
                                                            Operasional Kantor
                                                        @endif
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; border-bottom: 1px solid #f3f4f6;">
                                                    <strong style="color: #374151;">📦 Total Barang:</strong><br>
                                                    <span style="color: #111827; font-weight: 600;">{{ $totalBarang }} item</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0;">
                                                    <strong style="color: #374151;">📊 Status:</strong><br>
                                                    <span style="background-color: #fbbf24; color: #1f2937; padding: 4px 12px; border-radius: 15px; font-size: 12px; font-weight: bold; text-transform: uppercase;">PENDING</span>
                                                </td>
                                            </tr>
                                        </table>
                                        
                                        <!-- Clear float -->
                                        <div style="clear: both;"></div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Keterangan Box (if exists) -->
                    @if($pengajuanItems->first()->keterangan)
                    <tr>
                        <td style="padding: 0 30px 30px 30px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #dbeafe; border-left: 6px solid #2563eb; border-radius: 8px; padding: 20px;">
                                <tr>
                                    <td>
                                        <h3 style="color: #1e40af; font-size: 16px; font-weight: bold; margin: 0 0 10px 0;">💬 Keterangan Pengaju:</h3>
                                        <p style="margin: 0; color: #1e3a8a; font-size: 14px; line-height: 1.6;">{{ $pengajuanItems->first()->keterangan }}</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endif
                    
                    <!-- Items Header -->
                    <tr>
                        <td style="padding: 0 30px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td style="background-color: #dc2626; color: white; padding: 15px 20px; text-align: center; font-size: 18px; font-weight: bold; border-radius: 8px 8px 0 0;">
                                        📋 Daftar Barang Diajukan ({{ $totalBarang }} Item)
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Items Container -->
                    <tr>
                        <td style="padding: 0 30px 30px 30px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border: 2px solid #dc2626; border-top: none; border-radius: 0 0 8px 8px;">
                                @foreach($pengajuanItems as $index => $item)
                                <tr>
                                    <td style="padding: 20px; border-bottom: 1px solid #f3f4f6; background-color: #ffffff;">
                                        
                                        <!-- Item Header -->
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom: 15px;">
                                            <tr>
                                                <td style="width: 30px;">
                                                    <div style="background-color: #1f2937; color: white; width: 25px; height: 25px; border-radius: 50%; text-align: center; line-height: 25px; font-size: 12px; font-weight: bold;">{{ $index + 1 }}</div>
                                                </td>
                                                <td style="padding-left: 10px;">
                                                    <strong style="color: #dc2626; font-size: 16px;">{{ $item->barang->nama_barang ?? 'Barang Tidak Ditemukan' }}</strong>
                                                </td>
                                                <td style="text-align: right;">
                                                    <span style="background-color: #059669; color: white; padding: 6px 12px; border-radius: 15px; font-size: 12px; font-weight: bold;">{{ $item->Jumlah_barang_diajukan }} UNIT</span>
                                                </td>
                                            </tr>
                                        </table>
                                        
                                        <!-- Item Details -->
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                            <tr>
                                                <td width="48%" style="vertical-align: top;">
                                                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                                        <tr>
                                                            <td style="background-color: #f8fafc; padding: 8px; border-radius: 6px; border-left: 3px solid #e5e7eb; margin-bottom: 8px;">
                                                                <strong style="color: #374151; font-size: 12px;">🔖 Kode Barang:</strong><br>
                                                                <span style="color: #111827; font-size: 13px;">{{ $item->barang->kode_barang ?? '-' }}</span>
                                                            </td>
                                                        </tr>
                                                        <tr><td style="height: 5px;"></td></tr>
                                                        <tr>
                                                            <td style="background-color: #f8fafc; padding: 8px; border-radius: 6px; border-left: 3px solid #e5e7eb;">
                                                                <strong style="color: #374151; font-size: 12px;">📂 Kategori:</strong><br>
                                                                <span style="color: #111827; font-size: 13px;">{{ $item->barang->kategori->nama_kategori ?? '-' }}</span>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <td width="4%"></td>
                                                <td width="48%" style="vertical-align: top;">
                                                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                                        <tr>
                                                            <td style="background-color: #f8fafc; padding: 8px; border-radius: 6px; border-left: 3px solid #e5e7eb; margin-bottom: 8px;">
                                                                <strong style="color: #374151; font-size: 12px;">📊 Stok Tersedia:</strong><br>
                                                                <span style="color: #111827; font-size: 13px;">{{ $item->barang->jumlah_barang ?? 0 }} unit</span>
                                                            </td>
                                                        </tr>
                                                        <tr><td style="height: 5px;"></td></tr>
                                                        @if($item->barang && $item->barang->serial_number)
                                                        <tr>
                                                            <td style="background-color: #f8fafc; padding: 8px; border-radius: 6px; border-left: 3px solid #e5e7eb;">
                                                                <strong style="color: #374151; font-size: 12px;">🔢 Serial Number:</strong><br>
                                                                <span style="color: #111827; font-size: 13px;">{{ $item->barang->serial_number }}</span>
                                                            </td>
                                                        </tr>
                                                        @endif
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                        
                                        <!-- Warning if stock insufficient -->
                                        @if($item->barang && $item->barang->jumlah_barang < $item->Jumlah_barang_diajukan)
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-top: 15px;">
                                            <tr>
                                                <td style="background-color: #fef2f2; border-left: 4px solid #dc2626; padding: 12px; border-radius: 6px;">
                                                    <span style="color: #991b1b; font-size: 13px; font-weight: 600;">
                                                        ⚠️ <strong>Perhatian:</strong> Stok tidak mencukupi! 
                                                        Diajukan: {{ $item->Jumlah_barang_diajukan }} unit, Tersedia: {{ $item->barang->jumlah_barang }} unit
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>
                                        @endif
                                        
                                    </td>
                                </tr>
                                @endforeach
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #1f2937; color: #ffffff; text-align: center; padding: 25px; border-radius: 0 0 12px 12px;">
                            <h3 style="margin: 0 0 10px 0; font-size: 16px; font-weight: bold;">{{ config('app.name') }}</h3>
                            <p style="margin: 8px 0 0 0; font-size: 12px; opacity: 0.7;">Dikirim pada: {{ now()->format('d M Y, H:i') }} WIB</p>
                        </td>
                    </tr>
                    
                </table>
                
            </td>
        </tr>
    </table>
    
</body>
</html>