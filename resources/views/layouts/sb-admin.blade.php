<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ $title ?? 'Dashboard' }} - Satpam UNHAS</title>

    {{-- Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('sb-admin-2/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    
    {{-- Core CSS --}}
    <link href="{{ asset('sb-admin-2/css/sb-admin-2.min.css') }}" rel="stylesheet">

    <style>
        :root {
            --primary: #9b1c1c; /* Deep Red Unhas */
            --primary-light: #ef5350;
            --primary-dark: #7f1d1d;
            --accent: #224abe; /* Blue contrast */
            --success: #10b981;
            --warning: #f59e0b;
            --bg: #f8fafc;
            --sidebar-bg: #9b1c1c; /* Dark Red Sidebar */
            --radius: 12px;
        }

        .badge-ok { background: #d1fae5; color: #065f46; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; display: inline-block; }
        .badge-late { background: #fee2e2; color: #7f1d1d; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; display: inline-block; }
        .badge-none { background: #f1f5f9; color: #64748b; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; display: inline-block; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: #1e293b;
        }

        h1, h2, h3, h4, .sidebar-brand-text {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
        }

        .card {
            border-radius: var(--radius);
            border: 1px solid rgba(0,0,0,0.05);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .bg-gradient-unhas {
            background-color: var(--sidebar-bg);
            background-image: linear-gradient(180deg, var(--sidebar-bg) 10%, var(--primary-dark) 100%);
        }

        .btn-primary {
            background-color: var(--primary) !important;
            border-color: var(--primary) !important;
            border-radius: 10px;
            font-weight: 600;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark) !important;
            border-color: var(--primary-dark) !important;
        }

        /* Override Global SB Admin Bootstrap Primary Color */
        .text-primary { color: var(--accent) !important; }
        .bg-primary { background-color: var(--primary) !important; }
        .border-primary { border-color: var(--primary) !important; }
        .border-left-primary { border-left-color: var(--accent) !important; }
        .badge-primary { background-color: var(--accent) !important; }

        /* Holy Grail Fixed Layout */
        #wrapper {
            height: 100vh;
            display: flex;
            overflow: hidden;
        }
        
        .sidebar {
            height: 100vh !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            z-index: 1030;
            flex-shrink: 0;
            scrollbar-width: thin;
        }

        #content-wrapper {
            height: 100vh;
            display: flex !important;
            flex-direction: column;
            overflow: hidden !important;
            flex-grow: 1;
            width: 100%;
        }

        #content {
            display: flex;
            flex-direction: column;
            height: 100%;
            overflow: hidden;
        }

        nav.topbar {
            flex-shrink: 0;
            z-index: 1020;
        }

        .container-fluid {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding-top: 1.5rem;
            background-color: var(--bg);
        }

        /* Unified Scrollbar Styling */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* Dedicated Sidebar Scrollbar */
        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.2); }

        /* Modal Backdrop Blur */
        .modal-backdrop.show {
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            background-color: rgba(0, 0, 0, 0.5) !important;
        }
        .modal-content {
            border-radius: 15px !important;
            border: none !important;
            overflow: hidden;
        }
    </style>
    @yield('styles')
</head>
<body id="page-top">
    <div id="wrapper">
        @include('layouts.partials.sidebar')

        <div id="content-wrapper" class="d-flex flex-column" style="background-color: var(--bg);">
            <div id="content">
                @include('layouts.partials.topbar')

                <div class="container-fluid pb-5">
                    @yield('content')
                </div>
            </div>

            <footer class="sticky-footer bg-white shadow-sm mt-auto">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span class="text-muted small">Copyright &copy; Satpam UNHAS Modern {{ date('Y') }}</span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    {{-- Scripts --}}
    <script src="{{ asset('sb-admin-2/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('sb-admin-2/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('sb-admin-2/vendor/jquery-easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('sb-admin-2/js/sb-admin-2.min.js') }}"></script>
    
    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const SwalToast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        $(document).ready(function() {
            // Flash Messages Detect
            @if(session('success'))
                SwalToast.fire({ icon: 'success', title: "{{ session('success') }}" });
            @endif
            
            @if(session('error'))
                SwalToast.fire({ icon: 'error', title: "{{ session('error') }}" });
            @endif

            @if(session('info'))
                SwalToast.fire({ icon: 'info', title: "{{ session('info') }}" });
            @endif

            @if(session('warning'))
                SwalToast.fire({ icon: 'warning', title: "{{ session('warning') }}" });
            @endif

            // Global Delete Confirmation
            $(document).on('click', '.confirm-delete', function(e) {
                e.preventDefault();
                let form = $(this).closest('form');
                let message = $(this).data('confirm') || "Data yang dihapus tidak dapat dikembalikan!";
                let icon = $(this).data('icon') || "warning";
                let confirmText = $(this).data('confirm-text') || "Ya, Lanjutkan!";
                
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: message,
                    icon: icon,
                    showCancelButton: true,
                    confirmButtonColor: '#9b1c1c',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: confirmText,
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                    customClass: {
                        confirmButton: 'rounded-pill px-4',
                        cancelButton: 'rounded-pill px-4'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
    
    @yield('scripts')
</body>
</html>