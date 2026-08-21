<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel Petugas')</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            font-family: system-ui, -apple-system, sans-serif; 
            background-color: #f8f9fa; 
        }
        .sidebar { 
            width: 250px; 
            min-height: 100vh; 
            background-color: #121929; 
            color: #fff; 
        }
        .sidebar .nav-link { 
            color: #9a9ea9; 
            padding: 12px 20px; 
            font-size: 0.95rem; 
            border-radius: 6px;
        }
        .sidebar .nav-link:hover {
            color: #fff;
            background-color: #1e293b;
        }
        .sidebar .nav-link.active { 
            color: #ffffff !important; 
            background-color: #2c3f58 !important; 
            font-weight: 600;
        }
        .sidebar-heading { 
            font-size: 1.1rem; 
            font-weight: bold; 
            letter-spacing: 1px; 
            padding: 20px 10px; 
        }
        .top-navbar { 
            background-color: #fff; 
            border-bottom: 1px solid #e2e8f0; 
            padding: 15px 30px; 
        }
        .main-content { 
            flex: 1; 
        }
        .sidebar-footer {
            color: #ffffff !important;
            opacity: 0.9;
        }
        @media print {
            .sidebar, .top-navbar, .no-print {
                display: none !important;
            }
            .main-content {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
        }
    </style>
</head>
<body>

<div class="d-flex">
    <!-- Sidebar Panel Petugas -->
    <div class="sidebar d-flex flex-column justify-content-between p-3">
        <div>
            <div class="sidebar-heading text-white mb-3">PANEL PETUGAS</div>
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item mb-1">
                    <a href="{{ route('petugas.peminjaman.index') }}" class="nav-link {{ request()->routeIs('petugas.peminjaman.*') ? 'active' : '' }}">
                        Kelola Peminjaman
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="{{ route('petugas.pengembalian.index') }}" class="nav-link {{ request()->routeIs('petugas.pengembalian.*') ? 'active' : '' }}">
                        Kelola Pengembalian
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="{{ Route::has('petugas.laporan.index') ? route('petugas.laporan.index') : '#' }}" class="nav-link {{ request()->routeIs('petugas.laporan.*') ? 'active' : '' }}">
                        Cetak Laporan
                    </a>
                </li>
            </ul>
        </div>

        <div class="border-top border-secondary pt-3 px-2 sidebar-footer small">
            Logged in as: <strong class="text-white">{{ Auth::user()->name ?? 'Petugas' }}</strong>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="main-content d-flex flex-column">
        <div class="top-navbar d-flex justify-content-between align-items-center">
            <h5 class="m-0 fw-bold text-secondary">Dashboard Petugas</h5>
            <form action="{{ route('logout') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm px-3">Logout</button>
            </form>
        </div>

        <div class="p-4">
            @yield('content')
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>