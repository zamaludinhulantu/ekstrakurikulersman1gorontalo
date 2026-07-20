<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sistem Informasi Ekstrakurikuler SMA Negeri 1 Gorontalo')</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/brand/sman1-gorontalo-logo.jpg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="public-body">
@php
    $publicCategories = collect(\App\Models\Extracurricular::categoryDefinitions())
        ->map(fn (array $definition) => [
            'label' => $definition['label'],
            'slug' => $definition['slug'],
        ])
        ->values();
    $currentCategorySlug = request()->routeIs('public.activities.category')
        ? (string) request()->route('slug')
        : null;
@endphp
<nav class="navbar navbar-expand-lg navbar-public sticky-top" data-public-navbar>
    <div class="container">
        <a href="{{ route('landing') }}" class="brand-public">
            <span class="brand-public-mark">
                <img src="{{ asset('images/brand/sman1-gorontalo-logo.jpg') }}" alt="Logo SMAN 1 Gorontalo" loading="eager">
            </span>
            <span class="brand-public-copy">
                <strong>Sistem Informasi Ekstrakurikuler</strong>
                <span>SMA Negeri 1 Gorontalo</span>
            </span>
        </a>
        <button class="btn btn-outline-primary btn-sm d-lg-none ms-auto public-menu-button" type="button" data-bs-toggle="offcanvas" data-bs-target="#publicMobileMenu" aria-label="Buka menu navigasi">
            <i class="bi bi-list"></i>Menu
        </button>
        <div class="public-nav-links mx-auto d-none d-lg-flex">
            <a class="public-nav-link {{ request()->routeIs('landing') ? 'active' : '' }}" href="{{ route('landing') }}">Beranda</a>
            <div class="dropdown">
                <a class="public-nav-link dropdown-toggle {{ request()->routeIs('public.activities.*') ? 'active' : '' }}" href="{{ route('public.activities.index') }}" data-bs-toggle="dropdown" aria-expanded="false">Kategori</a>
                <div class="dropdown-menu public-nav-dropdown">
                    <a class="dropdown-item" href="{{ route('public.activities.index') }}">Semua Kategori</a>
                    @foreach($publicCategories as $publicCategory)
                        <a class="dropdown-item {{ $currentCategorySlug === $publicCategory['slug'] ? 'active' : '' }}" href="{{ route('public.activities.category', $publicCategory['slug']) }}">{{ $publicCategory['label'] }}</a>
                    @endforeach
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('public.activities.all') }}">Semua Pilihan</a>
                </div>
            </div>
            <a class="public-nav-link {{ request()->routeIs('public.announcements') ? 'active' : '' }}" href="{{ route('public.announcements') }}">Pengumuman</a>
            <a class="public-nav-link {{ request()->routeIs('public.information') ? 'active' : '' }}" href="{{ route('public.information') }}">Alur Pendaftaran</a>
        </div>
        <div class="d-none d-lg-flex align-items-center gap-2 public-nav-actions">
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-speedometer2"></i>Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-box-arrow-in-right"></i>Masuk</a>
                <a href="{{ route('register') }}" class="btn btn-primary btn-sm"><i class="bi bi-person-plus"></i>Buat Akun</a>
            @endauth
        </div>
    </div>
</nav>

<main>
    @yield('content')
</main>

<footer class="footer-public">
    <div class="container">
        <div class="footer-public-grid">
            <div class="footer-public-brand">
                <div class="footer-public-logo">
                    <img src="{{ asset('images/brand/sman1-gorontalo-logo.jpg') }}" alt="Logo SMAN 1 Gorontalo" loading="lazy">
                </div>
                <div>
                    <strong>Sistem Informasi Ekstrakurikuler</strong>
                    <div class="footer-public-school">SMA Negeri 1 Gorontalo</div>
                    <p>Portal resmi untuk menjelajahi kategori ekskul seperti OSN, FLS3N, Debat, O2SN, dan kegiatan umum secara lebih ringkas.</p>
                </div>
            </div>
            <div>
                <h3>Alamat & Kontak</h3>
                <ul class="footer-public-list">
                    <li><i class="bi bi-geo-alt"></i>Gorontalo, Indonesia</li>
                    <li><i class="bi bi-telephone"></i>Hubungi admin sekolah untuk informasi pendaftaran</li>
                    <li><i class="bi bi-envelope"></i>Akun siswa digunakan untuk akses pendaftaran online</li>
                </ul>
            </div>
            <div>
                <h3>Menu Cepat</h3>
                <ul class="footer-public-links">
                    <li><a href="{{ route('landing') }}">Beranda</a></li>
                    <li><a href="{{ route('public.activities.index') }}">Semua Kategori</a></li>
                    <li><a href="{{ route('public.activities.all') }}">Semua Pilihan</a></li>
                    <li><a href="{{ route('public.announcements') }}">Pengumuman</a></li>
                    <li><a href="{{ route('public.information') }}">Alur Pendaftaran</a></li>
                </ul>
            </div>
            <div>
                <h3>Kategori</h3>
                <ul class="footer-public-links">
                    @foreach($publicCategories as $publicCategory)
                        <li><a href="{{ route('public.activities.category', $publicCategory['slug']) }}">{{ $publicCategory['label'] }}</a></li>
                    @endforeach
                    @guest
                        <li><a href="{{ route('register') }}">Buat Akun</a></li>
                    @endguest
                </ul>
            </div>
        </div>
        <div class="footer-public-bottom">
            <span>&copy; {{ now()->year }} SMA Negeri 1 Gorontalo. Seluruh hak cipta dilindungi.</span>
            <span>Sistem Informasi Ekstrakurikuler</span>
        </div>
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
            <button class="public-mobile-link public-mobile-link-button {{ request()->routeIs('public.activities.*') ? 'active' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#publicMobileActivitiesMenu" aria-expanded="{{ request()->routeIs('public.activities.*') ? 'true' : 'false' }}">
                <span>Kategori</span>
                <i class="bi bi-chevron-down"></i>
            </button>
            <div class="collapse {{ request()->routeIs('public.activities.*') ? 'show' : '' }}" id="publicMobileActivitiesMenu">
                <div class="public-mobile-submenu">
                    <a class="public-mobile-sublink" href="{{ route('public.activities.index') }}">Semua Kategori</a>
                    @foreach($publicCategories as $publicCategory)
                        <a class="public-mobile-sublink {{ $currentCategorySlug === $publicCategory['slug'] ? 'active' : '' }}" href="{{ route('public.activities.category', $publicCategory['slug']) }}">{{ $publicCategory['label'] }}</a>
                    @endforeach
                    <a class="public-mobile-sublink" href="{{ route('public.activities.all') }}">Semua Pilihan</a>
                </div>
            </div>
            <a class="public-mobile-link {{ request()->routeIs('public.announcements') ? 'active' : '' }}" href="{{ route('public.announcements') }}">Pengumuman</a>
            <a class="public-mobile-link {{ request()->routeIs('public.information') ? 'active' : '' }}" href="{{ route('public.information') }}">Alur Pendaftaran</a>
        </div>
        <div class="d-grid gap-2 mt-4">
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-outline-primary"><i class="bi bi-speedometer2"></i>Buka Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-outline-primary"><i class="bi bi-box-arrow-in-right"></i>Masuk</a>
                <a href="{{ route('register') }}" class="btn btn-primary"><i class="bi bi-person-plus"></i>Buat Akun</a>
            @endauth
        </div>
    </div>
</div>

@stack('scripts')
</body>
</html>
