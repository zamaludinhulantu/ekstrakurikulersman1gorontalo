@extends('layouts.app')

@section('page_title', 'Dashboard Kepala Sekolah')
@section('page_subtitle', 'Pantauan read-only untuk seluruh kegiatan ekstrakurikuler sekolah')

@php
    $tabs = [
        'overview' => 'Ringkasan',
        'activities' => 'Ekstrakurikuler',
        'students' => 'Siswa',
        'attendance' => 'Kehadiran',
        'achievements' => 'Prestasi',
        'agenda' => 'Agenda',
        'reports' => 'Laporan',
    ];

    $summaryCards = [
        ['key' => 'total_extracurriculars', 'label' => 'Total Ekstrakurikuler', 'icon' => 'bi-grid-1x2', 'hint' => 'Unit kegiatan yang tercatat', 'href' => route('principal.dashboard', array_merge(request()->query(), ['tab' => 'activities'])), 'tone' => 'primary'],
        ['key' => 'total_students', 'label' => 'Total Siswa', 'icon' => 'bi-people', 'hint' => 'Seluruh siswa pada sistem', 'href' => route('principal.dashboard', array_merge(request()->query(), ['tab' => 'students'])), 'tone' => 'info'],
        ['key' => 'students_joined', 'label' => 'Sudah Ikut Ekskul', 'icon' => 'bi-person-check', 'hint' => 'Siswa dengan pendaftaran diterima', 'href' => route('principal.dashboard', array_merge(request()->query(), ['tab' => 'students'])), 'tone' => 'success'],
        ['key' => 'students_not_joined', 'label' => 'Belum Ikut Ekskul', 'icon' => 'bi-person-x', 'hint' => 'Perlu pendampingan atau sosialisasi', 'href' => route('principal.dashboard', array_merge(request()->query(), ['tab' => 'students'])), 'tone' => 'warning'],
        ['key' => 'registrations_approved', 'label' => 'Pendaftaran Diterima', 'icon' => 'bi-patch-check', 'hint' => 'Record pendaftaran berstatus diterima', 'href' => $reportLinks['registrations'], 'tone' => 'success'],
        ['key' => 'registrations_rejected', 'label' => 'Pendaftaran Ditolak', 'icon' => 'bi-x-octagon', 'hint' => 'Record pendaftaran berstatus ditolak', 'href' => $reportLinks['registrations'], 'tone' => 'danger'],
        ['key' => 'registrations_pending', 'label' => 'Pendaftaran Diperiksa', 'icon' => 'bi-hourglass-split', 'hint' => 'Menunggu keputusan pembina/admin', 'href' => $reportLinks['registrations'], 'tone' => 'warning'],
    ];
@endphp

@section('content')
    <x-dashboard.updated-at :value="$dashboardUpdatedAt" class="mb-3" />

    <div class="surface-card toolbar-card mb-3">
        <div class="section-header-inline">
            <div>
                <h2>Filter dashboard</h2>
                <p>Gunakan filter untuk melihat tren per tahun ajaran, kelas, jenis kelamin, dan ekstrakurikuler tertentu.</p>
            </div>
            <div class="quick-actions">
                <a href="{{ route('principal.reports.index') }}" class="btn btn-primary"><i class="bi bi-file-earmark-bar-graph"></i>Pusat laporan</a>
                <a href="{{ route('principal.attendances.index') }}" class="btn btn-outline-primary"><i class="bi bi-check2-square"></i>Rekap presensi</a>
            </div>
        </div>
        <form method="get" class="toolbar-grid">
            <input type="hidden" name="tab" value="{{ $activeTab }}">
            <div class="toolbar-col-3">
                <label class="form-label" for="school_year">Tahun ajaran</label>
                <select class="form-select" id="school_year" name="school_year">
                    @foreach($schoolYearOptions as $option)
                        <option value="{{ $option }}" @selected(($filters['school_year'] ?? null) === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
            <div class="toolbar-col-2">
                <label class="form-label" for="semester">Semester</label>
                <select class="form-select" id="semester" name="semester">
                    <option value="all" @selected(($filters['semester'] ?? 'all') === 'all')>Semua</option>
                    <option value="odd" @selected(($filters['semester'] ?? null) === 'odd')>Ganjil</option>
                    <option value="even" @selected(($filters['semester'] ?? null) === 'even')>Genap</option>
                </select>
            </div>
            <div class="toolbar-col-3">
                <label class="form-label" for="extracurricular_id">Ekstrakurikuler</label>
                <select class="form-select" id="extracurricular_id" name="extracurricular_id">
                    <option value="">Semua ekstrakurikuler</option>
                    @foreach($extracurricularOptions as $option)
                        <option value="{{ $option->id }}" @selected((string) ($filters['extracurricular_id'] ?? '') === (string) $option->id)>{{ $option->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="toolbar-col-2">
                <label class="form-label" for="class_name">Kelas</label>
                <select class="form-select" id="class_name" name="class_name">
                    <option value="">Semua kelas</option>
                    @foreach($classOptions as $option)
                        <option value="{{ $option }}" @selected(($filters['class_name'] ?? null) === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
            <div class="toolbar-col-2">
                <label class="form-label" for="gender">Jenis kelamin</label>
                <select class="form-select" id="gender" name="gender">
                    <option value="">Semua</option>
                    <option value="L" @selected(($filters['gender'] ?? null) === 'L')>Laki-laki</option>
                    <option value="P" @selected(($filters['gender'] ?? null) === 'P')>Perempuan</option>
                </select>
            </div>
            <div class="toolbar-col-8">
                <label class="form-label" for="search">Pencarian</label>
                <input class="form-control" id="search" type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cari nama siswa, NIS, kelas, atau nama ekstrakurikuler">
            </div>
            <div class="toolbar-col-4">
                <div class="quick-actions w-100">
                    <button type="submit" class="btn btn-primary flex-fill" data-loading-text="Menerapkan filter..."><i class="bi bi-funnel"></i>Terapkan</button>
                    <a href="{{ route('principal.dashboard') }}" class="btn btn-outline-secondary flex-fill"><i class="bi bi-arrow-counterclockwise"></i>Reset</a>
                </div>
            </div>
            <div class="toolbar-col-12">
                <div class="toolbar-hint">Periode aktif: {{ $windowLabel }}</div>
            </div>
        </form>
    </div>

    <div class="tab-scroll-nav mb-3" data-tab-scroll-nav>
        @foreach($tabs as $tabKey => $tabLabel)
            <a href="{{ route('principal.dashboard', array_merge(request()->query(), ['tab' => $tabKey])) }}"
               class="tab-scroll-nav__item {{ $activeTab === $tabKey ? 'active' : '' }}">
                {{ $tabLabel }}
            </a>
        @endforeach
    </div>

    @if($activeTab === 'overview')
        <div class="dashboard-stat-grid dashboard-stat-grid--wide mb-3">
            @foreach($summaryCards as $card)
                <x-dashboard.stat-card
                    :label="$card['label']"
                    :value="$summary[$card['key']] ?? 0"
                    :hint="$card['hint']"
                    :icon="$card['icon']"
                    :tone="$card['tone']"
                    :href="$card['href']"
                    action-label="Lihat ringkasan"
                />
            @endforeach
        </div>

        <div class="row g-3">
            <div class="col-12 col-xl-6">
                <div class="surface-card h-100">
                    <div class="surface-card-header">Anggota per ekstrakurikuler</div>
                    <div class="surface-card-body">
                        @include('principal.reports.partials.metric-bars', ['rows' => $charts['member_chart'], 'suffix' => 'anggota'])
                    </div>
                </div>
            </div>
            <div class="col-12 col-xl-6">
                <div class="surface-card h-100">
                    <div class="surface-card-header">Tren pendaftaran</div>
                    <div class="surface-card-body">
                        @include('principal.reports.partials.metric-bars', ['rows' => $charts['registration_trend'], 'suffix' => 'pendaftaran'])
                    </div>
                </div>
            </div>
            <div class="col-12 col-xl-6">
                <div class="surface-card h-100">
                    <div class="surface-card-header">Ekstrakurikuler paling diminati</div>
                    <div class="surface-card-body">
                        @include('principal.reports.partials.metric-bars', ['rows' => $charts['top_interest'], 'suffix' => 'peminat'])
                    </div>
                </div>
            </div>
            <div class="col-12 col-xl-6">
                <div class="surface-card h-100">
                    <div class="surface-card-header">Ekstrakurikuler paling sedikit diminati</div>
                    <div class="surface-card-body">
                        @include('principal.reports.partials.metric-bars', ['rows' => $charts['lowest_interest'], 'suffix' => 'peminat'])
                    </div>
                </div>
            </div>
            <div class="col-12 col-xl-6">
                <div class="surface-card h-100">
                    <div class="surface-card-header">Persentase kehadiran</div>
                    <div class="surface-card-body">
                        @include('principal.reports.partials.metric-bars', ['rows' => $charts['attendance_chart'], 'suffix' => '%'])
                    </div>
                </div>
            </div>
            <div class="col-12 col-xl-6">
                <div class="surface-card h-100">
                    <div class="surface-card-header">Jumlah prestasi per ekstrakurikuler</div>
                    <div class="surface-card-body">
                        @include('principal.reports.partials.metric-bars', ['rows' => $charts['achievement_chart'], 'suffix' => 'prestasi'])
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($activeTab === 'activities')
        <div class="surface-card">
            <div class="surface-card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
                <span>Data ekstrakurikuler</span>
                <a href="{{ $reportLinks['activities'] }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-download"></i>Laporan kegiatan</a>
            </div>
            <div class="surface-card-body">
                @if($activities->count())
                    <div class="row g-3">
                        @foreach($activities as $activity)
                            <div class="col-12 col-xl-6">
                                <article class="principal-panel-card h-100">
                                    <div class="principal-panel-card__head">
                                        <div>
                                            <h3>{{ $activity->name }}</h3>
                                            <p>{{ $activity->coach_names ?: 'Pembina belum tercatat' }}</p>
                                        </div>
                                        <span class="badge {{ $activity->is_active ? 'badge-status-success' : 'badge-status-secondary' }}">{{ $activity->is_active ? 'Aktif' : 'Tidak aktif' }}</span>
                                    </div>
                                    <div class="principal-metadata-grid">
                                        <div><span>Anggota</span><strong>{{ $activity->principal_members_count }}</strong></div>
                                        <div><span>Pendaftar</span><strong>{{ $activity->principal_registration_count }}</strong></div>
                                        <div><span>Kuota</span><strong>{{ $activity->principal_quota ?? '-' }}</strong></div>
                                        <div><span>Kehadiran</span><strong>{{ number_format($activity->principal_attendance_rate, 1) }}%</strong></div>
                                    </div>
                                    <div class="principal-note-box">
                                        <span>Jadwal latihan terbaru</span>
                                        <strong>{{ $activity->principal_recent_schedule_text }}</strong>
                                    </div>
                                </article>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-3">{{ $activities->links() }}</div>
                @else
                    <div class="empty-state">
                        <div class="icon"><i class="bi bi-grid-1x2"></i></div>
                        <p class="mb-0">Belum ada data ekstrakurikuler yang sesuai dengan filter.</p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if($activeTab === 'students')
        <div class="row g-3">
            <div class="col-12 col-xxl-7">
                <div class="surface-card h-100">
                    <div class="surface-card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
                        <span>Siswa yang sudah mengikuti ekstrakurikuler</span>
                        <a href="{{ $reportLinks['members'] }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-download"></i>Unduh anggota</a>
                    </div>
                    <div class="surface-card-body">
                        @if($joinedStudents->count())
                            <div class="table-responsive desktop-table">
                                <table class="table align-middle">
                                    <thead>
                                    <tr>
                                        <th>Siswa</th>
                                        <th>Kelas</th>
                                        <th>Ekstrakurikuler</th>
                                        <th>Status</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($joinedStudents as $student)
                                        @php
                                            $approved = $student->registrations->where('status', \App\Models\Registration::STATUS_APPROVED);
                                            $approvedActivities = $approved->pluck('extracurricular')->filter()->unique('id')->values();
                                        @endphp
                                        <tr>
                                            <td><x-student.identity :student="$student" :subtitle="$student->nis ?: 'NIS belum diisi'" /></td>
                                            <td>{{ $student->class_name ?: 'Kelas belum diisi' }}</td>
                                            <td><x-student.activity-badges :activities="$approvedActivities" /></td>
                                            <td><span class="badge badge-status-success">Aktif mengikuti</span></td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mobile-stack-table">
                                @foreach($joinedStudents as $student)
                                    @php
                                        $approved = $student->registrations->where('status', \App\Models\Registration::STATUS_APPROVED);
                                        $approvedActivities = $approved->pluck('extracurricular')->filter()->unique('id')->values();
                                    @endphp
                                    <article class="mobile-data-card">
                                        <div class="mobile-data-card-header">
                                            <x-student.identity :student="$student" :subtitle="$student->class_name ?: 'Kelas belum diisi'" />
                                            <span class="badge badge-status-success">Aktif</span>
                                        </div>
                                        <div class="mobile-data-list">
                                            <div><span class="mobile-data-item-label">NIS</span><span class="mobile-data-item-value {{ $student->nis ? '' : 'student-missing-value' }}">{{ $student->nis ?: 'NIS belum diisi' }}</span></div>
                                            <div><span class="mobile-data-item-label">Ekstrakurikuler</span><x-student.activity-badges :activities="$approvedActivities" :limit="1" /></div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                            <x-student.pagination :paginator="$joinedStudents" noun="siswa" class="mt-3" />
                        @else
                            <div class="empty-state">
                                <div class="icon"><i class="bi bi-people"></i></div>
                                <p class="mb-0">Belum ada siswa aktif yang sesuai dengan filter saat ini.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-12 col-xxl-5">
                <div class="surface-card h-100">
                    <div class="surface-card-header">Siswa yang belum mengikuti ekstrakurikuler</div>
                    <div class="surface-card-body">
                        @if($notJoinedStudents->count())
                            <div class="principal-compact-list">
                                @foreach($notJoinedStudents as $student)
                                    <div class="principal-compact-list__item">
                                        <x-student.identity :student="$student" :subtitle="($student->nis ?: 'NIS belum diisi') . ' | ' . ($student->class_name ?: 'Kelas belum diisi')" />
                                        <span class="badge badge-status-warning">Belum terdaftar</span>
                                    </div>
                                @endforeach
                            </div>
                            <x-student.pagination :paginator="$notJoinedStudents" noun="siswa" class="mt-3" />
                        @else
                            <div class="empty-state">
                                <div class="icon"><i class="bi bi-person-check"></i></div>
                                <p class="mb-0">Semua siswa pada filter ini sudah memiliki ekstrakurikuler.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($activeTab === 'attendance')
        <div class="row g-3">
            <div class="col-12 col-xl-7">
                <div class="surface-card h-100">
                    <div class="surface-card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
                        <span>Siswa dengan kehadiran rendah</span>
                        <a href="{{ $reportLinks['attendances'] }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-download"></i>Unduh presensi</a>
                    </div>
                    <div class="surface-card-body">
                        @if($lowAttendanceStudents->count())
                            <div class="principal-compact-list">
                                @foreach($lowAttendanceStudents as $row)
                                    <div class="principal-compact-list__item">
                                        <div>
                                            <strong>{{ $row['name'] }}</strong>
                                            <span>{{ $row['class_name'] }} | {{ $row['total_sessions'] }} pertemuan</span>
                                        </div>
                                        <span class="badge badge-status-danger">{{ number_format($row['attendance_rate'], 1) }}%</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state">
                                <div class="icon"><i class="bi bi-emoji-smile"></i></div>
                                <p class="mb-0">Belum ada siswa dengan tingkat kehadiran di bawah 75% pada periode ini.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-12 col-xl-5">
                <div class="surface-card h-100">
                    <div class="surface-card-header">Ekstrakurikuler yang perlu perhatian</div>
                    <div class="surface-card-body">
                        @if($lowActivityExtracurriculars->count())
                            <div class="principal-compact-list">
                                @foreach($lowActivityExtracurriculars as $row)
                                    <div class="principal-compact-list__item">
                                        <div>
                                            <strong>{{ $row['name'] }}</strong>
                                            <span>{{ $row['coach'] ?: 'Pembina belum tercatat' }}</span>
                                        </div>
                                        <div class="text-end">
                                            <strong class="d-block">{{ $row['schedule_count'] }} jadwal</strong>
                                            <span class="small text-muted">{{ $row['attendance_count'] }} absensi</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state">
                                <div class="icon"><i class="bi bi-calendar-check"></i></div>
                                <p class="mb-0">Belum ada unit yang terdeteksi minim kegiatan pada periode aktif ini.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($activeTab === 'achievements')
        <div class="surface-card">
            <div class="surface-card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
                <span>Monitoring prestasi</span>
                <a href="{{ $reportLinks['achievements'] }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-download"></i>Unduh prestasi</a>
            </div>
            <div class="surface-card-body">
                @if($achievementRows->count())
                    <div class="row g-3">
                        @foreach($achievementRows as $row)
                            <div class="col-12 col-xl-6">
                                <article class="principal-panel-card h-100">
                                    <div class="principal-panel-card__head">
                                        <div>
                                            <h3>{{ $row['title'] }}</h3>
                                            <p>{{ $row['extracurricular'] }}</p>
                                        </div>
                                        <span class="badge badge-status-secondary">{{ $row['level'] }}</span>
                                    </div>
                                    <div class="principal-metadata-grid">
                                        <div><span>Siswa</span><strong>{{ $row['student'] }}</strong></div>
                                        <div><span>Tanggal</span><strong>{{ $row['date'] }}</strong></div>
                                        <div><span>Hasil</span><strong>{{ $row['result'] }}</strong></div>
                                        <div><span>Dokumentasi</span><strong>{{ \Illuminate\Support\Str::limit($row['documentation'], 45) }}</strong></div>
                                    </div>
                                </article>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-3">{{ $achievementRows->links() }}</div>
                @else
                    <div class="empty-state">
                        <div class="icon"><i class="bi bi-award"></i></div>
                        <p class="mb-0">Belum ada data prestasi pada rentang waktu yang dipilih.</p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if($activeTab === 'agenda')
        <div class="row g-3">
            <div class="col-12 col-xl-6">
                <div class="surface-card h-100">
                    <div class="surface-card-header">Agenda terdekat</div>
                    <div class="surface-card-body">
                        @if($upcomingAgenda->count())
                            <div class="principal-timeline">
                                @foreach($upcomingAgenda as $row)
                                    <article class="principal-timeline__item">
                                        <span class="principal-timeline__dot"></span>
                                        <div>
                                            <div class="d-flex justify-content-between gap-2 flex-wrap align-items-start">
                                                <strong>{{ $row['title'] }}</strong>
                                                <span class="badge badge-status-secondary">{{ $row['type'] }}</span>
                                            </div>
                                            <div class="small text-muted">{{ $row['extracurricular'] }} | {{ $row['coach'] ?: 'Pembina belum tercatat' }}</div>
                                            <div class="small mt-1">{{ $row['date'] }} | {{ $row['time'] }} | {{ $row['location'] }}</div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state">
                                <div class="icon"><i class="bi bi-calendar-event"></i></div>
                                <p class="mb-0">Belum ada agenda mendatang yang tercatat setelah 23 Juli 2026.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-12 col-xl-6">
                <div class="surface-card h-100">
                    <div class="surface-card-header">Pengumuman penting</div>
                    <div class="surface-card-body">
                        @if($importantAnnouncements->count())
                            <div class="principal-compact-list">
                                @foreach($importantAnnouncements as $item)
                                    <div class="principal-compact-list__item align-items-start">
                                        <div>
                                            <strong>{{ $item->title }}</strong>
                                            <span>{{ $item->extracurricular->name ?? 'Informasi sekolah' }} | {{ optional($item->publish_at ?? $item->created_at)->translatedFormat('d M Y H:i') }}</span>
                                            <p class="small text-muted mb-0 mt-1">{{ \Illuminate\Support\Str::limit($item->content, 120) }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state">
                                <div class="icon"><i class="bi bi-megaphone"></i></div>
                                <p class="mb-0">Belum ada pengumuman aktif untuk ditampilkan.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="surface-card">
                    <div class="surface-card-header">Berita kegiatan terbaru</div>
                    <div class="surface-card-body">
                        @if($latestNews->count())
                            <div class="row g-3">
                                @foreach($latestNews as $news)
                                    <div class="col-12 col-lg-6 col-xxl-4">
                                        <article class="principal-news-card h-100">
                                            <span class="badge {{ $news['type'] === 'achievement' ? 'badge-status-success' : 'badge-status-secondary' }}">
                                                {{ $news['type'] === 'achievement' ? 'Prestasi' : ($news['type'] === 'article' ? 'Artikel' : 'Pengumuman') }}
                                            </span>
                                            <h3>{{ $news['title'] }}</h3>
                                            <p class="principal-news-card__subtitle">{{ $news['subtitle'] }}</p>
                                            <p class="principal-news-card__content">{{ $news['content'] }}</p>
                                            <small>{{ $news['date'] }}</small>
                                        </article>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state">
                                <div class="icon"><i class="bi bi-newspaper"></i></div>
                                <p class="mb-0">Belum ada berita atau update kegiatan terbaru.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($activeTab === 'reports')
        <div class="row g-3">
            @foreach([
                ['title' => 'Laporan data anggota', 'text' => 'Daftar anggota aktif per ekstrakurikuler, kelas, dan jenis kelamin.', 'link' => $reportLinks['members'], 'icon' => 'bi-people'],
                ['title' => 'Laporan pendaftaran', 'text' => 'Status diterima, ditolak, dan masih diperiksa.', 'link' => $reportLinks['registrations'], 'icon' => 'bi-clipboard-check'],
                ['title' => 'Laporan kehadiran', 'text' => 'Rekap hadir, izin, sakit, dan alpa.', 'link' => $reportLinks['attendances'], 'icon' => 'bi-check2-square'],
                ['title' => 'Laporan prestasi', 'text' => 'Daftar lomba, tingkat, hasil, dan dokumentasi prestasi.', 'link' => $reportLinks['achievements'], 'icon' => 'bi-award'],
                ['title' => 'Laporan kegiatan', 'text' => 'Agenda latihan, rapat, seleksi, dan lomba.', 'link' => $reportLinks['activities'], 'icon' => 'bi-calendar3'],
            ] as $report)
                <div class="col-12 col-md-6 col-xxl-4">
                    <div class="principal-report-tile h-100">
                        <span class="principal-report-tile__icon"><i class="bi {{ $report['icon'] }}"></i></span>
                        <h3>{{ $report['title'] }}</h3>
                        <p>{{ $report['text'] }}</p>
                        <a href="{{ $report['link'] }}" class="btn btn-outline-primary mt-auto"><i class="bi bi-arrow-right-circle"></i>Buka laporan</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
