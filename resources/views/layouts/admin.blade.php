<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Admin Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>




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
            gap: 4 px;
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

        .td-jadwal {
            min-height: 120px;
            vertical-align: top;
            padding: 8px;
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

        /* Pastel palette 0..9 (aman untuk teks gelap) */
        .bg-mapel-0 {
            background-color: #E3F2FD;
        }

        .bg-mapel-1 {
            background-color: #E8F5E9;
        }

        .bg-mapel-2 {
            background-color: #FFF3E0;
        }

        .bg-mapel-3 {
            background-color: #F3E5F5;
        }

        .bg-mapel-4 {
            background-color: #E0F7FA;
        }

        .bg-mapel-5 {
            background-color: #FCE4EC;
        }

        .bg-mapel-6 {
            background-color: #F1F8E9;
        }

        .bg-mapel-7 {
            background-color: #FFFDE7;
        }

        .bg-mapel-8 {
            background-color: #EDE7F6;
        }

        .bg-mapel-9 {
            background-color: #E0E0E0;
        }

        /* Khusus slot non-mapel */
        .bg-upacara {
            background-color: #D1E7DD;
        }

        .bg-ekskul {
            background-color: #CCE5FF;
        }

        .bg-kegiatan {
            background-color: #FFE5D0;
        }

        /* Atur lebar kolom dengan min-width supaya kolom tidak terlalu kecil */
        .schedule-table th:nth-child(1),
        .schedule-table td:nth-child(1) {
            width: 10%;
            min-width: 60px;
            /* minimal lebar agar kolom Jam cukup */
        }

        .schedule-table th:nth-child(2),
        .schedule-table td:nth-child(2) {
            width: 40%;
            min-width: 180px;
            /* mapel biasanya teks agak panjang */
        }

        .schedule-table th:nth-child(3),
        .schedule-table td:nth-child(3) {
            width: 30%;
            min-width: 140px;
        }

        .schedule-table th:nth-child(4),
        .schedule-table td:nth-child(4) {
            width: 20%;
            min-width: 100px;
        }

        /* Buat teks tetap rapi dan responsif */
        .schedule-table th,
        .schedule-table td {
            vertical-align: middle;
            white-space: nowrap;
            /* cegah wrap */
            overflow: hidden;
            /* sembunyikan overflow */
            text-overflow: ellipsis;
            /* potong dengan ... */
            padding: 0.5rem 0.75rem;
            /* beri padding nyaman */
            font-size: 0.9rem;
            /* sedikit lebih kecil dan rapi */
        }

        /* Agar pada layar kecil tetap bisa scroll */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            /* smooth scroll di iOS */
        }

        /* Hover effect agar baris tabel lebih jelas */
        .schedule-table tbody tr:hover {
            background-color: #f1f1f1;
        }

        /* Kartu sel jadwal */
        .card-slot {
            border: 1px solid rgba(0, 0, 0, .08);
            border-radius: .5rem;
            padding: .5rem;
        }

        .pagination .page-item .page-link {
            margin: 0 4px;
            /* jarak kiri kanan */
            padding: 0.5rem 0.9rem;
            /* biar tombol lebih lega */
        }

        .pagination {
            font-variant-numeric: tabular-nums;
            /* aktifkan angka monospasi */
            font-feature-settings: "tnum" 1;
            /* fallback */
        }

        .page-shell {
            min-height: 95vh;
            /* atau calc(100vh - 56px) jika ada navbar fixed tinggi 56px */
            display: flex;
            flex-direction: column;
        }

        /* Rapikan pagination tanpa membuat tombol melebar */
        .pagination .page-item+.page-item {
            margin-left: .25rem;
        }

        .pagination .page-link {
            white-space: nowrap;
            flex: 0 0 auto;
            padding: .5rem .9rem;
        }

        .pagination {
            font-variant-numeric: tabular-nums;
            font-feature-settings: "tnum";
        }

        @media (max-width: 768px) {
            #sidebar {
                left: -250px;
                z-index: 9999;
                transition: left 0.3s ease;
                position: fixed;
                /* pastikan fixed */
                top: 0;
                bottom: 0;
                width: 250px;
                height: 100dvh;
            }

            #sidebar.active {
                left: 0;
            }

            table td:last-child {
                min-width: 180px;
            }

            #sidebar.active {
                left: 0;
            }

            #content {
                margin-left: 0 !important;
                padding-top: 60px;
                position: relative;
                z-index: 1;
                /* kalau ada bottom nav atau tombol bawah, beri breathing space: */
                padding-bottom: calc(16px + env(safe-area-inset-bottom));
            }

            #content.overlay {
                z-index: 1000;
                background-color: rgba(0, 0, 0, 0.3);
            }

            #toggleSidebarBtn {
                position: fixed;
                top: 1rem;
                left: 1rem;
                z-index: 11000;
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
                inset: 0;
                height: 100dvh;
                /* ganti 100vh -> 100dvh */
                width: 100vw;
                background-color: rgba(0, 0, 0, 0.4);
                z-index: 1030;
                /* di bawah sidebar (9999), di atas content (1) */
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

            table td:last-child {
                min-width: 180px;
            }

            .bottom-nav,
            .footer-fixed,
            .logout-bar {
                padding-bottom: env(safe-area-inset-bottom);
            }
        }
    </style>

</head>

<body>
    <nav id="sidebar" class="no-print d-flex flex-column p-3">
        {{-- Tombol toggle sidebar (mobile) --}}
        <div class="d-flex align-items-center justify-content-start mb-3">
            <button id="toggleSidebarBtn" class="no-print" aria-label="Toggle sidebar">☰</button>
            <span class="no-print text-white fs-5 d-md-none d-inline-block">Admin</span>
        </div>

        {{-- Tulisan Admin (desktop) --}}
        <a href="{{ route('admin.dashboard') }}" class="sidebar-header d-none d-md-flex align-items-center mb-3 text-white text-decoration-none fs-4">
            <span class="fs-4">Admin</span>
        </a>

        <hr class="text-white" />

        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}"
                    class="nav-link sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : 'text-white' }}">
                    <i class="fas fa-home me-2"></i>
                    <span class="menu-text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.jurusan.index') }}"
                    class="nav-link sidebar-link {{ request()->routeIs('admin.jurusan.*') ? 'active' : 'text-white' }}">
                    <i class="fas fa-layer-group me-2"></i>
                    <span class="menu-text">Data Jurusan Sekolah</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.guru.index') }}"
                    class="nav-link sidebar-link {{ request()->routeIs('admin.guru.*') ? 'active' : 'text-white' }}">
                    <i class="fas fa-chalkboard-teacher me-2"></i>
                    <span class="menu-text">Data Guru</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.kelas.index') }}"
                    class="nav-link sidebar-link {{ request()->routeIs('admin.kelas.*') ? 'active' : 'text-white' }}">
                    <i class="fas fa-door-open me-2"></i>
                    <span class="menu-text">Data Kelas Sekolah</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.ruangan.index') }}"
                    class="nav-link sidebar-link {{ request()->routeIs('admin.ruangan.*') ? 'active' : 'text-white' }}">
                    <i class="fas fa-building me-2"></i>
                    <span class="menu-text">Data Ruangan Sekolah</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.mapel.index') }}"
                    class="nav-link sidebar-link {{ request()->routeIs('admin.mapel.*') ? 'active' : 'text-white' }}">
                    <i class="fas fa-book me-2"></i>
                    <span class="menu-text">Data Mata Pelajaran</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.guru-mapel.index') }}"
                    class="nav-link sidebar-link {{ request()->routeIs('admin.guru-mapel.*') ? 'active' : 'text-white' }}">
                    <i class="fas fa-book-reader me-2"></i>
                    <span class="menu-text">Data Guru Mapel</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.kebutuhan-mapel-kelas.index') }}"
                    class="nav-link sidebar-link {{ request()->routeIs('admin.kebutuhan-mapel-kelas.*') ? 'active' : 'text-white' }}">
                    <i class="fas fa-clipboard-list me-2"></i>
                    <span class="menu-text">Kebutuhan Kelas Mapel</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.penjadwalan.index') }}"
                    class="nav-link sidebar-link {{ request()->routeIs('admin.penjadwalan.*') ? 'active' : 'text-white' }}">
                    <i class="fas fa-calendar-alt me-2"></i>
                    <span class="menu-text">Penjadwalan Kelas</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.jadwal_khusus.index') }}"
                    class="nav-link sidebar-link {{ request()->routeIs('admin.jadwal_khusus.*') ? 'active' : 'text-white' }}">
                    <i class="fas fa-calendar-check me-2"></i>
                    <span class="menu-text">Jadwal Khusus</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.histori.index') }}"
                    class="nav-link sidebar-link {{ request()->routeIs('admin.histori.*') ? 'active' : 'text-white' }}">
                    <i class="fas fa-history me-2"></i>
                    <span class="menu-text">Histori Jadwal</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.profil.index') }}"
                    class="nav-link sidebar-link {{ request()->routeIs('admin.profil.*') ? 'active' : 'text-white' }}">
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

    <!-- Bootstrap JS dan dependensi (Popper.js sudah termasuk di bundle) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script toggle sidebar -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const toggleBtn = document.getElementById('toggleSidebarBtn');
            const sidebar = document.getElementById('sidebar');
            const content = document.getElementById('content');
            const overlay = document.getElementById('sidebarOverlay');

            function isMobile() {
                return window.innerWidth < 768;
            }

            toggleBtn?.addEventListener('click', () => {
                if (isMobile()) {
                    if (document.body.classList.contains('modal-open')) return;
                    const isOpen = sidebar.classList.toggle('active');
                    overlay.classList.toggle('active', isOpen);
                } else {
                    sidebar.classList.toggle('collapsed');
                    content.classList.toggle('expanded');
                }
            });

            overlay?.addEventListener('click', () => {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            });

            document.addEventListener('show.bs.modal', () => {
                if (isMobile()) overlay.classList.remove('active');
            });

            document.addEventListener('hidden.bs.modal', () => {
                if (isMobile() && sidebar.classList.contains('active')) {
                    overlay.classList.add('active');
                }
            });

            // Smooth ganti halaman di mobile
            const sidebarLinks = document.querySelectorAll('.sidebar-link');
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    if (isMobile() && sidebar.classList.contains('active')) {
                        e.preventDefault(); // Stop sejenak
                        const targetHref = this.href;

                        sidebar.classList.remove('active');
                        overlay.classList.remove('active');

                        // Tunggu transisi sidebar selesai
                        sidebar.addEventListener('transitionend', function handler() {
                            window.location.href = targetHref;
                            sidebar.removeEventListener('transitionend', handler);
                        });
                    }
                });
            });
        });
    </script>

    <!-- Tempat untuk inject script tambahan dari halaman lain -->
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
                positionClass: 'toast-top-right'
            };
            toastr.success("{{ addslashes(session('toast_success')) }}");
        });
    </script>
    @endif

</body>

</html>