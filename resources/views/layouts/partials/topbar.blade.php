<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow-sm border-bottom">
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars text-primary"></i>
    </button>

    <div class="d-none d-sm-inline-block mr-auto ml-md-3 my-2 my-md-0 mw-100">
        <h5 class="mb-0 text-gray-800 font-weight-bold">Sistem Monitoring Satpam UNHAS</h5>
    </div>

    <ul class="navbar-nav ml-auto">
        <!-- Notifikasi -->
        @if(auth()->user() && auth()->user()->role === 'admin')
        <li class="nav-item dropdown no-arrow mx-1">
            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-bell fa-fw"></i>
                <!-- Counter - Alerts -->
                @if(isset($notificationsCount) && $notificationsCount > 0)
                <span class="badge badge-danger badge-counter">{{ $notificationsCount > 99 ? '99+' : $notificationsCount }}</span>
                @endif
            </a>
            <!-- Dropdown - Alerts -->
            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in border-0"
                aria-labelledby="alertsDropdown">
                <h6 class="dropdown-header bg-primary text-white border-0 py-2">
                    Pusat Notifikasi
                </h6>
                @if(isset($notifications) && $notifications->count() > 0)
                    @foreach($notifications as $notif)
                    <a class="dropdown-item d-flex align-items-center py-2" href="{{ $notif['link'] }}">
                        <div class="mr-3">
                            <div class="icon-circle bg-light d-flex align-items-center justify-content-center rounded-circle" style="width: 40px; height: 40px;">
                                <i class="{{ $notif['icon'] }} text-{{ $notif['color'] }}"></i>
                            </div>
                        </div>
                        <div>
                            <div class="small text-gray-500 mb-1">{{ $notif['time'] }}</div>
                            <span class="font-weight-bold" style="font-size: 13px;">{{ $notif['text'] }}</span>
                        </div>
                    </a>
                    @endforeach
                @else
                    <a class="dropdown-item text-center small text-gray-500 py-4" href="#">Tidak ada aktivitas baru hari ini</a>
                @endif
            </div>
        </li>
        @endif

        <div class="topbar-divider d-none d-sm-block"></div>

        <!-- User Information -->
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <div class="d-flex flex-column text-right mr-3 d-none d-lg-block">
                    <span class="text-gray-900 small font-weight-bold mb-0"
                        style="line-height: 1;">{{ auth()->user()->nama ?? 'Administrator' }}</span>
                    <span class="text-muted small" style="font-size: 10px;">{{ auth()->user()->nip }}</span>
                </div>
                <div class="avatar-circle bg-primary text-white d-flex align-items-center justify-content-center rounded-circle shadow-sm"
                    style="width: 35px; height: 35px;">
                    <i class="fas fa-user-shield fa-sm"></i>
                </div>
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in border-0"
                aria-labelledby="userDropdown">
                <a class="dropdown-item py-2" href="#">
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                    Profil Saya
                </a>
                <a class="dropdown-item py-2" href="{{ route('ringkasan.sistem') }}">
                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                    Status Sistem
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item py-2 text-danger" href="#" data-toggle="modal" data-target="#logoutModal">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-danger"></i>
                    Keluar Sistem
                </a>
            </div>
        </li>
    </ul>
</nav>

{{-- Logout Modal --}}
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title font-weight-bold" id="exampleModalLabel">Konfirmasi Keamanan</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body py-4">
                <p class="mb-0 text-muted">Apakah Anda yakin ingin mengakhiri sesi administrasi ini?</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button class="btn btn-light px-4" type="button" data-dismiss="modal">Batal</button>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger px-4 shadow-sm">Ya, Keluar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const alertsDropdown = document.getElementById('alertsDropdown');
    const badgeCounter = document.querySelector('.badge-counter');
    
    if (alertsDropdown) {
        alertsDropdown.addEventListener('click', function() {
            if (badgeCounter && badgeCounter.style.display !== 'none') {
                fetch("{{ route('admin.notifications.read') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        badgeCounter.style.display = 'none';
                    }
                })
                .catch(error => console.error('Error marking notifications as read:', error));
            }
        });
    }
});
</script>
