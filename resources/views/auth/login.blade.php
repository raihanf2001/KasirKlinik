<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    @php
        $appSetting = \App\Models\Setting::first() ?? new \App\Models\Setting(['app_name' => 'Kasir Pintar', 'theme_color' => '#4361ee']);
    @endphp

    <title>Login - {{ $appSetting->app_name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            /* Warna Utama Mengikuti Database */
            --primary: {{ $appSetting->theme_color }};
            --bg-color: #f4f7fe;
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --border: #e5e7eb;
            --danger: #ef4444;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-wrapper {
            background-color: white;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            display: flex;
            max-width: 900px;
            width: 100%;
            overflow: hidden;
            min-height: 500px;
        }

        /* Bagian Kiri (Branding) */
        .login-left {
            flex: 1;
            /* Efek gradient yang menyesuaikan warna apapun dari database */
            background-color: var(--primary);
            background-image: linear-gradient(135deg, rgba(255,255,255,0.15) 0%, rgba(0,0,0,0.25) 100%);
            color: white;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .login-left h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .login-left p {
            font-size: 1rem;
            opacity: 0.9;
            line-height: 1.6;
        }

        .login-left i {
            font-size: 5rem;
            margin-bottom: 20px;
            color: rgba(255, 255, 255, 0.9);
        }

        /* Styling Khusus untuk Logo Gambar */
        .logo-img {
            max-width: 120px;
            max-height: 120px;
            margin-bottom: 20px;
            object-fit: contain;
            background-color: white; /* Beri background putih agar logo gelap tetap terlihat */
            padding: 15px;
            border-radius: 20px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.15);
        }

        /* Bagian Kanan (Form) */
        .login-right {
            flex: 1;
            padding: 50px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-header {
            margin-bottom: 30px;
        }

        .login-header h2 {
            font-size: 1.8rem;
            color: var(--text-main);
            margin-bottom: 5px;
        }

        .login-header p {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        /* Input Styling */
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-main);
            font-size: 0.95rem;
        }

        .input-icon-wrapper {
            position: relative;
        }

        .input-icon-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
            outline: none;
            color: var(--text-main);
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            font-size: 0.9rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-muted);
            cursor: pointer;
        }

        .forgot-password {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-login:hover {
            /* Membuat warna tombol sedikit lebih gelap saat di-hover secara otomatis */
            filter: brightness(0.9);
        }

        /* Pesan Error Laravel */
        .error-message {
            color: var(--danger);
            font-size: 0.85rem;
            margin-top: 5px;
            display: block;
        }

        .is-invalid {
            border-color: var(--danger) !important;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .login-wrapper {
                flex-direction: column;
            }
            .login-left {
                padding: 30px;
            }
            .login-left i {
                font-size: 3.5rem;
            }
            .login-right {
                padding: 30px;
            }
        }
    </style>
</head>
<body>

    <div class="login-wrapper">
        <div class="login-left">
            @if($appSetting->logo_path)
                <img src="{{ asset($appSetting->logo_path) }}" alt="Logo" class="logo-img">
            @else
                <i class="fa-solid fa-store"></i>
            @endif
            
            <h1>{{ $appSetting->app_name }}</h1>
            <p>Kelola penjualan, pantau pendapatan, dan atur produk Anda dengan lebih mudah dan cepat dalam satu aplikasi.</p>
        </div>

        <div class="login-right">
            <div class="login-header">
                <h2>Selamat Datang! 👋</h2>
                <p>Silakan masuk ke akun Anda untuk melanjutkan.</p>
            </div>

            <form method="POST" action="/masuk">
                @csrf
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-icon-wrapper">
                        <i class="fa-regular fa-envelope"></i>
                        <input type="text" id="username" name="username" class="form-control" placeholder="admin@kasirpintar.com" value="{{ old('username') }}" required autofocus>
                    </div>
                    @error('username') 
                        <span class="error-message" style="color: red; font-size: 0.85rem;">{{ $message }}</span> 
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Kata Sandi</label>
                    <div class="input-icon-wrapper">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                    @error('password') 
                        <span class="error-message" style="color: red; font-size: 0.85rem;">{{ $message }}</span> 
                    @enderror
                </div>

                <div class="form-options">
                    <a href="#" class="forgot-password">Lupa Sandi?</a>
                </div>

                <button type="submit" class="btn-login">Masuk Sekarang</button>
            </form>
        </div>
    </div>

</body>
</html>