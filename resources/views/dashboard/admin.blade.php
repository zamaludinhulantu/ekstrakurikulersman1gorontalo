@extends('layouts.app')

@section('page_title', 'Dashboard Admin/Kesiswaan')
@section('page_subtitle', 'Pantau pendaftaran, data ekskul, dan aktivitas utama dari satu dashboard')

@section('content')
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card h-100"><div class="card-body stat-card"><span class="stat-icon"><i class="bi bi-grid-1x2"></i></span><p class="label">Total Ekstrakurikuler</p><p class="value">{{ $totalExtracurriculars }}</p><div class="trend">Unit kegiatan aktif</div></div></div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card h-100"><div class="card-body stat-card"><span class="stat-icon"><i class="bi bi-people"></i></span><p class="label">Total Siswa</p><p class="value">{{ $totalStudents }}</p><div class="trend">Peserta terdaftar</div></div></div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card h-100"><div class="card-body stat-card"><span class="stat-icon"><i class="bi bi-hourglass-split"></i></span><p class="label">Menunggu Konfirmasi</p><p class="value">{{ $pendingRegistrations }}</p><div class="trend">Perlu tindak lanjut</div></div></div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card h-100"><div class="card-body stat-card"><span class="stat-icon"><i class="bi bi-person-workspace"></i></span><p class="label">Total Pembina</p><p class="value">{{ $totalCoaches }}</p><div class="trend">Pendamping aktif</div></div></div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card h-100"><div class="card-body stat-card"><span class="stat-icon"><i class="bi bi-award"></i></span><p class="label">Total Prestasi</p><p class="value">{{ $assessmentCount }}</p><div class="trend">Data evaluasi tersimpan</div></div></div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card h-100"><div class="card-body stat-card"><span class="stat-icon"><i class="bi bi-calendar-event"></i></span><p class="label">Jadwal Hari Ini</p><p class="value">{{ $todaySchedules }}</p><div class="trend">Agenda berjalan</div></div></div>
        </div>
    </div>

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
@endsection
