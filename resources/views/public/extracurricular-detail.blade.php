@extends('layouts.public')

@section('title', 'Detail ' . $extracurricular->category_label . ' | ' . $extracurricular->name)

@push('styles')
    <style>
        .detail-hero {
            border-radius: 32px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(237, 245, 255, 0.96) 100%);
            border: 1px solid #d8e4f2;
            padding: 1.3rem;
            margin: 1.25rem 0 1.5rem;
            box-shadow: 0 24px 40px rgba(16, 35, 63, 0.08);
        }

        .detail-cover {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            border-radius: 22px;
        }

        .detail-cover-frame {
            background: linear-gradient(135deg, #eef5ff 0%, #f8fbff 100%);
            border: 1px solid #dbe5f0;
            border-radius: 28px;
            padding: 0.85rem;
            box-shadow: 0 18px 30px rgba(16, 35, 63, 0.08);
        }

        .detail-cover-box {
            aspect-ratio: 4 / 3;
            overflow: hidden;
            border-radius: 22px;
            background: #f4f8ff;
        }

        .detail-default-visual {
            width: 100%;
            height: 100%;
            position: relative;
            overflow: hidden;
            border-radius: 22px;
            background: linear-gradient(135deg, #dfeeff 0%, #eef5ff 50%, #d9e9ff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .detail-default-visual::before,
        .detail-default-visual::after {
            content: "";
            position: absolute;
            border-radius: 999px;
            background: rgba(79, 124, 196, 0.1);
        }

        .detail-default-visual::before {
            width: 150px;
            height: 150px;
            top: -46px;
            right: -28px;
        }

        .detail-default-visual::after {
            width: 120px;
            height: 120px;
            bottom: -36px;
            left: -22px;
        }

        .detail-default-visual-inner {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 1rem;
            width: 100%;
            padding: 0 1.25rem;
        }

        .detail-default-visual-icon {
            width: 4rem;
            height: 4rem;
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.76);
            color: #355987;
            font-size: 1.7rem;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.45);
            flex-shrink: 0;
        }

        .detail-default-visual-label {
            display: block;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 800;
            color: #5d789a;
            margin-bottom: 0.25rem;
        }

        .detail-default-visual-title {
            display: block;
            color: #23446f;
            font-size: 1.2rem;
            font-weight: 800;
            line-height: 1.25;
        }

        .detail-summary-list {
            display: grid;
            gap: 0.9rem;
        }

        .detail-info-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        }

        .detail-info-card {
            border: 1px solid #dbe5f0;
            border-radius: 22px;
            padding: 1rem 1.05rem;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.99), rgba(244, 248, 255, 0.95));
            box-shadow: 0 12px 24px rgba(16, 35, 63, 0.05);
        }

        .detail-info-card .label {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.76rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-weight: 800;
            color: #5f7897;
            margin-bottom: 0.45rem;
        }

        .detail-info-card .value {
            color: #173556;
            font-weight: 700;
            line-height: 1.5;
        }

        .detail-cta-card {
            border-radius: 28px;
            background: linear-gradient(135deg, #0f2f57 0%, #1f5eff 100%);
            color: #fff;
            padding: 1.2rem;
            box-shadow: 0 24px 38px rgba(31, 94, 255, 0.18);
        }

        .detail-cta-card p {
            color: rgba(239, 246, 255, 0.88);
        }

        .achievement-list {
            display: grid;
            gap: 0.9rem;
            margin-top: 1rem;
        }

        .achievement-item {
            border: 1px solid #dbe5f0;
            border-radius: 20px;
            padding: 1rem 1.1rem;
            background: linear-gradient(135deg, #f8fbff 0%, #eef5ff 100%);
        }

        .achievement-item.fallback {
            background: linear-gradient(135deg, #ffffff 0%, #f5f9ff 100%);
        }

        .achievement-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.65rem 1rem;
            margin-top: 0.55rem;
            font-size: 0.92rem;
            color: #5a6f8d;
        }

        .achievement-meta span {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .detail-announcement-card {
            border: 1px solid #dbe5f0;
            border-radius: 20px;
            padding: 1rem 1.05rem;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.99), rgba(246, 250, 255, 0.95));
            box-shadow: 0 12px 24px rgba(16, 35, 63, 0.04);
            height: 100%;
        }

        @media (max-width: 991.98px) {
            .detail-cover-frame {
                max-width: 560px;
                margin: 0 auto;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container py-3 py-md-4">
        @php
            $user = auth()->user();
            $isStudent = $user?->hasRole(\App\Models\User::ROLE_STUDENT) ?? false;
            $firstSchedule = $extracurricular->schedules->first();
            $achievements = $extracurricular->achievements;
            $branchOptions = collect($extracurricular->branch_options ?? [])->filter()->values();
            $location = $firstSchedule?->location ?: 'Belum ditentukan';
            $quota = $extracurricular->quota ? $extracurricular->quota . ' peserta' : 'Belum ditentukan';
            $normalizedName = \Illuminate\Support\Str::lower(trim($extracurricular->name));
            $visualMap = [
                'pramuka' => ['icon' => 'bi-tree', 'label' => 'Kegiatan lapangan'],
                'paskibra' => ['icon' => 'bi-flag', 'label' => 'Latihan disiplin'],
                'pbb/paskib' => ['icon' => 'bi-flag', 'label' => 'Latihan disiplin'],
                'pmr' => ['icon' => 'bi-heart-pulse', 'label' => 'Kegiatan sosial'],
                'basket' => ['icon' => 'bi-dribbble', 'label' => 'Latihan olahraga'],
                'basketball' => ['icon' => 'bi-dribbble', 'label' => 'Latihan olahraga'],
                'futsal' => ['icon' => 'bi-trophy', 'label' => 'Latihan olahraga'],
                'rohis' => ['icon' => 'bi-moon-stars', 'label' => 'Pembinaan rohani'],
                "tilawatil qur'an" => ['icon' => 'bi-book', 'label' => 'Pembinaan keagamaan'],
                "tartil dan hifzil qur'an" => ['icon' => 'bi-book', 'label' => 'Pembinaan keagamaan'],
                'konten kreator' => ['icon' => 'bi-camera-video', 'label' => 'Kegiatan media'],
                'menulis artikel' => ['icon' => 'bi-pencil-square', 'label' => 'Kegiatan literasi'],
                'opsi' => ['icon' => 'bi-lightbulb', 'label' => 'Kegiatan akademik'],
                'osis / mpk' => ['icon' => 'bi-people', 'label' => 'Kegiatan organisasi'],
                'pelsis' => ['icon' => 'bi-people', 'label' => 'Kegiatan organisasi'],
                'smag' => ['icon' => 'bi-people', 'label' => 'Kegiatan organisasi'],
                'fortina' => ['icon' => 'bi-megaphone', 'label' => 'Kegiatan komunikasi'],
            ];
            $visual = $visualMap[$normalizedName] ?? ['icon' => 'bi-stars', 'label' => 'Kegiatan siswa'];
        @endphp

        <div class="split-actions mb-3">
            <a href="{{ $backToActivitiesUrl ?? route('public.activities.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i>Kembali ke Kategori</a>
            <a href="{{ route('public.information') }}" class="btn btn-outline-primary"><i class="bi bi-signpost-2"></i>Lihat Alur Pendaftaran</a>
        </div>

        <section class="detail-hero">
            <div class="row g-4 align-items-center">
                <div class="col-lg-7">
                    <span class="section-kicker"><i class="bi bi-grid-1x2"></i>Detail {{ $extracurricular->category_label }}</span>
                    <h1 class="section-title">{{ $extracurricular->name }}</h1>
                    <p class="section-subtitle mb-3">Baca informasi berikut terlebih dahulu agar kamu yakin sebelum mengirim pendaftaran.</p>

                    <div class="catalog-card-meta mb-3">
                        <span><i class="bi bi-bookmark-star"></i>{{ $extracurricular->category_label }}</span>
                        <span><i class="bi bi-circle-fill"></i>{{ $extracurricular->is_active ? 'Aktif' : 'Tidak Aktif' }}</span>
                    </div>

                    <div class="detail-summary-list">
                        <div class="dashboard-highlight">
                            <span class="dashboard-highlight-icon"><i class="bi bi-person-workspace"></i></span>
                            <div class="dashboard-highlight-copy">
                                <h3>Pembina</h3>
                                <p>{{ $extracurricular->coach_names }}</p>
                            </div>
                        </div>
                        <div class="dashboard-highlight">
                            <span class="dashboard-highlight-icon"><i class="bi bi-calendar3"></i></span>
                            <div class="dashboard-highlight-copy">
                                <h3>Deskripsi dan jadwal</h3>
                                <p class="mb-2">{{ $extracurricular->description }}</p>
                                <p>
                                    @if($extracurricular->schedule_overview)
                                        {{ $extracurricular->schedule_overview }}
                                    @elseif($firstSchedule)
                                        {{ $firstSchedule->title }} - {{ optional($firstSchedule->activity_date)->format('d-m-Y') }} di {{ $firstSchedule->location }}
                                    @else
                                        Jadwal latihan belum tersedia.
                                    @endif
                                </p>
                            </div>
                        </div>
                        @if($branchOptions->isNotEmpty())
                            <div class="dashboard-highlight">
                                <span class="dashboard-highlight-icon"><i class="bi bi-diagram-3"></i></span>
                                <div class="dashboard-highlight-copy">
                                    <h3>Pilihan cabang</h3>
                                    <p>{{ $branchOptions->implode(', ') }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="detail-cover-frame">
                        <div class="detail-cover-box">
                            @if(!empty($extracurricular->image_path))
                                <img src="{{ $extracurricular->preview_image }}" alt="{{ $extracurricular->name }}" class="detail-cover" decoding="async" fetchpriority="high">
                            @else
                                <div class="detail-default-visual" aria-hidden="true">
                                    <div class="detail-default-visual-inner">
                                        <span class="detail-default-visual-icon"><i class="bi {{ $visual['icon'] }}"></i></span>
                                        <div>
                                            <span class="detail-default-visual-label">{{ $visual['label'] }}</span>
                                            <span class="detail-default-visual-title">{{ $extracurricular->name }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="row g-3 mb-3">
            <div class="col-lg-12">
                <div class="detail-panel">
                    <span class="section-kicker"><i class="bi bi-info-circle"></i>Informasi Utama</span>
                    <h3>Rincian kegiatan</h3>
                    <p class="mb-3">Semua informasi inti kegiatan dikumpulkan di sini agar siswa tidak perlu membaca kartu yang terlalu panjang.</p>

                    <div class="detail-info-grid">
                        <div class="detail-info-card">
                            <div class="label"><i class="bi bi-bookmark-star"></i>Kategori</div>
                            <div class="value">{{ $extracurricular->category_label }}</div>
                        </div>
                        <div class="detail-info-card">
                            <div class="label"><i class="bi bi-circle-fill"></i>Status</div>
                            <div class="value">{{ $extracurricular->is_active ? 'Pendaftaran Dibuka' : 'Pendaftaran Ditutup' }}</div>
                        </div>
                        <div class="detail-info-card">
                            <div class="label"><i class="bi bi-person-workspace"></i>Pembina</div>
                            <div class="value">{{ $extracurricular->coach_names }}</div>
                        </div>
                        <div class="detail-info-card">
                            <div class="label"><i class="bi bi-geo-alt"></i>Lokasi</div>
                            <div class="value">{{ $location }}</div>
                        </div>
                        <div class="detail-info-card">
                            <div class="label"><i class="bi bi-people"></i>Kuota</div>
                            <div class="value">{{ $quota }}</div>
                        </div>
                        <div class="detail-info-card">
                            <div class="label"><i class="bi bi-card-checklist"></i>Syarat</div>
                            <div class="value">Login sebagai siswa lalu lengkapi data pendaftaran sesuai instruksi pembina atau admin.</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="detail-panel">
                    <span class="section-kicker"><i class="bi bi-award"></i>Prestasi</span>
                    <h3>Daftar prestasi kegiatan</h3>
                    <p class="mb-0">Prestasi di bawah ini merupakan capaian kegiatan yang diinput admin.</p>

                    @if($achievements->isNotEmpty())
                        <div class="achievement-list">
                            @foreach($achievements as $achievement)
                                <div class="achievement-item">
                                    <h4 class="h6 mb-1">{{ $achievement->title }}</h4>
                                    @if($achievement->description)
                                        <p class="mb-0">{{ $achievement->description }}</p>
                                    @endif
                                    <div class="achievement-meta">
                                        <span><i class="bi bi-calendar-event"></i>{{ optional($achievement->achievement_date)->format('d-m-Y') ?: '-' }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state py-3">
                            <div class="icon"><i class="bi bi-award"></i></div>
                            <p class="mb-0">Belum ada prestasi kegiatan yang ditampilkan.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-header">Jadwal Latihan</div>
                    <div class="card-body">
                        @if($extracurricular->schedules->isNotEmpty())
                            <div class="desktop-table table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                    <tr>
                                        <th>Kegiatan</th>
                                        <th>Tanggal</th>
                                        <th>Jam</th>
                                        <th>Lokasi</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($extracurricular->schedules as $schedule)
                                        <tr>
                                            <td>{{ $schedule->title }}</td>
                                            <td>{{ optional($schedule->activity_date)->format('d-m-Y') }}</td>
                                            <td>{{ \Illuminate\Support\Str::substr($schedule->start_time, 0, 5) }} - {{ \Illuminate\Support\Str::substr($schedule->end_time, 0, 5) }}</td>
                                            <td>{{ $schedule->location ?: 'Belum ditentukan' }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mobile-stack-table">
                                @foreach($extracurricular->schedules as $schedule)
                                    <div class="mobile-data-card">
                                        <div class="mobile-data-card-header">
                                            <h3 class="mobile-data-card-title">{{ $schedule->title }}</h3>
                                        </div>
                                        <div class="mobile-data-list">
                                            <div><span class="mobile-data-item-label">Tanggal</span><p class="mobile-data-item-value">{{ optional($schedule->activity_date)->format('d-m-Y') }}</p></div>
                                            <div><span class="mobile-data-item-label">Jam</span><p class="mobile-data-item-value">{{ \Illuminate\Support\Str::substr($schedule->start_time, 0, 5) }} - {{ \Illuminate\Support\Str::substr($schedule->end_time, 0, 5) }}</p></div>
                                            <div><span class="mobile-data-item-label">Lokasi</span><p class="mobile-data-item-value">{{ $schedule->location ?: 'Belum ditentukan' }}</p></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state py-3">
                                <div class="icon"><i class="bi bi-calendar3"></i></div>
                                <p class="mb-0">Jadwal latihan belum tersedia.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="detail-cta-card h-100">
                    <div class="small text-white-50 mb-2">Langkah berikutnya</div>
                    <h3 class="h5">Siap mendaftar ke kegiatan {{ strtolower($extracurricular->category_label) }} ini?</h3>
                    <p class="mb-3">Form pendaftaran lengkap tidak ditampilkan di halaman ini. Setelah login, kamu akan diarahkan ke halaman pendaftaran terpisah.</p>
                    <div class="d-grid gap-2">
                        @if(!$user)
                            <a href="{{ route('public.extracurriculars.register', $extracurricular) }}" class="btn btn-light text-primary"><i class="bi bi-send-check"></i>Daftar Kegiatan Ini</a>
                            <a href="{{ route('login') }}" class="btn btn-outline-light"><i class="bi bi-box-arrow-in-right"></i>Masuk sebagai Siswa</a>
                            <a href="{{ route('register') }}" class="btn btn-outline-light"><i class="bi bi-person-plus"></i>Buat Akun Siswa</a>
                        @elseif($isStudent)
                            <a href="{{ route('student.extracurriculars.register', $extracurricular) }}" class="btn btn-light text-primary"><i class="bi bi-send-check"></i>Daftar Kegiatan Ini</a>
                        @else
                            <a href="{{ route('dashboard') }}" class="btn btn-light text-primary"><i class="bi bi-arrow-right-circle"></i>Kembali ke Dashboard</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-12">
                <div class="detail-panel">
                    <span class="section-kicker"><i class="bi bi-megaphone"></i>Pengumuman Terkait</span>
                    <h3>Informasi terbaru untuk kegiatan ini</h3>
                    <p class="mb-3">Pengumuman terkait kegiatan dipusatkan di halaman detail agar beranda tetap ringkas.</p>

                    @if($relatedAnnouncements->isNotEmpty())
                        <div class="row g-3">
                            @foreach($relatedAnnouncements as $announcement)
                                <div class="col-12 col-lg-4">
                                    <article class="detail-announcement-card">
                                        <div class="small text-muted mb-2">{{ optional($announcement->created_at)->format('d-m-Y') }}</div>
                                        <h4 class="h6 mb-2">{{ $announcement->title }}</h4>
                                        <p class="mb-0 text-muted">{{ \Illuminate\Support\Str::limit($announcement->content, 160) }}</p>
                                    </article>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state py-3">
                            <div class="icon"><i class="bi bi-megaphone"></i></div>
                            <p class="mb-0">Belum ada pengumuman terkait kegiatan ini.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
