@extends('layouts.app')

@section('page_title', 'Dashboard Pembina Ekstrakurikuler')
@section('page_subtitle', 'Halo, ' . $coach->user->name . ' (NIP: ' . $coach->nip . ')')

@section('content')
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-4 col-xl-3">
            <a href="{{ route('coach.registrations.index') }}" class="text-decoration-none"><div class="card h-100"><div class="card-body stat-card"><span class="stat-icon"><i class="bi bi-hourglass-split"></i></span><p class="label">Pendaftar Baru</p><p class="value">{{ $pendingRegistrations }}</p><div class="trend">Menunggu verifikasi</div></div></div></a>
        </div>
        <div class="col-6 col-md-4 col-xl-3">
            <a href="{{ route('coach.talent-tests.index') }}" class="text-decoration-none"><div class="card h-100"><div class="card-body stat-card"><span class="stat-icon"><i class="bi bi-clipboard2-pulse"></i></span><p class="label">Tes Terdekat</p><p class="value">{{ $upcomingTalentTests }}</p><div class="trend">Jadwal akan datang</div></div></div></a>
        </div>
        <div class="col-6 col-md-4 col-xl-3">
            <a href="{{ route('coach.talent-tests.index') }}" class="text-decoration-none"><div class="card h-100"><div class="card-body stat-card"><span class="stat-icon"><i class="bi bi-pencil-square"></i></span><p class="label">Belum Dinilai</p><p class="value">{{ $pendingTalentAssessments }}</p><div class="trend">Peserta tes menunggu hasil</div></div></div></a>
        </div>
        <div class="col-6 col-md-4 col-xl-3">
            <a href="{{ route('coach.extracurriculars.index') }}" class="text-decoration-none"><div class="card h-100"><div class="card-body stat-card"><span class="stat-icon"><i class="bi bi-people"></i></span><p class="label">Anggota Aktif</p><p class="value">{{ $totalParticipants }}</p><div class="trend">Siswa terlibat</div></div></div></a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header">Aktivitas Kehadiran Terbaru</div>
                <div class="card-body">
                    <div class="info-list">
                        @forelse($recentAttendances as $attendance)
                            <div class="info-item">
                                <div class="title">{{ $attendance->student->user->name ?? '-' }}</div>
                                <div class="small text-muted mt-1">{{ $attendance->schedule->extracurricular->name ?? '-' }} | {{ $attendance->schedule->title ?? '-' }}</div>
                            </div>
                        @empty
                            <div class="empty-state py-3">
                                <p class="mb-0">Belum ada data kehadiran terbaru.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header">Pengumuman Saya</div>
                <div class="card-body">
                    <div class="info-list">
                        @forelse($recentAnnouncements as $announcement)
                            <div class="info-item">
                                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                                    <div>
                                        <div class="title">{{ $announcement->title }}</div>
                                        <div class="small text-muted mt-1">{{ $announcement->extracurricular->name ?? 'Semua ekstrakurikuler binaan' }}</div>
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
