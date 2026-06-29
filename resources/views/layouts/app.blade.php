<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('page_title', 'Dashboard') | {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body>
@php
    $authUser = auth()->user();
    $roleLabel = match($authUser->role ?? '') {
        \App\Models\User::ROLE_ADMIN => 'Admin / Kesiswaan',
        \App\Models\User::ROLE_STUDENT => 'Siswa',
        \App\Models\User::ROLE_COACH => 'Pembina',
        \App\Models\User::ROLE_PRINCIPAL => 'Kepala Sekolah',
        default => 'Pengguna',
    };

    $menuItems = [];
    if ($authUser?->role === \App\Models\User::ROLE_ADMIN) {
        $menuItems = [
            ['label' => 'Dashboard', 'route' => route('admin.dashboard'), 'active' => 'admin.dashboard', 'icon' => 'bi-speedometer2', 'caption' => 'Ringkasan sistem'],
            ['label' => 'Data Ekskul', 'route' => route('admin.extracurriculars.index'), 'active' => 'admin.extracurriculars.*', 'icon' => 'bi-grid-1x2', 'caption' => 'Unit kegiatan'],
            ['label' => 'Pendaftar', 'route' => route('admin.registrations.index'), 'active' => 'admin.registrations.*', 'icon' => 'bi-clipboard-check', 'caption' => 'Verifikasi siswa'],
            ['label' => 'Anggota', 'route' => route('admin.students.index'), 'active' => 'admin.students.*', 'icon' => 'bi-person-badge', 'caption' => 'Data peserta'],
            ['label' => 'Pembina', 'route' => route('admin.coaches.index'), 'active' => 'admin.coaches.*', 'icon' => 'bi-person-workspace', 'caption' => 'Data pembina'],
            ['label' => 'Jadwal', 'route' => route('admin.schedules.index'), 'active' => 'admin.schedules.*', 'icon' => 'bi-calendar3', 'caption' => 'Agenda kegiatan'],
            ['label' => 'Prestasi', 'route' => route('admin.assessments.index'), 'active' => 'admin.assessments.*', 'icon' => 'bi-award', 'caption' => 'Prestasi dan nilai'],
            ['label' => 'Pengumuman', 'route' => route('admin.announcements.index'), 'active' => 'admin.announcements.*', 'icon' => 'bi-megaphone', 'caption' => 'Info untuk siswa'],
            ['label' => 'Pengguna', 'route' => route('admin.users.index'), 'active' => 'admin.users.*', 'icon' => 'bi-people', 'caption' => 'Akun semua role'],
            ['label' => 'Laporan Peserta', 'route' => route('admin.participants.index'), 'active' => 'admin.participants.*', 'icon' => 'bi-card-checklist', 'caption' => 'Rekap peserta'],
            ['label' => 'Laporan Presensi', 'route' => route('admin.attendances.index'), 'active' => 'admin.attendances.*', 'icon' => 'bi-check2-square', 'caption' => 'Kehadiran peserta'],
        ];
    } elseif ($authUser?->role === \App\Models\User::ROLE_STUDENT) {
        $menuItems = [
            ['label' => 'Dashboard', 'route' => route('student.dashboard'), 'active' => 'student.dashboard', 'icon' => 'bi-speedometer2', 'caption' => 'Ringkasan pribadi'],
            ['label' => 'Ekstrakurikuler', 'route' => route('student.extracurriculars.index'), 'active' => 'student.extracurriculars.*', 'icon' => 'bi-grid-1x2', 'caption' => 'Katalog kegiatan'],
            ['label' => 'Pendaftaran Saya', 'route' => route('student.registrations.index'), 'active' => 'student.registrations.*', 'icon' => 'bi-clipboard-check', 'caption' => 'Status pengajuan'],
            ['label' => 'Jadwal Ekskul', 'route' => route('student.schedules.index'), 'active' => 'student.schedules.*', 'icon' => 'bi-calendar3', 'caption' => 'Agenda kegiatan'],
            ['label' => 'Presensi', 'route' => route('student.attendances.index'), 'active' => 'student.attendances.*', 'icon' => 'bi-check2-square', 'caption' => 'Riwayat hadir'],
            ['label' => 'Prestasi/Penilaian', 'route' => route('student.assessments.index'), 'active' => 'student.assessments.*', 'icon' => 'bi-award', 'caption' => 'Nilai dan prestasi'],
        ];
    } elseif ($authUser?->role === \App\Models\User::ROLE_COACH) {
        $menuItems = [
            ['label' => 'Dashboard', 'route' => route('coach.dashboard'), 'active' => 'coach.dashboard', 'icon' => 'bi-speedometer2', 'caption' => 'Ringkasan pembina'],
            ['label' => 'Ekstrakurikuler', 'route' => route('coach.extracurriculars.index'), 'active' => 'coach.extracurriculars.*', 'icon' => 'bi-grid-1x2', 'caption' => 'Daftar binaan'],
            ['label' => 'Pendaftar', 'route' => route('coach.registrations.index'), 'active' => 'coach.registrations.*', 'icon' => 'bi-clipboard-check', 'caption' => 'Verifikasi siswa'],
            ['label' => 'Pengumuman', 'route' => route('coach.announcements.index'), 'active' => 'coach.announcements.*', 'icon' => 'bi-megaphone', 'caption' => 'Info untuk siswa'],
            ['label' => 'Jadwal', 'route' => route('coach.schedules.index'), 'active' => 'coach.schedules.*', 'icon' => 'bi-calendar3', 'caption' => 'Kelola agenda'],
            ['label' => 'Presensi', 'route' => route('coach.attendances.index'), 'active' => 'coach.attendances.*', 'icon' => 'bi-check2-square', 'caption' => 'Kelola hadir'],
            ['label' => 'Prestasi/Penilaian', 'route' => route('coach.assessments.index'), 'active' => 'coach.assessments.*', 'icon' => 'bi-award', 'caption' => 'Catatan siswa'],
        ];
    } elseif ($authUser?->role === \App\Models\User::ROLE_PRINCIPAL) {
        $menuItems = [
            ['label' => 'Dashboard', 'route' => route('principal.dashboard'), 'active' => 'principal.dashboard', 'icon' => 'bi-speedometer2', 'caption' => 'Ringkasan sekolah'],
            ['label' => 'Presensi', 'route' => route('principal.attendances.index'), 'active' => 'principal.attendances.*', 'icon' => 'bi-check2-square', 'caption' => 'Rekap kehadiran'],
        ];
    }

    $routeTitleMap = [
        'admin.dashboard' => 'Dashboard Admin',
        'admin.users.*' => 'Manajemen Pengguna',
        'admin.students.*' => 'Manajemen Siswa',
        'admin.coaches.*' => 'Manajemen Pembina',
        'admin.extracurriculars.*' => 'Manajemen Ekstrakurikuler',
        'admin.registrations.*' => 'Verifikasi Pendaftaran',
        'admin.participants.*' => 'Laporan Peserta',
        'admin.schedules.*' => 'Laporan Jadwal',
        'admin.attendances.*' => 'Laporan Presensi',
        'admin.assessments.*' => 'Laporan Prestasi dan Penilaian',
        'admin.announcements.*' => 'Pengumuman Admin',
        'student.dashboard' => 'Dashboard Siswa',
        'student.extracurriculars.*' => 'Informasi Ekstrakurikuler',
        'student.registrations.*' => 'Status Pendaftaran',
        'student.schedules.*' => 'Jadwal Kegiatan',
        'student.attendances.*' => 'Riwayat Presensi',
        'student.assessments.*' => 'Prestasi dan Penilaian',
        'coach.dashboard' => 'Dashboard Pembina',
        'coach.announcements.*' => 'Pengumuman Pembina',
        'coach.extracurriculars.*' => 'Ekstrakurikuler Binaan',
        'coach.registrations.*' => 'Pendaftar Ekstrakurikuler',
        'coach.schedules.*' => 'Kelola Jadwal',
        'coach.attendances.*' => 'Kelola Presensi',
        'coach.assessments.*' => 'Kelola Prestasi dan Penilaian',
        'principal.dashboard' => 'Dashboard Kepala Sekolah',
        'principal.attendances.*' => 'Presensi Kepala Sekolah',
        'profile.*' => 'Profil Pengguna',
    ];

    $autoPageTitle = 'Dashboard';
    foreach ($routeTitleMap as $pattern => $title) {
        if (request()->routeIs($pattern)) {
            $autoPageTitle = $title;
            break;
        }
    }

    $pageTitle = trim($__env->yieldContent('page_title')) ?: $autoPageTitle;
    $pageSubtitle = trim($__env->yieldContent('page_subtitle'));
@endphp

<div class="app-shell">
    <aside class="sidebar d-none d-lg-block">
        <div class="brand-box">
            <span class="brand-mark"><i class="bi bi-mortarboard-fill"></i></span>
            <a class="brand-title" href="{{ route('dashboard') }}">Sistem Informasi Ekstrakurikuler</a>
            <div class="brand-subtitle">SMA Negeri 1 Gorontalo</div>
            <span class="role-badge"><i class="bi bi-shield-check"></i>{{ $roleLabel }}</span>
        </div>
        <span class="sidebar-group-label">Navigasi</span>
        @include('partials.sidebar-menu')
    </aside>

    <div class="app-main">
        <header class="app-topbar">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div class="d-flex align-items-start gap-2">
                    <button class="btn btn-outline-primary btn-sm d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" type="button" aria-label="Toggle Menu">
                        <i class="bi bi-list"></i>Menu
                    </button>
                    <div>
                        <p class="topbar-title">{{ $pageTitle }}</p>
                        <p class="topbar-subtitle">{{ $pageSubtitle ?: 'Panel kerja terintegrasi untuk mengelola aktivitas ekstrakurikuler sekolah.' }}</p>
                    </div>
                </div>
                <div class="topbar-meta">
                    <div class="date-pill">
                        <i class="bi bi-calendar3 text-primary"></i>
                        <div>
                            <strong>{{ now()->translatedFormat('d F Y') }}</strong>
                            <span>{{ now()->translatedFormat('l') }}</span>
                        </div>
                    </div>
                    <div class="profile-chip">
                        <span class="profile-chip-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($authUser->name ?? 'U', 0, 1)) }}</span>
                        <div class="profile-chip-text">
                            <strong>{{ $authUser->name ?? 'Pengguna' }}</strong>
                            <span>{{ $roleLabel }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="page-actions mt-3">
                <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-person-circle"></i>Profil</a>
                <form action="{{ route('logout') }}" method="post" class="d-inline-flex">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-box-arrow-right"></i>Logout</button>
                </form>
            </div>
        </header>

        <main class="app-content">
            <div class="content-surface">
                @include('partials.alerts')
                @yield('content')
                <div class="app-footer">Sistem Informasi Ekstrakurikuler SMA Negeri 1 Gorontalo</div>
            </div>
        </main>
    </div>
</div>

<div class="offcanvas offcanvas-start text-bg-dark d-lg-none" tabindex="-1" id="mobileSidebar">
    <div class="offcanvas-header border-bottom border-secondary-subtle">
        <div>
            <h5 class="offcanvas-title mb-1">Menu {{ $roleLabel }}</h5>
            <div class="small text-white-50">Navigasi modul utama</div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <div class="brand-box">
            <span class="brand-mark"><i class="bi bi-mortarboard-fill"></i></span>
            <a class="brand-title" href="{{ route('dashboard') }}">Sistem Informasi Ekstrakurikuler</a>
            <div class="brand-subtitle">SMA Negeri 1 Gorontalo</div>
            <span class="role-badge"><i class="bi bi-shield-check"></i>{{ $roleLabel }}</span>
        </div>
        <span class="sidebar-group-label">Navigasi</span>
        @include('partials.sidebar-menu')
    </div>
</div>

@stack('scripts')
<script>
    (function () {
        const statusClassMap = {
            pending: 'badge-status-warning',
            menunggu: 'badge-status-warning',
            approved: 'badge-status-success',
            diterima: 'badge-status-success',
            active: 'badge-status-success',
            aktif: 'badge-status-success',
            present: 'badge-status-success',
            hadir: 'badge-status-success',
            rejected: 'badge-status-danger',
            ditolak: 'badge-status-danger',
            absent: 'badge-status-danger',
            alpa: 'badge-status-danger',
            nonaktif: 'badge-status-secondary',
            'tidak aktif': 'badge-status-secondary',
            inactive: 'badge-status-secondary',
            sick: 'badge-status-warning',
            sakit: 'badge-status-warning',
            permission: 'badge-status-warning',
            izin: 'badge-status-warning',
        };

        const cleanClasses = ['text-bg-secondary', 'text-bg-success', 'text-bg-danger', 'text-bg-warning', 'text-bg-info'];

        const resolveStatus = function (text) {
            const raw = String(text || '').trim().toLowerCase();
            if (!raw) {
                return null;
            }

            if (statusClassMap[raw]) {
                return statusClassMap[raw];
            }

            const normalized = raw.replace(/^status\s*:\s*/i, '').trim();
            return statusClassMap[normalized] || null;
        };

        document.querySelectorAll('[data-status], .badge, .status-badge').forEach(function (el) {
            const resolvedClass = resolveStatus(el.dataset.status || el.textContent);
            if (!resolvedClass) {
                return;
            }

            el.classList.add('status-badge');
            cleanClasses.forEach(function (cls) {
                el.classList.remove(cls);
            });
            Object.values(statusClassMap).forEach(function (cls) {
                el.classList.remove(cls);
            });
            el.classList.add(resolvedClass);
        });
    })();
</script>
</body>
</html>
