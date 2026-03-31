<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') — {{ config('app.name') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary-color: #0f9455;
            --primary-hover: #0b7a46;
            --bg-color: #f8fafc;
            --text-main: #1f2937;
            --text-muted: #64748b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
            overflow: hidden;
        }

        .error-container {
            text-align: center;
            max-width: 500px;
            width: 100%;
            background: white;
            padding: 50px 40px;
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.05);
            position: relative;
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .brand-logo {
            width: 80px;
            height: 80px;
            background: white;
            padding: 10px;
            border-radius: 50%;
            box-shadow: 0 8px 20px rgba(15, 148, 85, 0.15);
            margin: 0 auto 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .brand-logo img {
            width: 100%;
            height: auto;
        }

        .error-code {
            font-size: 80px;
            font-weight: 800;
            color: var(--primary-color);
            line-height: 1;
            margin-bottom: 10px;
            letter-spacing: -2px;
            opacity: 0.9;
        }

        .error-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 15px;
            color: #334155;
        }

        .error-message {
            font-size: 15px;
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 35px;
        }

        .btn-home {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: var(--primary-color);
            color: white;
            padding: 14px 32px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(15, 148, 85, 0.2);
        }

        .btn-home:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(15, 148, 85, 0.3);
        }

        .btn-home i {
            font-size: 18px;
        }

        .bg-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .shape {
            position: absolute;
            background: var(--primary-color);
            opacity: 0.03;
            border-radius: 50%;
        }

        .shape-1 { width: 400px; height: 400px; top: -100px; right: -100px; }
        .shape-2 { width: 300px; height: 300px; bottom: -50px; left: -50px; }
    </style>
</head>
<body>
    <div class="bg-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
    </div>

    <div class="error-container">
        <div class="brand-logo">
            <img src="{{ asset('img/logo-cadisdik.png') }}" alt="Logo Cadisdik">
        </div>
        <div class="error-code">@yield('code')</div>
        <h1 class="error-title">@yield('title')</h1>
        <p class="error-message">@yield('message')</p>
        <a href="{{ url('/') }}" class="btn-home">
            <i class="fa-solid fa-house"></i>
            Kembali ke Beranda
        </a>
    </div>
</body>
</html>
