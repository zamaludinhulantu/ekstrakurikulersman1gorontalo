@extends('layouts.app')

@section('page_title', 'Dashboard Pembina')
@section('page_subtitle', 'Ringkasan kegiatan binaan ' . $coach->user->name . ($coach->nip ? ' | NIP ' . $coach->nip : ''))

@section('content')
    <x-dashboard.updated-at :value="$dashboardUpdatedAt" class="mb-3" />

    <div class="dashboard-stat-grid mb-3">
        <x-dashboard.stat-card
            label="Kegiatan Binaan"
            :value="$totalExtracurriculars"
            hint="Kegiatan dalam tanggung jawab Anda"
            icon="bi-grid-1x2"
            :href="route('coach.extracurriculars.index')"
            action-label="Lihat binaan"
        />
        <x-dashboard.stat-card
            label="Siswa Aktif"
            :value="$activeStudents"
            :hint="$totalParticipants . ' keanggotaan diterima'"
            icon="bi-people"
            tone="success"
            :href="route('coach.extracurriculars.index')"
            action-label="Lihat anggota"
        />
        <x-dashboard.stat-card
            label="Pendaftaran Menunggu"
            :value="$pendingRegistrations"
            hint="Record pendaftaran yang perlu diperiksa"
            icon="bi-hourglass-split"
            tone="warning"
            :href="route('coach.registrations.index', ['status' => \App\Models\Registration::STATUS_PENDING])"
            action-label="Periksa"
        />
        <x-dashboard.stat-card
            label="Jadwal Tes Mendatang"
            :value="$upcomingTalentTests"
            hint="Tes kegiatan binaan setelah waktu sekarang"
            icon="bi-clipboard2-pulse"
            tone="info"
            :href="route('coach.talent-tests.index')"
            action-label="Lihat tes"
        />
    </div>

    <div class="card dashboard-action-card mb-3">
        <div class="card-header dashboard-panel-header">
            <div>
                <strong>Perlu Tindakan</strong>
                <small>Pekerjaan pada kegiatan binaan yang perlu diselesaikan</small>
            </div>
        </div>
        <div class="card-body">
            <x-dashboard.action-list :items="$actionItems" empty-message="Tidak ada pekerjaan kegiatan binaan yang tertunda." />
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header dashboard-panel-header">
                    <div><strong>Jadwal Terdekat</strong><small>Agenda kegiatan binaan setelah waktu sekarang</small></div>
                    <a href="{{ route('coach.schedules.index') }}" class="btn btn-outline-primary btn-sm">Semua jadwal</a>
                </div>
                <div class="card-body">
                    <div class="dashboard-compact-list">
                        @forelse($upcomingSchedules as $schedule)
                            <a href="{{ route('coach.schedules.index') }}" class="dashboard-compact-item">
                                <span class="dashboard-date-tile"><strong>{{ optional($schedule->activity_date)->format('d') }}</strong><small>{{ optional($schedule->activity_date)->translatedFormat('M') }}</small></span>
                                <span><strong>{{ $schedule->title }}</strong><small>{{ $schedule->extracurricular->name ?? '-' }} | {{ substr((string) $schedule->start_time, 0, 5) }} | {{ $schedule->location ?: 'Lokasi belum diisi' }}</small></span>
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        @empty
                            <div class="empty-state py-3"><div class="icon"><i class="bi bi-calendar-x"></i></div><p class="mb-0">Belum ada jadwal kegiatan binaan yang akan datang.</p></div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header dashboard-panel-header">
                    <div><strong>Pendaftaran Terbaru</strong><small>Hanya berasal dari kegiatan binaan Anda</small></div>
                    <a href="{{ route('coach.registrations.index') }}" class="btn btn-outline-primary btn-sm">Semua pendaftar</a>
                </div>
                <div class="card-body">
                    <div class="dashboard-compact-list">
                        @forelse($recentRegistrations as $registration)
                            <a href="{{ route('coach.registrations.show', $registration) }}" class="dashboard-compact-item">
                                <span class="dashboard-compact-item__icon"><i class="bi bi-person-plus"></i></span>
                                <span><strong>{{ $registration->student->user->name ?? 'Siswa' }}</strong><small>{{ $registration->extracurricular->name ?? '-' }} | {{ optional($registration->created_at)->diffForHumans() }}</small></span>
                                <x-registration.status-badge :registration="$registration" />
                            </a>
                        @empty
                            <div class="empty-state py-3"><p class="mb-0">Belum ada pendaftaran pada kegiatan binaan.</p></div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header dashboard-panel-header">
                    <div><strong>Kehadiran Terbaru</strong><small>Lima pencatatan presensi terakhir</small></div>
                    <a href="{{ route('coach.attendances.index') }}" class="btn btn-outline-primary btn-sm">Kelola presensi</a>
                </div>
                <div class="card-body">
                    <div class="dashboard-compact-list">
                        @forelse($recentAttendances as $attendance)
                            <a href="{{ route('coach.attendances.index') }}" class="dashboard-compact-item">
                                <span class="dashboard-compact-item__icon"><i class="bi bi-check2-square"></i></span>
                                <span><strong>{{ $attendance->student?->user?->name ?? '-' }}</strong><small>{{ $attendance->schedule?->extracurricular?->name ?? '-' }} | {{ $attendance->schedule?->title ?? '-' }}</small></span>
                                <span class="badge" data-status="{{ $attendance->status }}">{{ $attendance->status }}</span>
                            </a>
                        @empty
                            <div class="empty-state py-3"><p class="mb-0">Belum ada data kehadiran terbaru.</p></div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header dashboard-panel-header">
                    <div><strong>Pengumuman Saya</strong><small>Publikasi terbaru yang Anda kelola</small></div>
                    <a href="{{ route('coach.announcements.index') }}" class="btn btn-outline-primary btn-sm">Kelola</a>
                </div>
                <div class="card-body">
                    <div class="dashboard-compact-list">
                        @forelse($recentAnnouncements as $announcement)
                            @php
                                $announcementStatus = $announcement->publication_status
                                    ?: ($announcement->is_active ? \App\Models\Announcement::STATUS_PUBLISHED : \App\Models\Announcement::STATUS_INACTIVE);
                                $announcementStatusLabel = match($announcementStatus) {
                                    \App\Models\Announcement::STATUS_PUBLISHED => 'Dipublikasikan',
                                    \App\Models\Announcement::STATUS_SCHEDULED => 'Terjadwal',
                                    \App\Models\Announcement::STATUS_INACTIVE => 'Dinonaktifkan',
                                    default => 'Draft',
                                };
                            @endphp
                            <a href="{{ route('coach.announcements.index') }}" class="dashboard-compact-item">
                                <span class="dashboard-compact-item__icon"><i class="bi bi-megaphone"></i></span>
                                <span><strong>{{ $announcement->title }}</strong><small>{{ $announcement->extracurricular->name ?? 'Semua binaan' }} | {{ optional($announcement->created_at)->diffForHumans() }}</small></span>
                                <span class="badge" data-status="{{ $announcementStatus }}">{{ $announcementStatusLabel }}</span>
                            </a>
                        @empty
                            <div class="empty-state py-3"><div class="icon"><i class="bi bi-megaphone"></i></div><p class="mb-0">Belum ada pengumuman.</p></div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
