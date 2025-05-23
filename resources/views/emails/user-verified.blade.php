<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akun Anda Telah Diverifikasi</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f8f8;
            margin: 0;
            padding: 0;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            background-color: #455045;
            padding: 20px;
            text-align: center;
        }
        
        .logo-container {
            background-color: #ffffff;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin: 0 auto 10px;
            padding: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .header img {
            max-width: 100%;
            height: auto;
        }
        
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 20px;
            font-weight: 600;
        }
        
        .content {
            padding: 20px;
            background-color: #ffffff;
        }
        
        .status-badge {
            display: inline-block;
            background-color: #e30613;
            color: white;
            font-size: 13px;
            padding: 4px 12px;
            border-radius: 30px;
            margin-bottom: 15px;
            font-weight: 500;
        }
        
        .greeting {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #455045;
        }
        
        .message {
            font-size: 14px;
            margin-bottom: 15px;
            color: #555555;
        }
        
        .info-box {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #e30613;
        }
        
        .info-box p {
            margin: 8px 0;
            display: flex;
            flex-wrap: wrap;
            font-size: 14px;
        }
        
        .info-box strong {
            width: 140px;
            color: #455045;
            font-weight: 600;
        }
        
        .info-box span {
            flex: 1;
            min-width: 150px;
        }
        
        .button-container {
            text-align: center;
            margin: 25px 0 15px;
        }
        
        .action-button {
            display: inline-block;
            background-color: #e30613;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .divider {
            height: 4px;
            background: linear-gradient(to right, #e30613 33%, #ffffff 33%, #ffffff 66%, #222222 66%);
            margin: 0;
        }
        
        .footer {
            text-align: center;
            padding: 15px;
            background-color: #455045;
            color: #ffffff;
            font-size: 12px;
        }
        
        .footer p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo-container">
                <img src="{{ $message->embed(public_path('images/Logo.png')) }}" alt="Logo">
            </div>
            <h1>Akun Anda Telah Diverifikasi!</h1>
        </div>
        
        <div class="divider"></div>
        
        <div class="content">
            <span class="status-badge">Verifikasi Berhasil</span>
            
            <p class="greeting">Halo, <strong>{{ $userName }}</strong></p>
            
            <p class="message">Akun Anda telah berhasil diverifikasi oleh administrator</p>
            
            <div class="info-box">
                <p><strong>Nama Pengguna:</strong> <span>{{ $userName }}</span></p>
                <p><strong>Email:</strong> <span>{{ $userEmail }}</span></p>
                <p><strong>Divisi:</strong> <span>{{ $userDivisi }}</span></p>
                <p><strong>Diverifikasi pada:</strong> <span>{{ $verifiedDate }}</span></p>
            </div>
            
            <p class="message">Anda sekarang dapat login ke sistem dan menggunakan semua fitur yang tersedia</p>
            
            <div class="button-container">
                <a href="{{ $loginUrl }}" class="action-button">Login Sekarang</a>
            </div>
        </div>
        
        <div class="divider"></div>
        
        <div class="footer">
            <p>Terima kasih telah bergabung dengan {{ config('app.name') }}!</p>
            <p>&copy; {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>