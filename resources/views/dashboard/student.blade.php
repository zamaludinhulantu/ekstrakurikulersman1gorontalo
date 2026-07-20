@extends('layouts.app')

@section('page_title', 'Dashboard Siswa')
@section('page_subtitle', 'Halo, ' . $student->user->name . ($student->nis ? ' (' . $student->nis . ')' : ''))

@push('styles')
    <style>
        .dashboard-notification {
            user-select: none;
        }
    </style>
@endpush

@section('content')
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-6 col-xl-3">
            <div class="card h-100"><div class="card-body stat-card"><span class="stat-icon"><i class="bi bi-grid"></i></span><p class="label">Ekskul Tersedia</p><p class="value">{{ $availableExtracurriculars }}</p></div></div>
        </div>
        <div class="col-6 col-md-6 col-xl-3">
            <div class="card h-100"><div class="card-body stat-card"><span class="stat-icon"><i class="bi bi-clipboard-check"></i></span><p class="label">Pendaftaran Saya</p><p class="value">{{ $totalRegistrations }}</p></div></div>
        </div>
        <div class="col-6 col-md-6 col-xl-3">
            <div class="card h-100"><div class="card-body stat-card"><span class="stat-icon"><i class="bi bi-person-check"></i></span><p class="label">Sudah Diterima</p><p class="value">{{ $approvedRegistrations }}</p></div></div>
        </div>
        <div class="col-6 col-md-6 col-xl-3">
            <div class="card h-100"><div class="card-body stat-card"><span class="stat-icon"><i class="bi bi-calendar-event"></i></span><p class="label">Jadwal Terdekat</p><p class="value">{{ $upcomingSchedules }}</p></div></div>
        </div>
    </div>

    @if($totalRegistrations === 0)
        <div class="info-banner mb-3">
            <i class="bi bi-megaphone"></i>
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 w-100">
                <div>
                    <strong class="d-block mb-1">Kamu belum mendaftar ekstrakurikuler.</strong>
                    Yuk pilih ekskul sesuai minatmu agar bisa segera mengikuti kegiatan sekolah.
                </div>
                <a href="{{ route('student.extracurriculars.index') }}" class="btn btn-primary"><i class="bi bi-grid-1x2"></i>Pilih Ekstrakurikuler</a>
            </div>
        </div>
    @endif

    @if($notifications !== [])
        <div class="card mb-3">
            <div class="card-header">Informasi Penting</div>
            <div class="card-body">
                <div class="row g-2">
                    @foreach($notifications as $notification)
                        <div class="col-12 col-lg-6">
                            <div class="alert alert-{{ $notification['type'] }} mb-0 dashboard-notification">
                                <i class="bi {{ $notification['icon'] }} me-2"></i>{{ $notification['message'] }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header">Jadwal Tes Bakat</div>
        <div class="card-body">
            <div class="info-list">
                @forelse($upcomingTalentTests as $test)
                    <div class="info-item">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                            <div>
                                <div class="title">{{ $test->schedule->title ?? '-' }}</div>
                                <div class="small text-muted mt-1">{{ $test->schedule->extracurricular->name ?? '-' }} | {{ $test->schedule->location ?? '-' }}</div>
                            </div>
                            <span class="badge" data-status="{{ $test->attendance_status }}">{{ $test->attendance_status }}</span>
                        </div>
                        <div class="small mt-2">{{ optional($test->schedule->activity_date)->format('d-m-Y') }} | {{ \Illuminate\Support\Str::substr($test->schedule->start_time, 0, 5) }} - {{ \Illuminate\Support\Str::substr($test->schedule->end_time, 0, 5) }}</div>
                    </div>
                @empty
                    <div class="empty-state py-3">
                        <div class="icon"><i class="bi bi-clipboard2-pulse"></i></div>
                        <p class="mb-0">Belum ada jadwal tes bakat terdekat.</p>
                    </div>
                @endforelse
            </div>
            <div class="form-actions mt-3">
                <a href="{{ route('student.talent-tests.index') }}" class="btn btn-outline-primary"><i class="bi bi-arrow-right-circle"></i>Lihat Semua Tes</a>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">Pengumuman Terbaru</div>
        <div class="card-body">
            <div class="info-list">
                @forelse($recentAnnouncements as $announcement)
                    <div class="info-item">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                            <div>
                                <div class="title">{{ $announcement->title }}</div>
                                <div class="small text-muted mt-1">
                                    {{ $announcement->extracurricular->name ?? 'Semua ekstrakurikuler' }} | {{ $announcement->publisher->name ?? 'Admin/Pembina' }}
                                </div>
                            </div>
                            <span class="small text-muted">{{ optional($announcement->created_at)->format('d-m-Y') }}</span>
                        </div>
                        <div class="small mt-2">{{ $announcement->content }}</div>
                    </div>
                @empty
                    <div class="empty-state py-3">
                        <div class="icon"><i class="bi bi-megaphone"></i></div>
                        <p class="mb-0">Belum ada pengumuman terbaru.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
