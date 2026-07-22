@extends('layouts.app')

@section('page_title', $dashboardTitle ?? 'Dashboard Admin/Kesiswaan')
@section('page_subtitle', $dashboardSubtitle ?? 'Pantau pendaftaran, data ekskul, dan aktivitas utama dari satu dashboard')

@section('content')
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-4 col-xl-3">
            <a href="{{ route('admin.extracurriculars.index') }}" class="text-decoration-none"><div class="card h-100"><div class="card-body stat-card"><span class="stat-icon"><i class="bi bi-grid-1x2"></i></span><p class="label">Ekstrakurikuler</p><p class="value">{{ $totalExtracurriculars }}</p><div class="trend">Unit kegiatan aktif</div></div></div></a>
        </div>
        <div class="col-6 col-md-4 col-xl-3">
            <a href="{{ route('admin.students.index') }}" class="text-decoration-none"><div class="card h-100"><div class="card-body stat-card"><span class="stat-icon"><i class="bi bi-people"></i></span><p class="label">Siswa</p><p class="value">{{ $totalStudents }}</p><div class="trend">Peserta terdaftar</div></div></div></a>
        </div>
        <div class="col-6 col-md-4 col-xl-3">
            <a href="{{ route('admin.registrations.index') }}" class="text-decoration-none"><div class="card h-100"><div class="card-body stat-card"><span class="stat-icon"><i class="bi bi-hourglass-split"></i></span><p class="label">Menunggu Verifikasi</p><p class="value">{{ $pendingRegistrations }}</p><div class="trend">Perlu tindak lanjut</div></div></div></a>
        </div>
        <div class="col-6 col-md-4 col-xl-3">
            <a href="{{ route('admin.talent-tests.index') }}" class="text-decoration-none"><div class="card h-100"><div class="card-body stat-card"><span class="stat-icon"><i class="bi bi-clipboard2-pulse"></i></span><p class="label">Tes Akan Datang</p><p class="value">{{ $upcomingTalentTests }}</p><div class="trend">Jadwal tes aktif</div></div></div></a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header">Aktivitas Terbaru</div>
                <div class="card-body">
                    <div class="info-list">
                        @forelse($recentRegistrations as $registration)
                            <div class="info-item">
                                <div class="d-flex justify-content-between gap-2">
                                    <div>
                                        <div class="title">{{ $registration->student->user->name ?? '-' }}</div>
                                        <div class="small text-muted mt-1">{{ $registration->extracurricular->name ?? '-' }}</div>
                                    </div>
                                    <span class="badge" data-status="{{ $registration->status }}">{{ $registration->status }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state py-3">
                                <p class="mb-0">Belum ada aktivitas pendaftaran terbaru.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header">Tes Bakat Terbaru</div>
                <div class="card-body">
                    <div class="info-list">
                        @forelse($recentTalentTests as $test)
                            <div class="info-item">
                                <div class="title">{{ $test->title }}</div>
                                <div class="small text-muted mt-1">{{ $test->extracurricular->name ?? '-' }} | {{ optional($test->activity_date)->format('d-m-Y') }}</div>
                            </div>
                        @empty
                            <div class="empty-state py-3">
                                <p class="mb-0">Belum ada jadwal tes bakat.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-header">Pengumuman Terbaru</div>
                <div class="card-body">
                    <div class="info-list">
                        @forelse($recentAnnouncements as $announcement)
                            <div class="info-item">
                                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                                    <div>
                                        <div class="title">{{ $announcement->title }}</div>
                                        <div class="small text-muted mt-1">{{ $announcement->extracurricular->name ?? 'Semua ekstrakurikuler' }} | {{ $announcement->publisher->name ?? '-' }}</div>
                                    </div>
                                    <span class="small text-muted">{{ optional($announcement->created_at)->format('d-m-Y') }}</span>
                                </div>
                                <div class="small mt-2">{{ \Illuminate\Support\Str::limit($announcement->content, 140) }}</div>
                            </div>
                        @empty
                            <div class="empty-state py-3">
                                <div class="icon"><i class="bi bi-megaphone"></i></div>
                                <p class="mb-0">Belum ada pengumuman.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
