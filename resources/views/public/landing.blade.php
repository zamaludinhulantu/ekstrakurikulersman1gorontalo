@extends('layouts.public')

@section('title', 'Sistem Informasi Ekstrakurikuler SMA Negeri 1 Gorontalo')

@push('styles')
    <style>
        .landing-shell section + section {
            margin-top: 1.5rem;
        }

        .hero-premium {
            position: relative;
            overflow: hidden;
            border-radius: 34px;
            padding: 1.5rem;
            margin: 1rem 0 1.25rem;
            color: #fff;
            background:
                linear-gradient(90deg, rgba(13, 33, 61, 0.72) 0%, rgba(17, 53, 102, 0.62) 42%, rgba(48, 110, 191, 0.44) 100%),
                url('{{ asset('images/extracurriculars/smans1.jpeg') }}') center/cover no-repeat;
            box-shadow: 0 28px 52px rgba(16, 35, 63, 0.18);
        }

        .hero-premium::before,
        .hero-premium::after {
            content: "";
            position: absolute;
            border-radius: 999px;
            pointer-events: none;
        }

        .hero-premium::before {
            inset: 0;
            border-radius: inherit;
            background:
                radial-gradient(circle at 18% 18%, rgba(255, 255, 255, 0.16) 0%, rgba(255, 255, 255, 0) 22%),
                linear-gradient(180deg, rgba(7, 20, 38, 0.08) 0%, rgba(7, 20, 38, 0.18) 100%);
        }

        .hero-premium::after {
            width: 180px;
            height: 180px;
            left: -36px;
            bottom: -76px;
            background: rgba(90, 197, 255, 0.14);
        }

        .hero-premium > * {
            position: relative;
            z-index: 1;
        }

        .hero-premium-title {
            margin: 0 0 0.8rem;
            font-size: clamp(2rem, 4vw, 3.6rem);
            line-height: 1.02;
            letter-spacing: -0.05em;
            font-weight: 900;
            max-width: 10ch;
        }

        .hero-premium-copy {
            max-width: 36rem;
            color: rgba(245, 249, 255, 0.96);
            font-size: 1rem;
            line-height: 1.75;
            margin-bottom: 1.15rem;
        }

        .hero-stat-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.8rem;
            margin-top: 1.15rem;
            max-width: 38rem;
        }

        .hero-stat-chip {
            padding: 0.9rem 1rem;
            border-radius: 22px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            background: rgba(255, 255, 255, 0.14);
            backdrop-filter: blur(10px);
        }

        .hero-stat-chip .label {
            display: block;
            color: rgba(236, 244, 255, 0.82);
            font-size: 0.74rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 0.3rem;
        }

        .hero-stat-chip .value {
            display: block;
            color: #fff;
            font-size: clamp(1.1rem, 2vw, 1.55rem);
            font-weight: 900;
            letter-spacing: -0.03em;
        }

        .premium-section-card,
        .premium-step-card,
        .premium-cta,
        .activities-hub-card {
            border-radius: 28px;
            border: 1px solid #dbe5f0;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.99), rgba(246, 250, 255, 0.96));
            box-shadow: 0 14px 28px rgba(16, 35, 63, 0.06);
        }

        .activities-hub-card {
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
            transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
        }

        .activities-hub-card-media {
            position: relative;
            aspect-ratio: 16 / 9;
            overflow: hidden;
            background: #eef5ff;
        }

        .activities-hub-card-media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.35s ease;
        }

        .activities-hub-card-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(10, 24, 46, 0.04), rgba(10, 24, 46, 0.35));
        }

        .activities-hub-card-body {
            padding: 1.15rem;
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
        }

        .activities-hub-card-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.8rem;
            margin-bottom: 0.95rem;
        }

        .activities-hub-card:hover {
            transform: translateY(-4px);
            border-color: #bfd3fb;
            box-shadow: 0 20px 34px rgba(16, 35, 63, 0.1);
        }

        .activities-hub-card:hover .activities-hub-card-media img {
            transform: scale(1.03);
        }

        .activities-hub-card-icon {
            width: 3.1rem;
            height: 3.1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 20px;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .activities-hub-card.is-extracurricular .activities-hub-card-icon {
            background: #eaf2ff;
            color: #1849cb;
        }

        .activities-hub-card.is-osn .activities-hub-card-icon {
            background: #eaf8ff;
            color: #0d78a7;
        }

        .activities-hub-card.is-o2sn .activities-hub-card-icon {
            background: #fff4dd;
            color: #a76405;
        }

        .activities-hub-card-count {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.42rem 0.68rem;
            border-radius: 999px;
            border: 1px solid #dbe5f0;
            color: #48607b;
            font-size: 0.76rem;
            font-weight: 800;
            background: #fff;
        }

        .activities-hub-card h2 {
            margin: 0 0 0.45rem;
            font-size: 1.35rem;
            font-weight: 900;
            color: #163252;
        }

        .activities-hub-card p {
            margin: 0 0 1rem;
            color: #607389;
            line-height: 1.75;
            flex: 1 1 auto;
        }

        .activities-hub-card .btn {
            margin-top: auto;
        }

        .premium-section-card {
            padding: 1.2rem;
        }

        .premium-step-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
        }

        .premium-step-card {
            padding: 1rem;
            height: 100%;
        }

        .premium-step-icon {
            width: 2.8rem;
            height: 2.8rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 18px;
            background: linear-gradient(135deg, #1f5eff 0%, #5ac5ff 100%);
            color: #fff;
            font-size: 1rem;
            margin-bottom: 0.85rem;
        }

        .premium-step-card h3 {
            margin: 0 0 0.4rem;
            font-size: 1rem;
            font-weight: 800;
        }

        .premium-step-card p {
            margin: 0;
            color: #607389;
        }

        .premium-cta {
            padding: 1.35rem;
            color: #fff;
            background:
                radial-gradient(circle at top right, rgba(90, 197, 255, 0.22) 0%, rgba(90, 197, 255, 0) 28%),
                linear-gradient(135deg, #0d2443 0%, #12325b 54%, #1849cb 100%);
            box-shadow: 0 24px 40px rgba(16, 35, 63, 0.16);
        }

        .premium-cta p {
            color: rgba(235, 244, 255, 0.84);
            margin-bottom: 0;
        }

        @media (max-width: 991.98px) {
            .hero-stat-grid,
            .premium-step-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 767.98px) {
            .hero-premium {
                padding: 1rem;
                border-radius: 26px;
                background-position: center right;
            }

            .hero-premium-title {
                max-width: none;
            }

            .category-premium-top {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container py-3 py-md-4 landing-shell">
        <section class="hero-premium" data-reveal>
            <div class="row">
                <div class="col-lg-8 col-xl-7">
                    <span class="badge bg-white text-primary px-3 py-2 mb-3">Portal Informasi Ekstrakurikuler</span>
                    <h1 class="hero-premium-title">Temukan ruang terbaik untuk bertumbuh.</h1>
                    <p class="hero-premium-copy">
                        Jelajahi ekstrakurikuler, OSN, dan O2SN yang sesuai dengan minat dan potensimu, mulai dari pembinaan seperti Tilawatil Qur&#039;an hingga jalur akademik dan olahraga sekolah.
                    </p>
                    <div class="hero-actions">
                        <a href="{{ route('public.activities.index') }}" class="btn btn-light text-primary"><i class="bi bi-grid-3x3-gap"></i>Jelajahi Kegiatan</a>
                        <a href="{{ route('public.information') }}" class="btn btn-outline-light"><i class="bi bi-signpost-2"></i>Lihat Alur Pendaftaran</a>
                    </div>
                    <div class="hero-stat-grid">
                        <div class="hero-stat-chip">
                            <span class="label">Kegiatan Aktif</span>
                            <span class="value" data-counter="{{ $statistics['totalActivities'] }}">{{ $statistics['totalActivities'] }}</span>
                        </div>
                        <div class="hero-stat-chip">
                            <span class="label">Kategori</span>
                            <span class="value" data-counter="{{ $statistics['categories'] }}">{{ $statistics['categories'] }}</span>
                        </div>
                        <div class="hero-stat-chip">
                            <span class="label">Pendaftaran Online</span>
                            <span class="value">{{ $statistics['onlineRegistration'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section data-reveal>
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-3">
                <div>
                    <span class="section-kicker"><i class="bi bi-collection"></i>Kategori Kegiatan</span>
                    <h2 class="section-title">Pilih kategori yang paling sesuai</h2>
                    <p class="section-subtitle mb-0">Setiap kategori dirancang agar siswa baru bisa memahami jalur kegiatan dengan lebih cepat.</p>
                </div>
            </div>
            <div class="row g-3">
                @foreach($categorySummaries as $summary)
                    <div class="col-12 col-lg-4">
                        @include('public._category-card', ['summary' => $summary, 'variant' => 'media'])
                    </div>
                @endforeach
            </div>
        </section>

        <section class="premium-section-card" data-reveal>
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-3">
                <div>
                    <span class="section-kicker"><i class="bi bi-signpost-split"></i>Alur Pendaftaran</span>
                    <h2 class="section-title">Tiga langkah yang perlu dilakukan siswa</h2>
                    <p class="section-subtitle mb-0">Rangkuman singkat di beranda, sementara detail lengkap tetap ada di halaman alur pendaftaran.</p>
                </div>
                <a href="{{ route('public.information') }}" class="btn btn-outline-primary"><i class="bi bi-arrow-right-circle"></i>Lihat Detail Alur</a>
            </div>
            <div class="premium-step-grid">
                <article class="premium-step-card">
                    <span class="premium-step-icon"><i class="bi bi-compass"></i></span>
                    <h3>Pilih kegiatan</h3>
                    <p>Telusuri kategori dan buka detail kegiatan yang paling cocok dengan minatmu.</p>
                </article>
                <article class="premium-step-card">
                    <span class="premium-step-icon"><i class="bi bi-box-arrow-in-right"></i></span>
                    <h3>Masuk dan kirim pendaftaran</h3>
                    <p>Gunakan akun siswa untuk melanjutkan ke halaman form pendaftaran terpisah.</p>
                </article>
                <article class="premium-step-card">
                    <span class="premium-step-icon"><i class="bi bi-patch-check"></i></span>
                    <h3>Tunggu verifikasi pembina</h3>
                    <p>Pendaftaran akan diperiksa pembina atau admin sebelum status akhirnya ditetapkan.</p>
                </article>
            </div>
        </section>

        <section data-reveal>
            <div class="section-header-inline">
                <div>
                    <span class="section-kicker"><i class="bi bi-megaphone"></i>Pengumuman</span>
                    <h2 class="section-title">Informasi terbaru dari sekolah dan pembina</h2>
                    <p class="section-subtitle mb-0">Pengumuman penting tetap singkat dan mudah dipindai dari beranda.</p>
                </div>
                <a href="{{ route('public.announcements') }}" class="btn btn-outline-primary"><i class="bi bi-arrow-right-circle"></i>Lihat Semua</a>
            </div>
            <div class="row g-3">
                @forelse($recentAnnouncements as $announcement)
                    <div class="col-12 col-lg-4">
                        @include('public._announcement-card', ['announcement' => $announcement])
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card">
                            <div class="empty-state">
                                <div class="icon"><i class="bi bi-megaphone"></i></div>
                                <p class="mb-0">Belum ada pengumuman terbaru.</p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="premium-cta" data-reveal>
            <div class="row g-4 align-items-center">
                <div class="col-lg-8">
                    <span class="section-kicker bg-white text-primary border-0"><i class="bi bi-rocket-takeoff"></i>Mulai Sekarang</span>
                    <h2 class="section-title text-white">Temukan kegiatan yang sesuai dengan potensimu.</h2>
                    <p>Mulai dari katalog kegiatan, pahami detailnya, lalu lanjutkan pendaftaran melalui akun siswa.</p>
                </div>
                <div class="col-lg-4">
                    <div class="d-grid gap-2">
                        <a href="{{ route('public.activities.index') }}" class="btn btn-light text-primary"><i class="bi bi-grid-3x3-gap"></i>Jelajahi Kegiatan</a>
                        <a href="{{ route('register') }}" class="btn btn-outline-light"><i class="bi bi-person-plus"></i>Buat Akun</a>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
