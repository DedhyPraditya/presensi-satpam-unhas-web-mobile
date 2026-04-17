<ul class="navbar-nav bg-gradient-unhas sidebar sidebar-dark accordion" id="accordionSidebar">
    <!-- Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="/">
        <div class="sidebar-brand-icon">
            <img src="{{ asset('img/logo_unhas.png') }}" alt="Logo UNHAS" style="height: 40px;">
        </div>
        <div class="sidebar-brand-text mx-3">SATPAM UNHAS</div>
    </a>

    <hr class="sidebar-divider my-0">

    <!-- Dashboard -->
    <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.dashboard') }}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Ringkasan Dashboard</span>
        </a>
    </li>

    <!-- Laporan Kejadian -->
    <li class="nav-item {{ request()->routeIs('admin.laporan') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.laporan') }}">
            <i class="fas fa-fw fa-exclamation-circle"></i>
            <span>Laporan Kejadian</span>
        </a>
    </li>

    <!-- Verifikasi User -->
    <li class="nav-item {{ request()->routeIs('verifikasi.user') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('verifikasi.user') }}">
            <i class="fas fa-fw fa-user-check"></i>
            <span>Verifikasi User</span>
        </a>
    </li>

    <hr class="sidebar-divider">
    <div class="sidebar-heading">Pengaturan Sistem</div>

    <!-- Manajemen POS -->
    <li class="nav-item {{ request()->routeIs('tambah.pos') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('tambah.pos') }}">
            <i class="fas fa-fw fa-map-marker-alt"></i>
            <span>Manajemen POS Jaga</span>
        </a>
    </li>

    <!-- Jam Kerja -->
    <li class="nav-item {{ request()->routeIs('edit.jam') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('edit.jam') }}">
            <i class="fas fa-fw fa-clock"></i>
            <span>Edit Jam Kerja</span>
        </a>
    </li>

    <!-- Ekspor Data -->
    <li class="nav-item {{ request()->routeIs('ekspor.laporan') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('ekspor.laporan') }}">
            <i class="fas fa-fw fa-file-export"></i>
            <span>Ekspor Laporan</span>
        </a>
    </li>

    <!-- Status Sistem -->
    <li class="nav-item {{ request()->routeIs('ringkasan.sistem') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('ringkasan.sistem') }}">
            <i class="fas fa-fw fa-info-circle"></i>
            <span>Ringkasan Sistem</span>
        </a>
    </li>

    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>
