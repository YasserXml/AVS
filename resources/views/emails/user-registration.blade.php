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
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            background-color: #222222;
            padding: 30px 20px;
            text-align: center;
            position: relative;
        }
        
        .logo-container {
            background-color: #ffffff;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin: 0 auto 15px;
            padding: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
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
            font-size: 26px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        .content {
            padding: 35px 30px;
            background-color: #ffffff;
        }
        
        .user-info {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            margin: 25px 0;
            border-left: 5px solid #e30613;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
        }
        
        .user-info p {
            margin: 12px 0;
            display: flex;
            flex-wrap: wrap;
        }
        
        .user-info strong {
            min-width: 160px;
            color: #222222;
            font-weight: 600;
        }
        
        .button-container {
            text-align: center;
            margin: 35px 0;
        }
        
        .verify-button {
            display: inline-block;
            background-color: #e30613;
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(227, 6, 19, 0.3);
        }
        
        .verify-button:hover {
            background-color: #d00511;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(227, 6, 19, 0.4);
        }
        
        .footer {
            text-align: center;
            padding: 25px 20px;
            background-color: #222222;
            color: #ffffff;
            font-size: 14px;
        }
        
        .footer p {
            margin: 8px 0;
        }
        
        .safety-notice {
            background-color: #fff8e1;
            border-radius: 10px;
            padding: 18px;
            margin-top: 25px;
            border-left: 5px solid #e30613;
            font-size: 14px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
        }
        
        .divider {
            height: 5px;
            background: linear-gradient(to right, #e30613 33%, #ffffff 33%, #ffffff 66%, #222222 66%);
            margin: 0;
        }
        
        .greeting {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #222222;
        }
        
        .message {
            font-size: 16px;
            margin-bottom: 20px;
            color: #555555;
        }
        
        .notification-badge {
            display: inline-block;
            background-color: #e30613;
            color: white;
            font-size: 13px;
            padding: 4px 10px;
            border-radius: 30px;
            margin-bottom: 15px;
            font-weight: 500;
        }
        
        .footer-logo {
            margin-bottom: 15px;
            opacity: 0.8;
        }
        
        .footer-links {
            margin: 15px 0;
        }
        
        .footer-links a {
            color: #ffffff;
            margin: 0 10px;
            text-decoration: none;
            opacity: 0.8;
            transition: opacity 0.3s;
        }
        
        .footer-links a:hover {
            opacity: 1;
        }
        
        /* Responsive */
        @media only screen and (max-width: 600px) {
            .container {
                margin: 10px;
                width: calc(100% - 20px);
            }
            
            .content {
                padding: 25px 20px;
            }
            
            .header h1 {
                font-size: 22px;
            }
            
            .user-info {
                padding: 20px 15px;
            }
            
            .user-info strong {
                min-width: 100%;
                margin-bottom: 5px;
            }
            
            .verify-button {
                padding: 12px 25px;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo-container">
                <img src="{{ $message->embed(public_path('images/Logo.png')) }}" alt="{{ config('app.name') }} Logo">
            </div>
            <h1>Pendaftaran Pengguna Baru</h1>
        </div>
        
        <div class="divider"></div>
        
        <div class="content">
            <span class="notification-badge">Memerlukan Verifikasi</span>
            
            <p class="greeting">Halo, <strong>{{ $adminName }}</strong></p>
            
            <p class="message">Ada pendaftaran pengguna baru yang memerlukan verifikasi dari Anda. Berikut adalah detail pengguna tersebut:</p>
            
            <div class="user-info">
                <p><strong>Nama:</strong> <span>{{ $userName }}</span></p>
                <p><strong>Email:</strong> <span>{{ $userEmail }}</span></p>
                <p><strong>Divisi:</strong> <span>{{ $userDivisi }}</span></p>
                <p><strong>Tanggal Pendaftaran:</strong> <span>{{ $registrationDate }}</span></p>
            </div>
            
            <div class="button-container">
                <a href="{{ $verificationUrl }}" class="verify-button">Verifikasi Sekarang</a>
            </div>
            
            <div class="safety-notice">
                <p><strong>Catatan Keamanan:</strong> Link ini hanya berlaku selama 7 hari sejak email ini dikirim.</p>
            </div>
        </div>
        
        <div class="divider"></div>
        
        <div class="footer">
            <p>Terima kasih telah membantu menjaga keamanan sistem kami.</p>
            <p>&copy;  {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>