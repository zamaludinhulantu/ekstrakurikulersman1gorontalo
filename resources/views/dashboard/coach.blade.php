@extends('layouts.app')

@section('page_title', 'Dashboard Pembina Ekstrakurikuler')
@section('page_subtitle', 'Halo, ' . $coach->user->name . ' (NIP: ' . $coach->nip . ')')

@section('content')
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-4 col-xl-3 col-xxl-2">
            <div class="card h-100"><div class="card-body stat-card"><span class="stat-icon"><i class="bi bi-grid"></i></span><p class="label">Ekskul Dibina</p><p class="value">{{ $totalExtracurriculars }}</p><div class="trend">Binaan aktif</div></div></div>
        </div>
        <div class="col-6 col-md-4 col-xl-3 col-xxl-2">
            <div class="card h-100"><div class="card-body stat-card"><span class="stat-icon"><i class="bi bi-people"></i></span><p class="label">Total Peserta</p><p class="value">{{ $totalParticipants }}</p><div class="trend">Siswa terlibat</div></div></div>
        </div>
        <div class="col-6 col-md-4 col-xl-3 col-xxl-2">
            <div class="card h-100"><div class="card-body stat-card"><span class="stat-icon"><i class="bi bi-calendar-event"></i></span><p class="label">Jadwal Hari Ini</p><p class="value">{{ $todaySchedules }}</p><div class="trend">Agenda aktif</div></div></div>
        </div>
        <div class="col-6 col-md-4 col-xl-3 col-xxl-2">
            <div class="card h-100"><div class="card-body stat-card"><span class="stat-icon"><i class="bi bi-award"></i></span><p class="label">Data Penilaian</p><p class="value">{{ $assessmentCount }}</p><div class="trend">Catatan evaluasi</div></div></div>
        </div>
    </div>

    <div class="card">
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
@endsection
