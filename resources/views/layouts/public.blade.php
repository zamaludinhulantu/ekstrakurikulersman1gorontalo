<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sistem Informasi Ekstrakurikuler SMA Negeri 1 Gorontalo')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="public-body">
<nav class="navbar navbar-expand-lg navbar-public sticky-top">
    <div class="container">
        <a href="{{ route('landing') }}" class="brand-public">
            <strong>Sistem Informasi Ekstrakurikuler</strong>
            <span>SMA Negeri 1 Gorontalo</span>
        </a>
        <button class="btn btn-outline-primary btn-sm d-lg-none ms-auto" type="button" data-bs-toggle="offcanvas" data-bs-target="#publicMobileMenu" aria-label="Buka menu navigasi">
            <i class="bi bi-list"></i>Menu
        </button>
        <div class="public-nav-links ms-auto me-3 d-none d-lg-flex">
            <a class="public-nav-link {{ request()->routeIs('landing') ? 'active' : '' }}" href="{{ route('landing') }}">Beranda</a>
            <a class="public-nav-link" href="{{ route('landing') }}#daftar-ekskul">Ekstrakurikuler</a>
            <a class="public-nav-link {{ request()->routeIs('public.announcements') ? 'active' : '' }}" href="{{ route('public.announcements') }}">Pengumuman</a>
            <a class="public-nav-link {{ request()->routeIs('public.information') ? 'active' : '' }}" href="{{ route('public.information') }}">Alur Pendaftaran</a>
        </div>
        <div class="d-none d-lg-flex align-items-center gap-2">
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-speedometer2"></i>Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-box-arrow-in-right"></i>Masuk</a>
                <a href="{{ route('register') }}" class="btn btn-primary btn-sm"><i class="bi bi-person-plus"></i>Daftar Sekarang</a>
            @endauth
        </div>
    </div>
</nav>

<main>
    @yield('content')
</main>

<footer class="footer-public">
    <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
        <div>SMA Negeri 1 Gorontalo</div>
        <div>Sistem informasi ekstrakurikuler yang rapi, modern, dan responsif.</div>
    </div>
</footer>

<div class="offcanvas offcanvas-start public-mobile-menu" tabindex="-1" id="publicMobileMenu">
    <div class="offcanvas-header">
        <div>
            <h5 class="offcanvas-title mb-1">Menu Utama</h5>
            <div class="small text-muted">Navigasi untuk siswa baru</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
    </div>
    <div class="offcanvas-body">
        <div class="public-mobile-links">
            <a class="public-mobile-link {{ request()->routeIs('landing') ? 'active' : '' }}" href="{{ route('landing') }}">Beranda</a>
            <a class="public-mobile-link" href="{{ route('landing') }}#daftar-ekskul">Daftar Ekstrakurikuler</a>
            <a class="public-mobile-link {{ request()->routeIs('public.announcements') ? 'active' : '' }}" href="{{ route('public.announcements') }}">Pengumuman</a>
            <a class="public-mobile-link {{ request()->routeIs('public.information') ? 'active' : '' }}" href="{{ route('public.information') }}">Alur Pendaftaran</a>
        </div>
        <div class="d-grid gap-2 mt-4">
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-outline-primary"><i class="bi bi-speedometer2"></i>Buka Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-outline-primary"><i class="bi bi-box-arrow-in-right"></i>Masuk</a>
                <a href="{{ route('register') }}" class="btn btn-primary"><i class="bi bi-person-plus"></i>Daftar Sekarang</a>
            @endauth
        </div>
    </div>
</div>

@stack('scripts')
</body>
</html>
