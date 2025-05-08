<!DOCTYPE html>
<html>
<head>
    <title>Akun Diverifikasi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4a6cf7;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 0 0 5px 5px;
            border: 1px solid #ddd;
        }
        .button {
            display: inline-block;
            background-color: #4a6cf7;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 0.8rem;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Akun Anda Telah Diverifikasi</h1>
    </div>
    
    <div class="content">
        <p>Halo {{ $notifiable->name }},</p>
        
        <p>Selamat! Akun Anda telah berhasil diverifikasi oleh administrator.</p>
        
        <p>Anda sekarang dapat masuk ke sistem AVSimulator dan mulai menggunakannya.</p>
        
        <div style="text-align: center;">
            <a href="{{ route('filament.admin.auth.login') }}" class="button">Login Sekarang</a>
        </div>
        
        <p>Jika Anda memiliki pertanyaan atau memerlukan bantuan, jangan ragu untuk menghubungi tim dukungan kami.</p>
        
        <p>Terima kasih telah menggunakan aplikasi AVSimulator!</p>
        
        <p>Salam,<br>Tim AVSimulator</p>
    </div>
    
    <div class="footer">
        <p>&copy; {{ date('Y') }} AVSimulator. All rights reserved.</p>
    </div>
</body>
</html>