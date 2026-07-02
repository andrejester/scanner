<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Kode Verifikasi 2FA</title>
    <style>
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 30px;
            color: #333;
        }

        .container {
            background-color: #ffffff;
            max-width: 500px;
            margin: 0 auto;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            padding: 30px;
        }

        .title {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            text-align: center;
            margin-bottom: 20px;
        }

        .otp-box {
            font-size: 26px;
            text-align: center;
            font-weight: bold;
            letter-spacing: 4px;
            background-color: #f0f3f8;
            padding: 15px 20px;
            border-radius: 6px;
            color: #1a73e8;
            margin: 20px 0;
        }

        .footer {
            text-align: center;
            font-size: 13px;
            color: #777;
            margin-top: 30px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="title">Hai, {{ $name }} 👋</div>

        <p>Berikut kode verifikasi <strong>2FA (Two-Factor Authentication)</strong> Anda:</p>

        <div class="otp-box">{{ $otp }}</div>

        <p>Kode ini berlaku selama <strong>5 menit</strong>. Jangan berikan kode ini kepada siapa pun.</p>

        <div class="footer">
            © {{ date('Y') }} {{ config('app.name') }}. Semua Hak Dilindungi.
        </div>
    </div>
</body>

</html>
