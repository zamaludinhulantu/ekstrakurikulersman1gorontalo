@extends('layouts.app')

@section('page_title', $dashboardTitle ?? 'Dashboard Admin/Kesiswaan')
@section('page_subtitle', $dashboardSubtitle ?? 'Pantau pendaftaran, data ekskul, dan aktivitas utama dari satu dashboard')

@section('content')
    <x-dashboard.updated-at :value="$dashboardUpdatedAt" class="mb-3" />

    <div class="dashboard-stat-grid mb-3">
        <x-dashboard.stat-card
            label="Kegiatan Aktif"
            :value="$totalExtracurriculars"
            hint="Kegiatan yang tersedia untuk siswa"
            icon="bi-grid-1x2"
            :href="route('admin.extracurriculars.index')"
            action-label="Lihat kegiatan"
        />
        <x-dashboard.stat-card
            label="Siswa Terdaftar"
            :value="$totalStudents"
            hint="Data siswa unik dalam sistem"
            icon="bi-people"
            tone="success"
            :href="route('admin.students.index')"
            action-label="Lihat siswa"
        />
        <x-dashboard.stat-card
            label="Pendaftaran Menunggu"
            :value="$pendingRegistrations"
            hint="Record pendaftaran yang perlu diputuskan"
            icon="bi-hourglass-split"
            tone="warning"
            :href="route('admin.registrations.index', ['status' => \App\Models\Registration::STATUS_PENDING])"
            action-label="Periksa"
        />
        <x-dashboard.stat-card
            label="Jadwal Tes Mendatang"
            :value="$upcomingTalentTests"
            hint="Tes aktif setelah waktu sekarang"
            icon="bi-clipboard2-pulse"
            tone="info"
            :href="route('admin.talent-tests.index')"
            action-label="Lihat tes"
        />
    </div>

    <div class="card dashboard-action-card mb-3">
        <div class="card-header dashboard-panel-header">
            <div>
                <strong>Perlu Tindakan</strong>
                <small>Pekerjaan operasional yang masih perlu diselesaikan</small>
            </div>
        </div>
        <div class="card-body">
            <x-dashboard.action-list :items="$actionItems" />
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-12 col-xl-8">
            <div class="card admin-registration-chart h-100">
                <div class="card-header admin-registration-chart__header">
                    <div>
                        <strong>Tren Pendaftaran</strong>
                        <div class="small text-muted fw-normal mt-1">Enam bulan berurutan, termasuk bulan tanpa pendaftaran</div>
                    </div>
                    <div class="admin-registration-chart__legend" aria-label="Keterangan grafik">
                        <span><i class="is-pending"></i>Menunggu</span>
                        <span><i class="is-approved"></i>Diterima</span>
                        <span><i class="is-rejected"></i>Ditolak</span>
                        <span><i class="is-cancelled"></i>Dibatalkan</span>
                    </div>
                </div>
                <div class="card-body">
                    @if(collect($registrationTrend['months'])->sum('total') > 0)
                        <div class="admin-registration-chart__frame">
                            <div class="admin-registration-chart__axis" aria-hidden="true">
                                <span>{{ $registrationTrend['maximum'] }}</span>
                                <span>{{ (int) ceil($registrationTrend['maximum'] / 2) }}</span>
                                <span>0</span>
                            </div>
                            <div class="admin-registration-chart__plot" role="img" aria-label="Grafik pendaftaran enam bulan terakhir. Nilai maksimum {{ $registrationTrend['maximum'] }} pendaftaran.">
                                @php
                                    $registrationStatusLabels = [
                                        'pending' => 'Menunggu',
                                        'approved' => 'Diterima',
                                        'rejected' => 'Ditolak',
                                        'cancelled' => 'Dibatalkan',
                                    ];
                                @endphp
                                @foreach($registrationTrend['months'] as $month)
                                    <div class="admin-registration-chart__column">
                                        <strong class="admin-registration-chart__total">{{ $month['total'] }}</strong>
                                        <div class="admin-registration-chart__bar">
                                            @foreach(['pending', 'approved', 'rejected', 'cancelled'] as $status)
                                                @if($month[$status] > 0)
                                                    <span
                                                        class="admin-registration-chart__segment is-{{ $status }}"
                                                        style="height: {{ $month[$status . '_height'] }}%"
                                                        title="{{ $month['label'] }} {{ $month['year'] }} - {{ $registrationStatusLabels[$status] }}: {{ $month[$status] }}"
                                                    ></span>
                                                @endif
                                            @endforeach
                                        </div>
                                        <div class="admin-registration-chart__month">{{ $month['label'] }}</div>
                                        <div class="admin-registration-chart__year">{{ $month['year'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="empty-state py-4">
                            <div class="icon"><i class="bi bi-bar-chart"></i></div>
                            <p class="mb-0">Belum ada pendaftaran dalam enam bulan terakhir.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="card h-100">
                <div class="card-header dashboard-panel-header">
                    <div>
                        <strong>Distribusi Status</strong>
                        <small>Seluruh record pendaftaran</small>
                    </div>
                </div>
                <div class="card-body">
                    @php($distributionTotal = collect($statusDistribution)->sum('value'))
                    @if($distributionTotal > 0)
                        <div class="dashboard-status-distribution" aria-label="Distribusi {{ $distributionTotal }} pendaftaran">
                            @foreach($statusDistribution as $item)
                                <div class="dashboard-status-row">
                                    <div>
                                        <span class="dashboard-status-dot is-{{ $item['key'] }}"></span>
                                        <strong>{{ $item['label'] }}</strong>
                                        <span>{{ $item['value'] }}</span>
                                    </div>
                                    <div class="dashboard-status-track">
                                        <span class="is-{{ $item['key'] }}" style="width: {{ round(($item['value'] / $distributionTotal) * 100, 2) }}%"></span>
                                    </div>
                                </div>
                            @endforeach
                            <p class="dashboard-status-total">{{ $distributionTotal }} total pendaftaran</p>
                        </div>
                    @else
                        <div class="empty-state py-4">
                            <div class="icon"><i class="bi bi-pie-chart"></i></div>
                            <p class="mb-0">Belum ada status pendaftaran untuk diringkas.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header dashboard-panel-header">
                    <div>
                        <strong>Kegiatan dengan Anggota Terbanyak</strong>
                        <small>Keanggotaan berstatus diterima</small>
                    </div>
                </div>
                <div class="card-body">
                    @if($popularExtracurriculars !== [])
                        <div class="admin-popularity-chart admin-ranking-chart--scroll" role="img" aria-label="Lima kegiatan dengan anggota aktif terbanyak">
                            @foreach($popularExtracurriculars as $index => $item)
                                <div class="admin-popularity-chart__row">
                                    <div class="admin-popularity-chart__meta">
                                        <span class="admin-popularity-chart__rank">{{ $index + 1 }}</span>
                                        <strong title="{{ $item['name'] }}">{{ $item['name'] }}</strong>
                                        <span>{{ $item['total'] }} anggota</span>
                                    </div>
                                    <div class="admin-popularity-chart__track"><span class="admin-popularity-chart__fill" style="width: {{ $item['width'] }}%"></span></div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state py-3"><div class="icon"><i class="bi bi-people"></i></div><p class="mb-0">Belum ada data anggota untuk ditampilkan.</p></div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header dashboard-panel-header">
                    <div>
                        <strong>Kegiatan dengan Pendaftar Menunggu Terbanyak</strong>
                        <small>Pendaftaran baru yang belum diputuskan</small>
                    </div>
                </div>
                <div class="card-body">
                    @if($popularRegistrations !== [])
                        <div class="admin-registrants-chart admin-ranking-chart--scroll" role="img" aria-label="Lima kegiatan dengan pendaftar menunggu terbanyak">
                            @foreach($popularRegistrations as $index => $item)
                                <div class="admin-registrants-chart__row">
                                    <div class="admin-registrants-chart__meta">
                                        <span class="admin-registrants-chart__rank">{{ $index + 1 }}</span>
                                        <strong title="{{ $item['name'] }}">{{ $item['name'] }}</strong>
                                        <span>{{ $item['total'] }} pendaftar</span>
                                    </div>
                                    <div class="admin-registrants-chart__track"><span class="admin-registrants-chart__fill" style="width: {{ $item['width'] }}%"></span></div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state py-3"><div class="icon"><i class="bi bi-hourglass"></i></div><p class="mb-0">Tidak ada pendaftaran menunggu untuk ditampilkan.</p></div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header dashboard-panel-header">
                    <div><strong>Aktivitas Pendaftaran Terbaru</strong><small>Lima perubahan pendaftaran terakhir</small></div>
                    <a href="{{ route('admin.registrations.index') }}" class="btn btn-outline-primary btn-sm">Semua pendaftar</a>
                </div>
                <div class="card-body">
                    <div class="dashboard-compact-list dashboard-compact-list--scroll">
                        @forelse($recentRegistrations as $registration)
                            <a href="{{ route('admin.registrations.show', $registration) }}" class="dashboard-compact-item">
                                <span class="dashboard-compact-item__icon"><i class="bi bi-person-plus"></i></span>
                                <span><strong>{{ $registration->student->user->name ?? 'Siswa' }}</strong><small>{{ $registration->extracurricular->name ?? '-' }} | {{ optional($registration->created_at)->diffForHumans() }}</small></span>
                                <x-registration.status-badge :registration="$registration" />
                            </a>
                        @empty
                            <div class="empty-state py-3"><p class="mb-0">Belum ada aktivitas pendaftaran terbaru.</p></div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header dashboard-panel-header">
                    <div><strong>Jadwal Terdekat</strong><small>Agenda aktif setelah waktu sekarang</small></div>
                    <a href="{{ route('admin.schedules.index') }}" class="btn btn-outline-primary btn-sm">Semua jadwal</a>
                </div>
                <div class="card-body">
                    <div class="dashboard-compact-list dashboard-compact-list--scroll">
                        @forelse($upcomingSchedules as $schedule)
                            <a href="{{ route('admin.schedules.index') }}" class="dashboard-compact-item">
                                <span class="dashboard-date-tile"><strong>{{ optional($schedule->activity_date)->format('d') }}</strong><small>{{ optional($schedule->activity_date)->translatedFormat('M') }}</small></span>
                                <span><strong>{{ $schedule->title }}</strong><small>{{ $schedule->extracurricular->name ?? '-' }} | {{ substr((string) $schedule->start_time, 0, 5) }} | {{ $schedule->location ?: 'Lokasi belum diisi' }}</small></span>
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        @empty
                            <div class="empty-state py-3"><div class="icon"><i class="bi bi-calendar-x"></i></div><p class="mb-0">Belum ada jadwal mendatang.</p></div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
