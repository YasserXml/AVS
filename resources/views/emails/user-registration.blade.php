<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Pengguna Baru</title>
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
            background-color: #455056;
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
            color: #222222;
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
            color: #455056;
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
            background-color: #455056;
            color: #ffffff;
            font-size: 12px;
        }
        
        .footer p {
            margin: 5px 0;
        }
        
        .safety-notice {
            background-color: #fff8e1;
            border-radius: 8px;
            padding: 12px;
            margin-top: 15px;
            border-left: 4px solid #e30613;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo-container">
                <img src="{{ $message->embed(public_path('images/Logo.png')) }}" alt="Logo">
            </div>
            <h1>Pendaftaran Pengguna Baru</h1>
        </div>
        
        <div class="divider"></div>
        
        <div class="content">
            <span class="status-badge">Memerlukan Verifikasi</span>
            
            <p class="greeting">Halo, <strong>{{ $adminName }}</strong></p>
            
            <p class="message">Ada pendaftaran pengguna baru yang memerlukan verifikasi dari Anda. Berikut adalah detail pengguna tersebut:</p>
            
            <div class="info-box">
                <p><strong>Nama:</strong> <span>{{ $userName }}</span></p>
                <p><strong>Email:</strong> <span>{{ $userEmail }}</span></p>
                <p><strong>Divisi:</strong> <span>{{ $userDivisi }}</span></p>
                <p><strong>Tanggal Pendaftaran:</strong> <span>{{ $registrationDate }}</span></p>
            </div>
            
            <div class="button-container">
                <a href="{{ $verificationUrl }}" class="action-button">Verifikasi Sekarang</a>
            </div>
            
            <div class="safety-notice">
                <p><strong>Catatan Keamanan:</strong> Link ini hanya berlaku selama 7 hari sejak email ini dikirim.</p>
            </div>
        </div>
        
        <div class="divider"></div>
        
        <div class="footer">
            <p>Terima kasih telah membantu menjaga keamanan sistem kami.</p>
            <p>&copy; {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>