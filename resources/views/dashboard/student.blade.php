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
    <x-dashboard.updated-at :value="$dashboardUpdatedAt" class="mb-3" />

    <div class="dashboard-stat-grid mb-3">
        <x-dashboard.stat-card label="Kegiatan Tersedia" :value="$availableExtracurriculars" hint="Kegiatan aktif yang dapat dipilih" icon="bi-grid" :href="route('student.extracurriculars.index')" action-label="Lihat kegiatan" />
        <x-dashboard.stat-card label="Pendaftaran Aktif" :value="$totalRegistrations" hint="Tidak termasuk pendaftaran dibatalkan" icon="bi-clipboard-check" tone="info" :href="route('student.registrations.index')" action-label="Lihat status" />
        <x-dashboard.stat-card label="Kegiatan Diikuti" :value="$approvedRegistrations" hint="Pendaftaran yang sudah diterima" icon="bi-person-check" tone="success" :href="route('student.registrations.index')" action-label="Lihat kegiatan" />
        <x-dashboard.stat-card label="Agenda Mendatang" :value="$upcomingSchedules" hint="Jadwal kegiatan setelah waktu sekarang" icon="bi-calendar-event" tone="warning" :href="route('student.schedules.index')" action-label="Lihat jadwal" />
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
        <div class="card dashboard-action-card mb-3">
            <div class="card-header dashboard-panel-header">
                <div><strong>Informasi Penting</strong><small>Status dan agenda yang perlu Anda perhatikan</small></div>
            </div>
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

    <div class="row g-3">
        <div class="col-12 col-xl-5">
            <div class="card h-100">
                <div class="card-header dashboard-panel-header">
                    <div><strong>Jadwal Kegiatan Terdekat</strong><small>Agenda dari kegiatan yang sudah menerima Anda</small></div>
                    <a href="{{ route('student.schedules.index') }}" class="btn btn-outline-primary btn-sm">Semua jadwal</a>
                </div>
                <div class="card-body">
                    @if($nextSchedule)
                        <div class="dashboard-next-schedule">
                            <span class="dashboard-date-tile is-large"><strong>{{ optional($nextSchedule->activity_date)->format('d') }}</strong><small>{{ optional($nextSchedule->activity_date)->translatedFormat('M') }}</small></span>
                            <div>
                                <strong>{{ $nextSchedule->title }}</strong>
                                <span>{{ $nextSchedule->extracurricular->name ?? '-' }}</span>
                                <span>{{ substr((string) $nextSchedule->start_time, 0, 5) }} | {{ $nextSchedule->location ?: 'Lokasi belum diisi' }}</span>
                            </div>
                        </div>
                    @else
                        <div class="empty-state py-3"><div class="icon"><i class="bi bi-calendar-x"></i></div><p class="mb-0">Belum ada jadwal kegiatan mendatang.</p></div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-7">
            <div class="card h-100">
                <div class="card-header dashboard-panel-header">
                    <div><strong>Jadwal Tes Bakat</strong><small>Tes yang sudah dijadwalkan untuk Anda</small></div>
                    <a href="{{ route('student.talent-tests.index') }}" class="btn btn-outline-primary btn-sm">Semua tes</a>
                </div>
                <div class="card-body">
                    <div class="dashboard-compact-list">
                        @forelse($upcomingTalentTests as $test)
                            <a href="{{ route('student.talent-tests.index') }}" class="dashboard-compact-item">
                                <span class="dashboard-date-tile"><strong>{{ optional($test->schedule?->activity_date)->format('d') }}</strong><small>{{ optional($test->schedule?->activity_date)->translatedFormat('M') }}</small></span>
                                <span><strong>{{ $test->schedule?->title ?? '-' }}</strong><small>{{ $test->schedule?->extracurricular?->name ?? '-' }} | {{ substr((string) $test->schedule?->start_time, 0, 5) }} | {{ $test->schedule?->location ?: 'Lokasi belum diisi' }}</small></span>
                                <span class="badge" data-status="{{ $test->attendance_status }}">{{ $test->attendance_status }}</span>
                            </a>
                        @empty
                            <div class="empty-state py-3"><div class="icon"><i class="bi bi-clipboard2-pulse"></i></div><p class="mb-0">Belum ada jadwal tes bakat terdekat.</p></div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header dashboard-panel-header">
            <div><strong>Pengumuman Terbaru</strong><small>Informasi sekolah dan kegiatan yang Anda ikuti</small></div>
            <a href="{{ route('notifications.index') }}" class="btn btn-outline-primary btn-sm">Kotak masuk</a>
        </div>
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
