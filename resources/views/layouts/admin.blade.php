<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Admin Dashboard' }} - Satpam UNHAS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
    @yield('styles')
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-brand">
            <img src="https://unhas.ac.id/wp-content/uploads/2019/10/logo-unhas-1.png" alt="Logo UNHAS">
            <div>
                <h3 style="font-size: 16px; letter-spacing: 1px;">SATPAM</h3>
                <p style="font-size: 10px; opacity: 0.6;">API ARCHITECTURE</p>
            </div>
        </div>
        <div class="sidebar-nav">
            <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i data-lucide="layout-dashboard"></i> Dashboard
            </a>
            <a href="#" class="nav-item">
                <i data-lucide="user-check"></i> Verifikasi
            </a>
            <a href="#" class="nav-item">
                <i data-lucide="clipboard-list"></i> Absensi
            </a>
            <a href="#" class="nav-item">
                <i data-lucide="alert-triangle"></i> Laporan
            </a>
            <a href="#" class="nav-item">
                <i data-lucide="settings"></i> Pengaturan
            </a>
            
            <div style="margin-top: 50px; padding: 10px 15px; border-top: 1px solid rgba(255,255,255,0.1);">
                <form action="/logout" method="POST">
                    @csrf
                    <button type="submit" class="nav-item" style="background: none; border: none; width: 100%; cursor: pointer;">
                        <i data-lucide="log-out"></i> Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <main class="main-content">
        <nav class="top-nav">
            <div class="breadcrumb">
                <p style="color: var(--text-muted); font-size: 14px;">Pages / {{ $title ?? 'Dashboard' }}</p>
                <h2 style="font-size: 18px;">{{ $title ?? 'Dashboard' }}</h2>
            </div>
            <div class="user-profile">
                <i data-lucide="user" style="width: 18px; color: var(--primary);"></i>
                <span style="font-weight: 600; font-size: 14px;">Administrator</span>
            </div>
        </nav>

        @yield('content')
    </main>

    <script>
        lucide.createIcons();
    </script>
    @yield('scripts')
</body>
</html>
