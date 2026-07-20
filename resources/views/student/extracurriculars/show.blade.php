@extends('layouts.app')

@section('page_title', 'Detail Kegiatan')
@section('page_subtitle', 'Pelajari informasi lengkap sebelum mengirim pendaftaran.')

@push('styles')
    <style>
        .student-detail-hero,
        .student-detail-panel,
        .student-detail-aside-card {
            border-radius: 28px;
            border: 1px solid #dbe5f0;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.99), rgba(248, 251, 255, 0.95));
            box-shadow: 0 18px 32px rgba(16, 35, 63, 0.07);
        }

        .student-detail-hero {
            overflow: hidden;
        }

        .student-detail-media {
            position: relative;
            aspect-ratio: 16 / 9;
            background: #eef5ff;
        }

        .student-detail-media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .student-detail-body {
            padding: 1.35rem;
        }

        .student-detail-title-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .student-detail-summary-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.9rem;
        }

        .student-detail-summary-item {
            border-radius: 18px;
            border: 1px solid #e1ebf5;
            background: #fbfdff;
            padding: 0.95rem 1rem;
        }

        .student-detail-summary-item .label {
            display: block;
            font-size: 0.76rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6a7f98;
            margin-bottom: 0.35rem;
        }

        .student-detail-summary-item .value {
            color: #18334f;
            font-weight: 700;
            line-height: 1.45;
        }

        .student-detail-panel {
            padding: 1.2rem;
            height: 100%;
        }

        .student-detail-panel h2 {
            font-size: 1.05rem;
            margin-bottom: 0.45rem;
        }

        .student-detail-copy {
            color: #5d7088;
            line-height: 1.7;
            margin-bottom: 0;
        }

        .student-detail-empty {
            color: #6f8197;
            margin: 0;
        }

        .student-detail-list {
            display: grid;
            gap: 0.8rem;
            margin-top: 0.95rem;
        }

        .student-detail-list-item {
            border: 1px solid #e1ebf5;
            border-radius: 18px;
            padding: 0.95rem 1rem;
            background: #fbfdff;
        }

        .student-detail-list-item-title {
            font-weight: 800;
            color: #18334f;
            margin-bottom: 0.2rem;
        }

        .student-detail-list-item-meta {
            color: #607389;
            font-size: 0.9rem;
        }

        .student-detail-aside-card {
            padding: 1.2rem;
            position: sticky;
            top: 1rem;
        }

        .student-detail-aside-card .btn {
            width: 100%;
        }

        .student-detail-documentation {
            border-radius: 22px;
            overflow: hidden;
            border: 1px solid #dbe5f0;
            background: #eef5ff;
        }

        .student-detail-documentation img {
            width: 100%;
            aspect-ratio: 16 / 9;
            object-fit: cover;
            display: block;
        }

        @media (max-width: 991.98px) {
            .student-detail-summary-grid {
                grid-template-columns: 1fr;
            }

            .student-detail-aside-card {
                position: static;
            }
        }

        @media (max-width: 767.98px) {
            .student-detail-body,
            .student-detail-panel,
            .student-detail-aside-card {
                padding: 1rem;
            }

            .student-detail-title-row {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $firstSchedule = $extracurricular->schedules->sortBy('activity_date')->first();
        $latestSchedule = $extracurricular->schedules->sortByDesc('activity_date')->first();
        $location = $firstSchedule?->location ?: $latestSchedule?->location;
        $memberCount = $extracurricular->participants_count ?? 0;
        $quota = $extracurricular->quota ?? $extracurricular->member_quota ?? $extracurricular->capacity ?? null;
        $memberText = $quota ? "{$memberCount} / {$quota} siswa" : ($memberCount > 0 ? "{$memberCount} anggota aktif" : 'Jumlah anggota belum tersedia.');
        $coachText = $extracurricular->coach_names === 'Belum tersedia' ? 'Pembina belum ditentukan.' : $extracurricular->coach_names;
        $branchOptions = collect($extracurricular->branch_options ?? [])->filter()->values();
        $statusLabel = match (strtolower((string) $registration?->status)) {
            'pending' => 'Menunggu Konfirmasi',
            'approved' => 'Sudah Mendaftar',
            'rejected' => 'Pendaftaran Ditolak',
            default => null,
        };
    @endphp

    <div class="split-actions mb-3">
        <a href="{{ route('student.extracurriculars.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i>Kembali ke Daftar Kegiatan</a>
        @if($registration)
            <a href="{{ route('student.registrations.index') }}" class="btn btn-outline-primary"><i class="bi bi-clipboard-check"></i>Lihat Status Pendaftaran</a>
        @elseif($extracurricular->is_active)
            <a href="{{ route('student.extracurriculars.register', $extracurricular) }}" class="btn btn-primary"><i class="bi bi-send-check"></i>Daftar Kegiatan Ini</a>
        @else
            <button type="button" class="btn btn-outline-secondary" disabled><i class="bi bi-lock"></i>Pendaftaran Ditutup</button>
        @endif
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-8">
            <section class="student-detail-hero mb-3">
                <div class="student-detail-media">
                    <img src="{{ $extracurricular->preview_image }}" alt="{{ $extracurricular->name }}" loading="eager" decoding="async">
                </div>
                <div class="student-detail-body">
                    <div class="section-kicker"><i class="bi bi-grid-1x2"></i>Detail {{ $extracurricular->category_label }}</div>
                    <div class="student-detail-title-row">
                        <div>
                            <h1 class="h3 mb-2">{{ $extracurricular->name }}</h1>
                            <p class="student-detail-copy">Baca informasi berikut terlebih dahulu agar kamu bisa menilai kecocokan kegiatan sebelum mendaftar.</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge {{ $extracurricular->is_active ? 'badge-status-success' : 'badge-status-secondary' }}">
                                {{ $extracurricular->is_active ? 'Aktif' : 'Pendaftaran Ditutup' }}
                            </span>
                            <span class="badge badge-status-secondary">{{ $extracurricular->category_label }}</span>
                            @if($statusLabel)
                                <span class="badge {{ strtolower((string) $registration?->status) === 'approved' ? 'badge-status-success' : (strtolower((string) $registration?->status) === 'rejected' ? 'badge-status-danger' : 'badge-status-warning') }}">
                                    {{ $statusLabel }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="student-detail-summary-grid">
                        <div class="student-detail-summary-item">
                            <span class="label">Pembina</span>
                            <div class="value">{{ $coachText }}</div>
                        </div>
                        <div class="student-detail-summary-item">
                            <span class="label">Jadwal latihan</span>
                            <div class="value">{{ $extracurricular->schedule_overview ?: 'Jadwal latihan belum tersedia.' }}</div>
                        </div>
                        <div class="student-detail-summary-item">
                            <span class="label">Lokasi</span>
                            <div class="value">{{ $location ?: 'Lokasi belum tersedia.' }}</div>
                        </div>
                        <div class="student-detail-summary-item">
                            <span class="label">Anggota / Kuota</span>
                            <div class="value">{{ $memberText }}</div>
                        </div>
                        @if($branchOptions->isNotEmpty())
                            <div class="student-detail-summary-item">
                                <span class="label">Pilihan cabang</span>
                                <div class="value">{{ $branchOptions->implode(', ') }}</div>
                            </div>
                        @endif
                        @if($registration?->selected_branch)
                            <div class="student-detail-summary-item">
                                <span class="label">Cabang yang dipilih</span>
                                <div class="value">{{ $registration->selected_branch }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </section>

            <div class="row g-3">
                <div class="col-12">
                    <section class="student-detail-panel">
                        <h2>Deskripsi</h2>
                        <p class="student-detail-copy">{{ $extracurricular->description }}</p>
                    </section>
                </div>

                <div class="col-12 col-lg-6">
                    <section class="student-detail-panel">
                        <h2>Syarat</h2>
                        @if($extracurricular->requirements)
                            <p class="student-detail-copy">{{ $extracurricular->requirements }}</p>
                        @else
                            <p class="student-detail-empty">Belum ada syarat khusus yang dipublikasikan.</p>
                        @endif
                    </section>
                </div>

                <div class="col-12 col-lg-6">
                    <section class="student-detail-panel">
                        <h2>Pembina</h2>
                        @if($extracurricular->coach_names !== 'Belum tersedia')
                            <p class="student-detail-copy">{{ $coachText }}</p>
                        @else
                            <p class="student-detail-empty">Pembina belum ditentukan.</p>
                        @endif
                    </section>
                </div>

                <div class="col-12">
                    <section class="student-detail-panel">
                        <h2>Kegiatan dan jadwal latihan</h2>
                        @if($extracurricular->schedules->isNotEmpty())
                            <div class="student-detail-list">
                                @foreach($extracurricular->schedules->sortBy('activity_date') as $schedule)
                                    <div class="student-detail-list-item">
                                        <div class="student-detail-list-item-title">{{ $schedule->title }}</div>
                                        <div class="student-detail-list-item-meta">
                                            {{ optional($schedule->activity_date)->format('d-m-Y') ?: 'Tanggal belum tersedia' }}
                                            @if($schedule->start_time)
                                                | {{ \Illuminate\Support\Str::substr($schedule->start_time, 0, 5) }}{{ $schedule->end_time ? ' - '.\Illuminate\Support\Str::substr($schedule->end_time, 0, 5) : '' }}
                                            @endif
                                            | {{ $schedule->location ?: 'Lokasi belum tersedia.' }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="student-detail-empty">Jadwal latihan belum tersedia.</p>
                        @endif
                    </section>
                </div>

                <div class="col-12">
                    <section class="student-detail-panel">
                        <h2>Prestasi</h2>
                        @if($extracurricular->achievements->isNotEmpty())
                            <div class="student-detail-list">
                                @foreach($extracurricular->achievements as $achievement)
                                    <div class="student-detail-list-item">
                                        <div class="student-detail-list-item-title">{{ $achievement->title }}</div>
                                        <div class="student-detail-list-item-meta">
                                            {{ optional($achievement->achievement_date)->format('d-m-Y') ?: 'Tanggal belum tersedia' }}
                                        </div>
                                        @if($achievement->description)
                                            <p class="student-detail-copy mt-2">{{ $achievement->description }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="student-detail-empty">Belum ada prestasi yang dipublikasikan.</p>
                        @endif
                    </section>
                </div>

                <div class="col-12">
                    <section class="student-detail-panel">
                        <h2>Dokumentasi</h2>
                        @if($extracurricular->image_path)
                            <div class="student-detail-documentation">
                                <img src="{{ $extracurricular->preview_image }}" alt="{{ $extracurricular->name }}" loading="lazy" decoding="async">
                            </div>
                        @else
                            <p class="student-detail-empty">Dokumentasi kegiatan belum tersedia.</p>
                        @endif
                    </section>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <aside class="student-detail-aside-card">
                <div class="section-kicker"><i class="bi bi-send-check"></i>Aksi Pendaftaran</div>
                <h2 class="h5 mb-2">Siap bergabung?</h2>
                <p class="student-detail-copy mb-3">Pastikan informasi kegiatan ini sudah sesuai dengan minatmu sebelum melanjutkan ke halaman pendaftaran.</p>

                <div class="student-detail-list mb-3">
                    <div class="student-detail-list-item">
                        <div class="student-detail-list-item-title">Status</div>
                        <div class="student-detail-list-item-meta">{{ $extracurricular->is_active ? 'Pendaftaran tersedia' : 'Pendaftaran sedang ditutup' }}</div>
                    </div>
                    <div class="student-detail-list-item">
                        <div class="student-detail-list-item-title">Jadwal singkat</div>
                        <div class="student-detail-list-item-meta">{{ $extracurricular->schedule_overview ?: 'Jadwal latihan belum tersedia.' }}</div>
                    </div>
                    <div class="student-detail-list-item">
                        <div class="student-detail-list-item-title">Pembina</div>
                        <div class="student-detail-list-item-meta">{{ $coachText }}</div>
                    </div>
                </div>

                @if($registration)
                    <a href="{{ route('student.registrations.index') }}" class="btn btn-outline-primary"><i class="bi bi-clipboard-check"></i>Lihat Status Pendaftaran</a>
                @elseif($extracurricular->is_active)
                    <a href="{{ route('student.extracurriculars.register', $extracurricular) }}" class="btn btn-primary"><i class="bi bi-send-check"></i>Daftar Kegiatan Ini</a>
                @else
                    <button type="button" class="btn btn-outline-secondary" disabled><i class="bi bi-lock"></i>Pendaftaran Ditutup</button>
                @endif
            </aside>
        </div>
    </div>
@endsection
