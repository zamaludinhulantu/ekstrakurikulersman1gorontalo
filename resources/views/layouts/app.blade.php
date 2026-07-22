<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('page_title', 'Dashboard') | {{ config('app.name') }}</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/brand/sman1-gorontalo-logo.jpg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body
    data-idle-logout="true"
    data-idle-timeout-ms="{{ config('session.idle_timeout') * 60 * 1000 }}"
    data-idle-logout-url="{{ route('logout') }}"
    data-idle-redirect-url="{{ route('login') }}"
    data-idle-keep-alive-url="{{ route('session.keep-alive') }}"
>
@php
    $authUser = auth()->user();
    $roleLabel = match($authUser->role ?? '') {
        \App\Models\User::ROLE_ADMIN => 'Admin / Kesiswaan',
        \App\Models\User::ROLE_STUDENT => 'Siswa',
        \App\Models\User::ROLE_COACH => 'Pembina',
        \App\Models\User::ROLE_PRINCIPAL => 'Kepala Sekolah',
        default => 'Pengguna',
    };

    $menuGroups = [];
    if ($authUser?->role === \App\Models\User::ROLE_ADMIN) {
        $menuGroups = [
            [
                'label' => 'Utama',
                'items' => [
                    ['label' => 'Dashboard', 'route' => route('admin.dashboard'), 'active' => 'admin.dashboard', 'icon' => 'bi-speedometer2', 'caption' => 'Ringkasan sistem'],
                    ['label' => 'Pengguna', 'route' => route('admin.users.index'), 'active' => 'admin.users.*', 'icon' => 'bi-people', 'caption' => 'Akun semua role'],
                ],
            ],
            [
                'label' => 'Ekstrakurikuler',
                'items' => [
                    ['label' => 'Data Ekskul', 'route' => route('admin.extracurriculars.index'), 'active' => 'admin.extracurriculars.*', 'icon' => 'bi-grid-1x2', 'caption' => 'Unit kegiatan'],
                    ['label' => 'Kategori Ekskul', 'route' => route('admin.extracurricular-categories.index'), 'active' => 'admin.extracurricular-categories.*', 'icon' => 'bi-collection', 'caption' => 'Kartu kategori publik'],
                    ['label' => 'Pendaftar', 'route' => route('admin.registrations.index'), 'active' => 'admin.registrations.*', 'icon' => 'bi-clipboard-check', 'caption' => 'Verifikasi siswa'],
                    ['label' => 'Anggota', 'route' => route('admin.students.index'), 'active' => 'admin.students.*', 'icon' => 'bi-person-badge', 'caption' => 'Data peserta'],
                    ['label' => 'Pembina', 'route' => route('admin.coaches.index'), 'active' => 'admin.coaches.*', 'icon' => 'bi-person-workspace', 'caption' => 'Data pembina'],
                    ['label' => 'Pengumuman', 'route' => route('admin.announcements.index'), 'active' => 'admin.announcements.*', 'icon' => 'bi-megaphone', 'caption' => 'Info untuk siswa'],
                ],
            ],
            [
                'label' => 'Kegiatan & Laporan',
                'items' => [
                    ['label' => 'Tes Bakat', 'route' => route('admin.talent-tests.index'), 'active' => 'admin.talent-tests.*', 'icon' => 'bi-clipboard2-pulse', 'caption' => 'Monitoring tes'],
                    ['label' => 'Jadwal', 'route' => route('admin.schedules.index'), 'active' => 'admin.schedules.*', 'icon' => 'bi-calendar3', 'caption' => 'Agenda kegiatan'],
                    ['label' => 'Prestasi', 'route' => route('admin.assessments.index'), 'active' => 'admin.assessments.*', 'icon' => 'bi-award', 'caption' => 'Prestasi dan nilai'],
                    ['label' => 'Laporan Peserta', 'route' => route('admin.participants.index'), 'active' => 'admin.participants.*', 'icon' => 'bi-card-checklist', 'caption' => 'Rekap peserta'],
                    ['label' => 'Laporan Presensi', 'route' => route('admin.attendances.index'), 'active' => 'admin.attendances.*', 'icon' => 'bi-check2-square', 'caption' => 'Kehadiran peserta'],
                ],
            ],
        ];
    } elseif ($authUser?->role === \App\Models\User::ROLE_STUDENT) {
        $menuGroups = [
            [
                'label' => 'Utama',
                'items' => [
                    ['label' => 'Dashboard', 'route' => route('student.dashboard'), 'active' => 'student.dashboard', 'icon' => 'bi-speedometer2', 'caption' => 'Ringkasan pribadi'],
                    ['label' => 'Ekstrakurikuler', 'route' => route('student.extracurriculars.index'), 'active' => 'student.extracurriculars.*', 'icon' => 'bi-grid-1x2', 'caption' => 'Katalog kegiatan'],
                    ['label' => 'Pendaftaran Saya', 'route' => route('student.registrations.index'), 'active' => 'student.registrations.*', 'icon' => 'bi-clipboard-check', 'caption' => 'Status pengajuan'],
                ],
            ],
            [
                'label' => 'Kegiatan',
                'items' => [
                    ['label' => 'Jadwal Ekskul', 'route' => route('student.schedules.index'), 'active' => 'student.schedules.*', 'icon' => 'bi-calendar3', 'caption' => 'Agenda latihan'],
                    ['label' => 'Tes Bakat', 'route' => route('student.talent-tests.index'), 'active' => 'student.talent-tests.*', 'icon' => 'bi-clipboard2-pulse', 'caption' => 'Jadwal dan hasil tes'],
                    ['label' => 'Presensi', 'route' => route('student.attendances.index'), 'active' => 'student.attendances.*', 'icon' => 'bi-check2-square', 'caption' => 'Riwayat hadir'],
                    ['label' => 'Penilaian', 'route' => route('student.assessments.index'), 'active' => 'student.assessments.*', 'icon' => 'bi-award', 'caption' => 'Nilai dan prestasi'],
                ],
            ],
        ];
    } elseif ($authUser?->role === \App\Models\User::ROLE_COACH) {
        $menuGroups = [
            [
                'label' => 'Utama',
                'items' => [
                    ['label' => 'Dashboard', 'route' => route('coach.dashboard'), 'active' => 'coach.dashboard', 'icon' => 'bi-speedometer2', 'caption' => 'Ringkasan pembina'],
                    ['label' => 'Ekstrakurikuler', 'route' => route('coach.extracurriculars.index'), 'active' => 'coach.extracurriculars.*', 'icon' => 'bi-grid-1x2', 'caption' => 'Daftar binaan'],
                    ['label' => 'Pendaftar', 'route' => route('coach.registrations.index'), 'active' => 'coach.registrations.*', 'icon' => 'bi-clipboard-check', 'caption' => 'Verifikasi siswa'],
                ],
            ],
            [
                'label' => 'Kegiatan',
                'items' => [
                    ['label' => 'Tes Bakat', 'route' => route('coach.talent-tests.index'), 'active' => ['coach.talent-tests.*', 'coach.talent-test-aspects.*'], 'icon' => 'bi-clipboard2-pulse', 'caption' => 'Jadwal dan hasil tes'],
                    ['label' => 'Jadwal', 'route' => route('coach.schedules.index'), 'active' => 'coach.schedules.*', 'icon' => 'bi-calendar3', 'caption' => 'Kelola agenda'],
                    ['label' => 'Presensi', 'route' => route('coach.attendances.index'), 'active' => 'coach.attendances.*', 'icon' => 'bi-check2-square', 'caption' => 'Kelola hadir'],
                    ['label' => 'Penilaian', 'route' => route('coach.assessments.index'), 'active' => 'coach.assessments.*', 'icon' => 'bi-award', 'caption' => 'Catatan siswa'],
                    ['label' => 'Pengumuman', 'route' => route('coach.announcements.index'), 'active' => 'coach.announcements.*', 'icon' => 'bi-megaphone', 'caption' => 'Info untuk siswa'],
                ],
            ],
        ];
    } elseif ($authUser?->role === \App\Models\User::ROLE_PRINCIPAL) {
        $menuGroups = [[
            'label' => 'Utama',
            'items' => [
                ['label' => 'Dashboard', 'route' => route('principal.dashboard'), 'active' => 'principal.dashboard', 'icon' => 'bi-speedometer2', 'caption' => 'Ringkasan sekolah'],
                ['label' => 'Presensi', 'route' => route('principal.attendances.index'), 'active' => 'principal.attendances.*', 'icon' => 'bi-check2-square', 'caption' => 'Rekap kehadiran'],
            ],
        ]];
    }

    $routeTitleMap = [
        'admin.dashboard' => 'Dashboard Admin',
        'admin.users.*' => 'Manajemen Pengguna',
        'admin.students.*' => 'Manajemen Siswa',
        'admin.coaches.*' => 'Manajemen Pembina',
        'admin.extracurriculars.*' => 'Manajemen Ekstrakurikuler',
        'admin.extracurricular-categories.*' => 'Kategori Ekskul',
        'admin.registrations.*' => 'Verifikasi Pendaftaran',
        'admin.talent-tests.*' => 'Monitoring Tes Bakat',
        'admin.participants.*' => 'Laporan Peserta',
        'admin.schedules.*' => 'Laporan Jadwal',
        'admin.attendances.*' => 'Laporan Presensi',
        'admin.assessments.*' => 'Kelola Prestasi dan Penilaian',
        'admin.announcements.*' => 'Pengumuman Admin',
        'student.dashboard' => 'Dashboard Siswa',
        'student.extracurriculars.*' => 'Informasi Ekstrakurikuler',
        'student.registrations.*' => 'Status Pendaftaran',
        'student.schedules.*' => 'Jadwal Kegiatan',
        'student.talent-tests.*' => 'Tes Bakat Saya',
        'student.attendances.*' => 'Riwayat Presensi',
        'student.assessments.*' => 'Prestasi dan Penilaian',
        'coach.dashboard' => 'Dashboard Pembina',
        'coach.announcements.*' => 'Pengumuman Pembina',
        'coach.extracurriculars.*' => 'Ekstrakurikuler Binaan',
        'coach.registrations.*' => 'Pendaftar Ekstrakurikuler',
        'coach.talent-tests.*' => 'Kelola Tes Bakat',
        'coach.talent-test-aspects.*' => 'Aspek Tes Bakat',
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
    $routeName = request()->route()?->getName();
    $breadcrumbItems = [
        ['label' => 'Dashboard', 'route' => route('dashboard')],
    ];
    if ($authUser?->role === \App\Models\User::ROLE_ADMIN) {
        $breadcrumbItems[0]['route'] = route('admin.dashboard');
    } elseif ($authUser?->role === \App\Models\User::ROLE_COACH) {
        $breadcrumbItems[0]['route'] = route('coach.dashboard');
    } elseif ($authUser?->role === \App\Models\User::ROLE_STUDENT) {
        $breadcrumbItems[0]['route'] = route('student.dashboard');
    } elseif ($authUser?->role === \App\Models\User::ROLE_PRINCIPAL) {
        $breadcrumbItems[0]['route'] = route('principal.dashboard');
    }

    if ($routeName && !str_ends_with($routeName, 'dashboard') && !str_starts_with($routeName, 'dashboard')) {
        $breadcrumbItems[] = ['label' => $pageTitle, 'route' => null];
    }
@endphp

<div class="app-shell">
    <aside class="sidebar d-none d-lg-block">
        <div class="brand-box">
            <span class="brand-mark">
                <img src="{{ asset('images/brand/sman1-gorontalo-logo.jpg') }}" alt="Logo SMAN 1 Gorontalo" loading="eager">
            </span>
            <a class="brand-title" href="{{ route('dashboard') }}">Sistem Informasi Ekstrakurikuler</a>
            <div class="brand-subtitle">SMA Negeri 1 Gorontalo</div>
            <span class="role-badge"><i class="bi bi-shield-check"></i>{{ $roleLabel }}</span>
        </div>
        @include('partials.sidebar-menu')
    </aside>

    <div class="app-main">
        <header class="app-topbar">
            <div class="app-topbar__row">
                <div class="app-topbar__identity">
                    <button class="btn btn-outline-primary btn-sm d-lg-none app-topbar__menu-button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" type="button" aria-label="Buka menu navigasi">
                        <i class="bi bi-list"></i>
                    </button>
                    <div>
                        <nav class="page-breadcrumb" aria-label="Breadcrumb">
                            @foreach($breadcrumbItems as $breadcrumb)
                                @if($breadcrumb['route'])
                                    <a href="{{ $breadcrumb['route'] }}">{{ $breadcrumb['label'] }}</a>
                                @else
                                    <span aria-current="page">{{ $breadcrumb['label'] }}</span>
                                @endif
                            @endforeach
                        </nav>
                        <p class="topbar-title">{{ $pageTitle }}</p>
                        <p class="topbar-subtitle">{{ $pageSubtitle ?: 'Panel kerja terintegrasi untuk mengelola aktivitas ekstrakurikuler sekolah.' }}</p>
                    </div>
                </div>
                <div class="topbar-meta topbar-meta-desktop">
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
            <div class="topbar-mobile-account dropdown d-md-none">
                <button class="btn btn-outline-primary btn-sm topbar-mobile-account__toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Buka menu akun">
                    <span class="profile-chip-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($authUser->name ?? 'U', 0, 1)) }}</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end topbar-account-menu">
                    <div class="topbar-account-menu__header">
                        <strong>{{ $authUser->name ?? 'Pengguna' }}</strong>
                        <span>{{ $roleLabel }}</span>
                        <small>{{ now()->translatedFormat('d F Y') }} · {{ now()->translatedFormat('l') }}</small>
                    </div>
                    <a href="{{ route('profile.edit') }}" class="dropdown-item">
                        <i class="bi bi-person-circle"></i>Profil
                    </a>
                    <form action="{{ route('logout') }}" method="post">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger" data-loading-text="Keluar...">
                            <i class="bi bi-box-arrow-right"></i>Logout
                        </button>
                    </form>
                </div>
            </div>
            <div class="page-actions page-actions-desktop mt-3">
                <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary btn-sm" aria-label="Buka halaman profil"><i class="bi bi-person-circle"></i>Profil</a>
                <form action="{{ route('logout') }}" method="post" class="d-inline-flex">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm" data-loading-text="Keluar..."><i class="bi bi-box-arrow-right"></i>Logout</button>
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
            <span class="brand-mark">
                <img src="{{ asset('images/brand/sman1-gorontalo-logo.jpg') }}" alt="Logo SMAN 1 Gorontalo" loading="lazy">
            </span>
            <a class="brand-title" href="{{ route('dashboard') }}">Sistem Informasi Ekstrakurikuler</a>
            <div class="brand-subtitle">SMA Negeri 1 Gorontalo</div>
            <span class="role-badge"><i class="bi bi-shield-check"></i>{{ $roleLabel }}</span>
        </div>
        <span class="sidebar-group-label">Navigasi</span>
        @include('partials.sidebar-menu')
    </div>
</div>

@include('partials.participant-profile-modal')

@if(session('success_modal'))
    <div class="modal fade" id="successStatusModal" tabindex="-1" aria-labelledby="successStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content success-status-modal">
                <div class="modal-body text-center p-4 p-md-5">
                    <div class="success-status-modal__icon">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                    <h2 class="success-status-modal__title" id="successStatusModalLabel">{{ session('success_modal.title') }}</h2>
                    <p class="success-status-modal__message mb-0">{{ session('success_modal.message') }}</p>
                </div>
                <div class="modal-footer border-0 justify-content-center px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-primary px-4" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endif

<div class="idle-warning-overlay" id="idleLogoutWarningModal" hidden>
    <div class="idle-warning-modal" role="dialog" aria-modal="true" aria-labelledby="idleLogoutWarningModalLabel">
        <div class="idle-warning-modal__body text-center p-4 p-md-5">
            <div class="idle-warning-modal__icon">
                <i class="bi bi-clock-history"></i>
            </div>
            <h2 class="idle-warning-modal__title" id="idleLogoutWarningModalLabel">Sesi akan berakhir</h2>
            <p class="idle-warning-modal__message mb-3">
                Tidak ada aktivitas terdeteksi. Anda akan logout otomatis dalam
                <strong id="idleLogoutCountdown">0 detik</strong>.
            </p>
            <p class="text-muted small mb-0">Klik tombol di bawah untuk tetap masuk dan melanjutkan pekerjaan Anda.</p>
        </div>
        <div class="idle-warning-modal__actions">
            <button type="button" class="btn btn-outline-secondary" id="idleLogoutDismiss">Tutup</button>
            <button type="button" class="btn btn-primary px-4" id="idleLogoutStaySignedIn">Tetap Masuk</button>
        </div>
    </div>
</div>

@stack('scripts')
<script>
    (function () {
        const statusClassMap = {
            pending: 'badge-status-warning',
            menunggu: 'badge-status-warning',
            waiting_test: 'badge-status-warning',
            'menunggu tes': 'badge-status-warning',
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

        const successStatusModal = document.getElementById('successStatusModal');
        if (successStatusModal && window.bootstrap?.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(successStatusModal).show();
        }
    })();
</script>
</body>
</html>
