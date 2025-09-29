<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Dashboard Guru</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    {{-- jQuery (WAJIB sebelum toastr 2.1.4) --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    {{-- Toastr --}}
    <link href="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.js"></script>

    <style>
        body {
            min-height: 100vh;
            display: flex;
        }

        #sidebar {
            min-width: 250px;
            max-width: 250px;
            background: #343a40;
            color: white;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1050;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            padding: 1rem;
            transition: all 0.3s ease;
        }

        #sidebar.collapsed {
            min-width: 70px;
            max-width: 70px;
        }

        #sidebar .nav-link {
            display: flex;
            align-items: center;
            height: 44px;
            padding: 0 12px;
            gap: 8px;
            /* dari 12px jadi 8px atau bahkan 6px */
        }

        #sidebar .nav-link.active {
            background-color: #198754;
            color: white !important;
        }


        #content {
            margin-left: 250px;
            width: 100%;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }

        #content.expanded {
            margin-left: 70px;
        }

        #sidebar.collapsed .nav-link span.menu-text {
            display: none;
        }

        #sidebar.collapsed .nav-link {
            text-align: center;
        }

        #sidebar .sidebar-header span {
            transition: opacity 0.3s ease;
        }

        #sidebar.collapsed .sidebar-header span {
            opacity: 0;
        }

        #toggleSidebarBtn {
            background-color: #198754;
            color: white;
            border: none;
            font-size: 1.2rem;
            width: 40px;
            height: 40px;
            border-radius: 4px;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 0;
            cursor: pointer;
            z-index: 1200;
        }

        #toggleSidebarBtn:hover {
            background-color: #145c32;
        }

        #sidebar ul.nav {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            padding-left: 0;
            margin-bottom: 0;
            list-style: none;
        }

        #sidebar ul.nav li.logout-item {
            margin-top: auto;
        }

        #sidebar .nav-link.logout {
            background-color: #dc3545 !important;
            color: white !important;
            text-align: center;
            border-radius: 0.25rem;
            justify-content: center;
        }

        #sidebar.collapsed .nav-link.active {
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        #sidebar.collapsed .nav-link.logout {
            text-align: center;
        }

        #sidebar.collapsed .menu-text {
            opacity: 0;
            width: 0;
            margin: 0;
        }

        #sidebar.collapsed .nav-link {
            text-align: center;
            padding: 0.5rem;
            justify-content: center;
            width: 100%;
            display: flex;
            align-items: center;
        }

        #sidebar.collapsed .nav-link i {
            margin-right: 0 !important;
            /* Hilangkan spasi kanan icon */
        }


        #sidebar .nav-link i {
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
            line-height: 1;
            transition: all 0.3s ease;
            flex-shrink: 0;
        }

        #sidebar .nav-link .menu-text {
            display: inline-block;
            transition: opacity 0.3s ease, width 0.3s ease;
        }

        #sidebar.collapsed .nav-link .menu-text {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }


        #sidebar .menu-text {
            white-space: nowrap;
            overflow: hidden;
            transition: opacity 0.3s ease, width 0.3s ease, margin-left 0.3s ease;
        }

        #sidebar.collapsed .menu-text {
            opacity: 0;
            width: 0;
            margin-left: 0;
            pointer-events: none;
        }

        #sidebar .nav-item {
            min-height: 44px;
        }

        .card-link {
            transition: transform .15s ease, box-shadow .15s ease;
        }

        .card-link:hover {
            transform: translateY(-4px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, .15);
            opacity: 0.9;
            /* efek sedikit gelap */
            cursor: pointer;
        }




        @media (max-width: 768px) {
            #sidebar {
                left: -250px;
                z-index: 9999;
                transition: left 0.3s ease;
            }

            #sidebar.active {
                left: 0;
            }

            #content {
                margin-left: 0 !important;
                padding-top: 60px;
                position: relative;
                z-index: 1;
            }

            #content.overlay {
                z-index: 1000;
                background-color: rgba(0, 0, 0, 0.3);
            }

            #toggleSidebarBtn {
                position: fixed;
                top: 1rem;
                left: 1rem;
                z-index: 1200;
            }

            #sidebar.active #toggleSidebarBtn {
                position: absolute;
                top: 1rem;
                right: 1rem;
                left: auto;
                z-index: 1201;
            }

            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                width: 100vw;
                background-color: rgba(0, 0, 0, 0.4);
                z-index: 1030;
                /* Lebih rendah dari backdrop modal (1050) */
                display: none;
            }

            .sidebar-overlay.active {
                display: block;
            }

            /* FIX utama: saat modal terbuka, overlay sidebar dipaksa hilang */
            body.modal-open .sidebar-overlay {
                display: none !important;
            }

            body.modal-open #toggleSidebarBtn {
                pointer-events: none;
                opacity: 0.3;
            }
        }

        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <nav id="sidebar" class="d-flex flex-column p-3">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <button id="toggleSidebarBtn" aria-label="Toggle sidebar">☰</button>
            <span class="text-white fs-5 d-md-none d-inline-block">Guru</span>
        </div>

        <a href="{{ route('guru.dashboard') }}"
            class="sidebar-header d-none d-md-flex align-items-center mb-3 text-white text-decoration-none fs-4">
            <span class="fs-4">Guru</span>
        </a>

        <hr class="text-white" />

        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="{{ route('guru.dashboard') }}"
                    class="nav-link sidebar-link {{ request()->routeIs('guru.dashboard') ? 'active' : 'text-white' }}">
                    <i class="fas fa-home me-2"></i>
                    <span class="menu-text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('guru.jadwal.index') }}"
                    class="nav-link sidebar-link {{ request()->routeIs('guru.jadwal.*') ? 'active' : 'text-white' }}">
                    <i class="fas fa-calendar-alt me-2"></i>
                    <span class="menu-text">Jadwal Mengajar</span>
                </a>
            </li>
            <li>
                <a href="{{ route('guru.jadwal_khusus.index') }}"
                    class="nav-link sidebar-link {{ request()->routeIs('guru.jadwal_khusus.*') ? 'active' : 'text-white' }}">
                    <i class="fas fa-calendar-check me-2"></i>
                    <span class="menu-text">Jadwal Khusus</span>
                </a>
            </li>
            <li>
                <a href="{{ route('guru.profil.index') }}"
                    class="nav-link sidebar-link {{ request()->routeIs('guru.profil.*') ? 'active' : 'text-white' }}">
                    <i class="fas fa-user me-2"></i>
                    <span class="menu-text">Profil</span>
                </a>
            </li>
            <li class="logout-item">
                <a href="{{ route('logout') }}"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                    class="nav-link logout">
                    <i class="fas fa-sign-out-alt me-2"></i>
                    <span class="menu-text">Logout</span>
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </li>
        </ul>
    </nav>

    <div id="sidebarOverlay" class="sidebar-overlay"></div> {{-- Tambahkan ini --}}

    <div id="content">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const toggleBtn = document.getElementById('toggleSidebarBtn');
        const sidebar = document.getElementById('sidebar');
        const content = document.getElementById('content');
        const overlay = document.getElementById('sidebarOverlay');

        function isMobile() {
            return window.innerWidth < 768;
        }

        toggleBtn.addEventListener('click', () => {
            if (isMobile()) {
                const isOpen = sidebar.classList.toggle('active');
                overlay.classList.toggle('active', isOpen);
            } else {
                sidebar.classList.toggle('collapsed');
                content.classList.toggle('expanded');
            }
        });

        overlay.addEventListener('click', () => {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        });

        // TRANSISI GANTI HALAMAN DI MOBILE
        document.querySelectorAll('.sidebar-link').forEach(link => {
            link.addEventListener('click', function(e) {
                if (isMobile() && sidebar.classList.contains('active')) {
                    e.preventDefault();
                    const href = this.href;
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');

                    sidebar.addEventListener('transitionend', function handler() {
                        window.location.href = href;
                        sidebar.removeEventListener('transitionend', handler);
                    });
                }
            });
        });
    </script>


    @stack('modals')
    @stack('scripts')

    @if (session('toast_error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            toastr.options = {
                closeButton: true,
                progressBar: true,
                timeOut: 5000,
                positionClass: 'toast-top-right' // atau posisi lainnya sesuai kebutuhan
            };
            toastr.error("{{ session('toast_error') }}");
        });
    </script>
    @endif


    @if (session('toast_success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            toastr.options = {
                closeButton: true,
                progressBar: true,
                timeOut: 5000,
                positionClass: 'toast-top-right',
                toastClass: 'toast toast-light-success',
            };
            toastr.success("{{ session('toast_success') }}");
        });
    </script>
    @endif

</body>

</html>