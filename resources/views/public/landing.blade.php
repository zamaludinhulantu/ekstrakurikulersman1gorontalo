@extends('layouts.public')

@section('title', 'Sistem Informasi Ekstrakurikuler SMA Negeri 1 Gorontalo')

@push('styles')
    <style>
        .hero-section {
            position: relative;
            overflow: hidden;
            border-radius: 36px;
            background:
                radial-gradient(circle at top right, rgba(255, 255, 255, 0.18) 0%, rgba(255, 255, 255, 0) 28%),
                linear-gradient(135deg, #0f2f57 0%, #1f5eff 52%, #78c4ff 100%);
            color: #fff;
            padding: 1.5rem;
            margin: 1.25rem 0 1.5rem;
            box-shadow: 0 28px 48px rgba(16, 35, 63, 0.18);
        }

        .hero-section::before,
        .hero-section::after {
            content: "";
            position: absolute;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.1);
        }

        .hero-section::before {
            width: 260px;
            height: 260px;
            top: -120px;
            right: -60px;
        }

        .hero-section::after {
            width: 180px;
            height: 180px;
            bottom: -70px;
            left: -40px;
        }

        .hero-panel,
        .hero-preview {
            position: relative;
            z-index: 1;
        }

        .hero-title {
            margin: 0 0 0.9rem;
            font-size: clamp(2rem, 4vw, 3.35rem);
            line-height: 1.08;
            font-weight: 900;
            letter-spacing: -0.04em;
        }

        .hero-text {
            max-width: 680px;
            color: rgba(239, 246, 255, 0.9);
            font-size: 1rem;
            margin-bottom: 1.3rem;
        }

        .hero-step-card {
            border-radius: 28px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.16);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(10px);
        }

        .hero-step-card {
            padding: 1rem;
        }

        .hero-step-list {
            display: grid;
            gap: 0.7rem;
        }

        .hero-step-item {
            display: flex;
            gap: 0.75rem;
            align-items: flex-start;
        }

        .hero-step-item span {
            width: 2rem;
            height: 2rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.18);
            font-size: 0.82rem;
            font-weight: 800;
            flex-shrink: 0;
        }

        .catalog-card {
            height: 100%;
            border-radius: 20px;
            border: 1px solid #dbe5f0;
            background: #fff;
            box-shadow: 0 10px 18px rgba(16, 35, 63, 0.05);
        }

        .catalog-card-media {
            padding: 0.95rem 0.95rem 0;
        }

        .catalog-card-media img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            display: block;
            border-radius: 14px;
            border: 1px solid #dbe5f0;
        }

        .catalog-card-default-visual {
            height: 120px;
            border-radius: 14px;
            width: 100%;
            overflow: hidden;
            border: 1px solid #dbe5f0;
            position: relative;
            background: linear-gradient(135deg, #dfeeff 0%, #eef5ff 50%, #d9e9ff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .catalog-card-default-visual::before,
        .catalog-card-default-visual::after {
            content: "";
            position: absolute;
            border-radius: 999px;
            background: rgba(79, 124, 196, 0.1);
        }

        .catalog-card-default-visual::before {
            width: 110px;
            height: 110px;
            top: -36px;
            right: -24px;
        }

        .catalog-card-default-visual::after {
            width: 90px;
            height: 90px;
            bottom: -30px;
            left: -18px;
        }

        .catalog-card-visual-inner {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            width: 100%;
            padding: 0 1rem;
        }

        .catalog-card-visual-icon {
            width: 3.2rem;
            height: 3.2rem;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.72);
            color: #355987;
            font-size: 1.4rem;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.45);
        }

        .catalog-card-visual-text {
            min-width: 0;
        }

        .catalog-card-visual-label {
            display: block;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 800;
            color: #5d789a;
            margin-bottom: 0.2rem;
        }

        .catalog-card-visual-title {
            display: block;
            color: #23446f;
            font-size: 0.98rem;
            font-weight: 800;
            line-height: 1.25;
        }

        .catalog-card-body {
            padding: 0.95rem;
        }

        .catalog-card-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.45rem;
            margin-bottom: 0.7rem;
        }

        .catalog-card-meta span {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.36rem 0.62rem;
            border-radius: 999px;
            background: #eef5ff;
            color: #355987;
            font-size: 0.74rem;
            font-weight: 700;
        }

        .catalog-card-title {
            margin: 0 0 0.2rem;
            font-size: 1.02rem;
            font-weight: 800;
        }

        .catalog-card-info {
            display: grid;
            gap: 0.45rem;
            margin: 0.65rem 0;
        }

        .catalog-card-info-item {
            padding: 0.55rem 0.7rem;
            border-radius: 10px;
            background: #f8fbff;
            border: 1px solid #e2ecf6;
        }

        .catalog-card-info-item strong {
            display: block;
            margin-bottom: 0.1rem;
            font-size: 0.7rem;
            color: #48607b;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .catalog-card-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.65rem;
        }

        .featured-banner {
            border-radius: 28px;
            padding: 1.2rem;
            background: linear-gradient(135deg, #0f2f57 0%, #255fff 100%);
            color: #fff;
            box-shadow: 0 24px 36px rgba(31, 94, 255, 0.18);
        }

        @media (max-width: 767.98px) {
            .hero-section {
                padding: 1.15rem;
                border-radius: 28px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container py-3 py-md-4">
        <section class="hero-section">
            <div class="row g-4 align-items-center">
                <div class="col-lg-7">
                    <div class="hero-panel">
                        <span class="badge bg-white text-primary px-3 py-2 mb-3">Portal Pendaftaran Ekstrakurikuler</span>
                        <h1 class="hero-title">Temukan ekstrakurikuler yang sesuai dengan minatmu.</h1>
                        <p class="hero-text">
                            Siswa baru bisa melihat daftar ekskul, membaca informasi penting, memahami alur pendaftaran,
                            lalu mendaftar secara online dengan langkah yang jelas dari awal sampai selesai.
                        </p>
                        <div class="hero-actions">
                            <a href="#daftar-ekskul" class="btn btn-light text-primary"><i class="bi bi-grid-3x3-gap"></i>Lihat Ekstrakurikuler</a>
                            <a href="{{ route('register') }}" class="btn btn-primary"><i class="bi bi-person-plus"></i>Daftar Sekarang</a>
                            <a href="{{ route('public.information') }}" class="btn btn-outline-light"><i class="bi bi-signpost-2"></i>Lihat Alur</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="hero-preview">
                        <div class="hero-step-card">
                            <div class="small text-white-50 mb-3">Langkah untuk siswa baru</div>
                            <div class="hero-step-list">
                                <div class="hero-step-item"><span>1</span><div>Lihat daftar ekstrakurikuler yang tersedia.</div></div>
                                <div class="hero-step-item"><span>2</span><div>Pilih ekskul yang sesuai minat dan baca detailnya.</div></div>
                                <div class="hero-step-item"><span>3</span><div>Isi formulir pendaftaran dari akun siswa.</div></div>
                                <div class="hero-step-item"><span>4</span><div>Tunggu konfirmasi pembina atau admin.</div></div>
                                <div class="hero-step-item"><span>5</span><div>Ikuti jadwal latihan yang sudah diumumkan.</div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="daftar-ekskul" class="mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-3">
                <div>
                    <span class="section-kicker"><i class="bi bi-stars"></i>Daftar Ekstrakurikuler</span>
                    <h2 class="section-title">Pilih kegiatan yang paling sesuai</h2>
                    <p class="section-subtitle mb-0">Setiap kartu menampilkan informasi singkat yang paling dibutuhkan siswa baru sebelum mendaftar.</p>
                </div>
                <div class="featured-banner">
                    <div class="small text-white-50">Total pilihan aktif</div>
                    <div class="h2 mb-1">{{ $extracurriculars->count() }}</div>
                    <div class="small text-white-50">Siap dijelajahi secara online</div>
                </div>
            </div>

            @if($usesDummyExtracurriculars)
                <div class="alert alert-info mb-3">
                    Data ekstrakurikuler di bawah ini masih berupa contoh tampilan. Data akan otomatis menyesuaikan saat admin menambahkan ekskul di sistem.
                </div>
            @endif

            <div class="row g-3">
                @forelse($extracurriculars as $extracurricular)
                    @php
                        $firstSchedule = $extracurricular->schedules->first();
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
                            'opsis' => ['icon' => 'bi-lightbulb', 'label' => 'Kegiatan akademik'],
                            'osis / mpk' => ['icon' => 'bi-people', 'label' => 'Kegiatan organisasi'],
                            'pelsis' => ['icon' => 'bi-people', 'label' => 'Kegiatan organisasi'],
                            'smag' => ['icon' => 'bi-people', 'label' => 'Kegiatan organisasi'],
                            'fortina' => ['icon' => 'bi-megaphone', 'label' => 'Kegiatan komunikasi'],
                        ];
                        $visual = $visualMap[$normalizedName] ?? ['icon' => 'bi-stars', 'label' => 'Kegiatan siswa'];
                    @endphp
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="catalog-card">
                            <div class="catalog-card-media">
                                @if(!empty($extracurricular->image_path))
                                    <img src="{{ $extracurricular->preview_image }}" alt="{{ $extracurricular->name }}">
                                @else
                                    <div class="catalog-card-default-visual" aria-hidden="true">
                                        <div class="catalog-card-visual-inner">
                                            <span class="catalog-card-visual-icon"><i class="bi {{ $visual['icon'] }}"></i></span>
                                            <div class="catalog-card-visual-text">
                                                <span class="catalog-card-visual-label">{{ $visual['label'] }}</span>
                                                <span class="catalog-card-visual-title">{{ $extracurricular->name }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="catalog-card-body">
                                <div class="catalog-card-meta">
                                    <span><i class="bi bi-circle-fill"></i>{{ $extracurricular->is_active ? 'Aktif' : 'Tidak Aktif' }}</span>
                                </div>
                                <h3 class="catalog-card-title">{{ $extracurricular->name }}</h3>

                                <div class="catalog-card-info">
                                    <div class="catalog-card-info-item">
                                        <strong>Pembina</strong>
                                        <div>{{ $extracurricular->coach_names }}</div>
                                    </div>
                                    <div class="catalog-card-info-item">
                                        <strong>Jadwal latihan</strong>
                                        <div>
                                            @if($extracurricular->schedule_overview)
                                                {{ $extracurricular->schedule_overview }}
                                            @elseif($firstSchedule)
                                                {{ $firstSchedule->title }} - {{ optional($firstSchedule->activity_date)->format('d-m-Y') }}
                                            @else
                                                Jadwal belum ditentukan
                                            @endif
                                        </div>
                                    </div>
                                    <div class="catalog-card-info-item">
                                        <strong>Prestasi terbaru</strong>
                                        <div>{{ $extracurricular->achievements->first()->title ?? 'Lihat detail untuk melihat prestasi ekstrakurikuler.' }}</div>
                                    </div>
                                </div>

                                <div class="catalog-card-actions">
                                    @if($extracurricular->exists)
                                        <a href="{{ route('public.extracurriculars.show', $extracurricular) }}" class="btn btn-outline-primary flex-fill"><i class="bi bi-eye"></i>Lihat Detail</a>
                                        <a href="{{ route('public.extracurriculars.register', $extracurricular) }}" class="btn btn-primary flex-fill"><i class="bi bi-send-check"></i>Daftar Ekskul</a>
                                    @else
                                        <a href="{{ route('login') }}" class="btn btn-primary w-100"><i class="bi bi-box-arrow-in-right"></i>Masuk untuk Mendaftar</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card">
                            <div class="empty-state">
                                <div class="icon"><i class="bi bi-inbox"></i></div>
                                <p class="mb-0">Belum ada ekstrakurikuler yang tersedia saat ini.</p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
@endsection
