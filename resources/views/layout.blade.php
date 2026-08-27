<!DOCTYPE html>
@php
    $appSetting = \App\Models\Setting::first() ?? new \App\Models\Setting(['app_name' => 'Kasir Pintar', 'theme_color' => '#4361ee']);
@endphp
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Kasir Pintar</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: {{ $appSetting->theme_color ?? '#4361ee' }};
            --sidebar-bg: {{ $appSetting->sidebar_color ?? '#111827' }};
            --sidebar-hover: rgba(255, 255, 255, 0.1); /* Hover otomatis transparan putih */
            --bg-color: #f4f7fe;
            --card-bg: #ffffff;
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --border: #e5e7eb;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-main);
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 260px;
            background-color: var(--sidebar-bg);
            color: white;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
            position: fixed;
            height: 100vh;
            z-index: 100;
        }

        .sidebar-header {
            padding: 24px;
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            text-align: center;
            border-bottom: 1px solid var(--sidebar-hover);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px; /* Jarak antara logo dan teks */
        }

        .sidebar-menu {
            padding: 20px 0;
            flex: 1;
        }

        .menu-item {
            padding: 15px 24px;
            display: flex;
            align-items: center;
            gap: 15px;
            color: #d1d5db;
            text-decoration: none;
            transition: all 0.2s;
            font-weight: 500;
        }

        .menu-item:hover, .menu-item.active {
            background-color: var(--sidebar-hover);
            color: white;
            border-left: 4px solid var(--primary);
        }

        .menu-item i {
            width: 20px;
            text-align: center;
        }

        /* Main Content Styling */
        .main-content {
            flex: 1;
            margin-left: 260px;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
        }

        /* Topbar */
        .topbar {
            background-color: var(--card-bg);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 99;
        }

        .menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--text-main);
            cursor: pointer;
        }

        .admin-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
        }

        .admin-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        /* Dashboard Content */
        .content-area {
            padding: 30px;
        }

        .page-title {
            margin-bottom: 20px;
            font-size: 1.5rem;
            color: var(--text-main);
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background-color: var(--card-bg);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            display: flex;
            align-items: center;
            gap: 20px;
            border: 1px solid var(--border);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            background-color: #e0e7ff;
            color: var(--primary);
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.8rem;
        }

        .stat-info h3 {
            font-size: 0.9rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .stat-info p {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-main);
        }

        /* Recent Table */
        .table-container {
            background-color: var(--card-bg);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            border: 1px solid var(--border);
            overflow-x: auto;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th, td {
            padding: 15px;
            border-bottom: 1px solid var(--border);
        }

        th {
            background-color: #f8fafc;
            color: var(--text-muted);
            font-weight: 600;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            background-color: #dcfce7;
            color: #166534;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
            .menu-toggle {
                display: block;
            }
        }
    </style>   
</head>
<body>

    <aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        @if($appSetting->logo_path)
            <img src="{{ asset($appSetting->logo_path) }}" alt="Logo" style="width: 35px; height: 35px; object-fit: contain; border-radius: 5px; background-color: white; padding: 2px;">
        @else
            <i class="fa-solid fa-store"></i> 
        @endif
        <span style="font-size: 1.1rem;">{{ $appSetting->app_name }}</span>
    </div>
    <div class="sidebar-menu">
        @if (auth()->user()->is_admin==1)
        <a href="/admin/settings" class="menu-item {{ request()->is('admin/settings') ? 'active' : '' }}">
            <i class="fa-solid fa-gear"></i> Pengaturan
        </a>
        @endif
        @if (auth()->user()->is_admin==1)
        <a href="/" class="menu-item {{ request()->is('/') ? 'active' : '' }}">
            <i class="fa-solid fa-chart-pie"></i> Dashboard
        </a>
        <a href="/admin/products" class="menu-item {{ request()->is('admin/products*') ? 'active' : '' }}">
            <i class="fa-solid fa-box-open"></i> Kelola Produk
        </a>
        @endif
        @if (auth()->user()->is_admin==1)
        <a href="/admin/report" class="menu-item {{ request()->is('admin/report*') ? 'active' : '' }}">
            <i class="fa-solid fa-receipt"></i> Data Transaksi
        </a>
        @endif
        <a href="/pos" class="menu-item {{ request()->is('pos') ? 'active' : '' }}">
            <i class="fa-solid fa-money-bill"></i> Kasir
        </a>
        @if (auth()->user()->is_admin==1)
        <a href="/users" class="menu-item {{ request()->is('users') ? 'active' : '' }}">
            <i class="fa-solid fa-users"></i> User
        </a>
        @endif
        <form id="logout-form" action="/logout" method="POST" style="display: none;">
            @csrf
        </form>
        <a href="/logout" class="menu-item" style="margin-top: auto;" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
    </div>
    </aside>
    <div class="main-content" style="background: {{ $appSetting->theme_color ?? '#4361ee' }}">
        <header class="topbar" style="background: {{ $appSetting->sidebar_color ?? '#111827' }}">
            <button class="menu-toggle" id="menu-toggle">
                <i class="fa-solid fa-bars"></i>
            </button>
            <div class="admin-profile">
                <span style="color: floralwhite">Halo, {{ auth()->user()->name ?? 'Admin' }}</span>
                <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'Admin') }}&background={{ str_replace('#', '', $appSetting->theme_color) ?? '4361ee' }}&color={{ str_replace('#', '', $appSetting->sidebar_color) ?? '111827' }}" alt="Admin">
            </div>
    </header>
        @yield('container')
    </div>
    <script>
        const menuToggle = document.getElementById('menu-toggle');
        const sidebar = document.getElementById('sidebar');

        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });
    </script>
<script>
    const successAlert = document.getElementById('success-alert');
    if (successAlert) {
        setTimeout(function() {
            // Memberikan efek fade out sederhana
            successAlert.style.opacity = '0';
            
            // Menghapus elemen dari tampilan setelah efek fade out selesai (0.5 detik)
            setTimeout(function() {
                successAlert.style.display = 'none';
            }, 500);
        }, 5000); // 10000 milidetik = 10 detik
    }
</script>
</body>
</html>