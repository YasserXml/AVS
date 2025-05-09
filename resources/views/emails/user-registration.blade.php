<!-- resources/views/emails/user-registration.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Pengguna Baru</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #eeeeee;
        }
        .content {
            padding: 20px 0;
        }
        .footer {
            text-align: center;
            color: #888888;
            font-size: 12px;
            padding-top: 20px;
            border-top: 1px solid #eeeeee;
        }
        .button {
            display: inline-block;
            background-color: #4a76a8;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 4px;
            font-weight: bold;
            margin: 20px 0;
        }
        h1 {
            color: #333;
            font-size: 24px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table td {
            padding: 10px;
            border-bottom: 1px solid #eeeeee;
        }
        .note {
            font-size: 13px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>AVSimulator</h1>
        </div>
        
        <div class="content">
            <h2>Halo {{ $userName }},</h2>
            
            <p>Ada pendaftaran pengguna baru yang memerlukan verifikasi.</p>
            
            <table>
                <tr>
                    <td><strong>Nama:</strong></td>
                    <td>{{ $newUserName }}</td>
                </tr>
                <tr>
                    <td><strong>Email:</strong></td>
                    <td>{{ $newUserEmail }}</td>
                </tr>
                <tr>
                    <td><strong>Tanggal Pendaftaran:</strong></td>
                    <td>{{ $registrationDate }}</td>
                </tr>
            </table>
            
            <div style="text-align: center;">
                <a href="{{ $verificationUrl }}" class="button">Verifikasi Sekarang</a>
            </div>
            
            <p class="note">Tombol ini akan langsung memverifikasi pengguna tanpa perlu membuka panel admin.</p>
            
            <p>Terima kasih telah membantu menjaga keamanan sistem!</p>
            
            <p>Salam,<br>AVSimulator</p>
        </div>
        
        <div class="footer">
            <p>© {{ date('Y') }} AVSimulator. Hak cipta dilindungi.</p>
            <p>Jika Anda mengalami kesulitan mengklik tombol "Verifikasi Sekarang", copy dan paste URL di bawah ini ke browser Anda:</p>
            <p style="word-break: break-all;">{{ $verificationUrl }}</p>
        </div>
    </div>
</body>
</html>