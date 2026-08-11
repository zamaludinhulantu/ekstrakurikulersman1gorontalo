@extends('layouts.public')

@section('title', 'Sistem Informasi Ekstrakurikuler SMA Negeri 1 Gorontalo')
@section('meta_description', 'Jelajahi kegiatan ekstrakurikuler, pengumuman, dan berita siswa di Sistem Informasi Ekstrakurikuler SMA Negeri 1 Gorontalo.')

@push('styles')
    <style>
        .landing-shell section + section {
            margin-top: 1.5rem;
        }

        .editorial-section-shell {
            padding: 1.25rem;
            border-radius: 30px;
            border: 1px solid rgba(219, 229, 240, 0.9);
            background:
                radial-gradient(circle at top right, rgba(184, 220, 255, 0.18) 0%, rgba(184, 220, 255, 0) 30%),
                linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(246, 250, 255, 0.95));
            box-shadow: 0 20px 36px rgba(16, 35, 63, 0.07);
        }

        .editorial-section-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .editorial-section-copy {
            max-width: 42rem;
        }

        .editorial-section-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            margin-bottom: 0.7rem;
            padding: 0.42rem 0.8rem;
            border-radius: 999px;
            background: #edf4ff;
            color: #1849cb;
            font-size: 0.75rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.12em;
        }

        .editorial-section-title {
            margin: 0;
            color: #12305b;
            font-size: clamp(1.5rem, 2.4vw, 2.15rem);
            line-height: 1.1;
            letter-spacing: -0.04em;
            font-weight: 900;
        }

        .editorial-section-subtitle {
            margin: 0.55rem 0 0;
            max-width: 38rem;
            color: #63768d;
            font-size: 0.95rem;
            line-height: 1.8;
        }

        .editorial-section-cta {
            flex-shrink: 0;
            align-self: flex-end;
        }

        .editorial-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
        }

        .editorial-grid.is-two-items {
            grid-template-columns: repeat(2, minmax(0, 22rem));
            justify-content: center;
        }

        .editorial-grid.is-one-item {
            grid-template-columns: minmax(0, 28rem);
            justify-content: center;
        }

        .editorial-showcase {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(18rem, 0.8fr);
            gap: 1rem;
        }

        .editorial-article-featured .public-article-card__media {
            aspect-ratio: 16 / 8.2;
        }

        .editorial-article-featured .public-article-card__title {
            font-size: clamp(1.25rem, 2vw, 1.55rem);
            -webkit-line-clamp: 2;
        }

        .editorial-article-support {
            display: grid;
            gap: 1rem;
            align-content: start;
        }

        .editorial-article-support .public-article-card {
            display: grid;
            grid-template-columns: 10rem minmax(0, 1fr);
            border-radius: 22px;
            box-shadow: none;
            overflow: hidden;
        }

        .editorial-article-support .public-article-card__media {
            width: 100%;
            height: 100%;
            min-height: 8.65rem;
            min-width: 0;
            aspect-ratio: auto;
        }

        .editorial-article-support .public-article-card__image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center 20%;
            min-width: 0;
        }

        .editorial-article-support .public-article-card__placeholder {
            min-width: 0;
        }

        .editorial-article-support .public-article-card__body {
            min-width: 0;
        }

        .editorial-article-support .public-article-card__body {
            padding: 0.9rem;
        }

        .editorial-article-support .public-article-card__meta {
            gap: 0.35rem;
            margin-bottom: 0.5rem;
        }

        .editorial-article-support .public-article-card__chip {
            padding: 0.3rem 0.48rem;
            font-size: 0.68rem;
        }

        .editorial-article-support .public-article-card__title {
            margin-bottom: 0.35rem;
            font-size: 1rem;
            -webkit-line-clamp: 2;
        }

        .editorial-article-support .public-article-card__excerpt {
            margin-bottom: 0;
            font-size: 0.82rem;
            line-height: 1.55;
            -webkit-line-clamp: 2;
        }

        .editorial-article-support .public-article-card__footer {
            display: none;
        }

        .hero-premium {
            position: relative;
            overflow: hidden;
            border-radius: 34px;
            padding: clamp(1.1rem, 2.3vw, 1.6rem);
            margin: 0.85rem 0 1.1rem;
            color: #fff;
            background:
                linear-gradient(90deg, rgba(10, 25, 46, 0.82) 0%, rgba(13, 37, 70, 0.72) 36%, rgba(27, 71, 129, 0.44) 68%, rgba(48, 110, 191, 0.18) 100%),
                url('{{ asset('images/extracurriculars/smans1.jpeg') }}') center/cover no-repeat;
            box-shadow: 0 22px 42px rgba(16, 35, 63, 0.16);
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
                radial-gradient(circle at 18% 18%, rgba(255, 255, 255, 0.12) 0%, rgba(255, 255, 255, 0) 22%),
                linear-gradient(180deg, rgba(7, 20, 38, 0.05) 0%, rgba(7, 20, 38, 0.16) 100%);
        }

        .hero-premium::after {
            width: 148px;
            height: 148px;
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
            font-size: clamp(2rem, 3.8vw, 3.2rem);
            line-height: 1.04;
            letter-spacing: -0.05em;
            font-weight: 900;
            max-width: 12ch;
            text-wrap: balance;
        }

        .hero-premium-copy {
            max-width: 34rem;
            color: rgba(245, 249, 255, 0.96);
            font-size: 0.96rem;
            line-height: 1.7;
            margin-bottom: 1rem;
        }

        .hero-stat-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.75rem;
            margin-top: 1rem;
            max-width: 36rem;
        }

        .hero-stat-chip {
            padding: 0.78rem 0.9rem;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            background: rgba(255, 255, 255, 0.14);
            min-width: 0;
        }

        .hero-stat-chip .label {
            display: block;
            color: rgba(236, 244, 255, 0.82);
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 0.22rem;
        }

        .hero-stat-chip .value {
            display: block;
            color: #fff;
            font-size: clamp(1.02rem, 1.9vw, 1.42rem);
            font-weight: 900;
            letter-spacing: -0.03em;
            line-height: 1.1;
            overflow-wrap: anywhere;
        }

        .hero-stat-chip .caption {
            display: block;
            margin-top: 0.3rem;
            color: rgba(232, 242, 255, 0.78);
            font-size: 0.76rem;
            line-height: 1.45;
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
            min-width: 0;
            transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
        }

        .activities-hub-card-media {
            position: relative;
            aspect-ratio: 16 / 8.4;
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

        .activities-hub-card-fallback {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            gap: 0.55rem;
            padding: 1rem;
            background:
                radial-gradient(circle at top right, rgba(255, 255, 255, 0.34) 0%, rgba(255, 255, 255, 0) 28%),
                linear-gradient(135deg, rgba(14, 45, 86, 0.92) 0%, rgba(31, 94, 255, 0.86) 58%, rgba(90, 197, 255, 0.76) 100%);
            color: #fff;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease;
        }

        .activities-hub-card-media.has-image-fallback .activities-hub-card-fallback {
            opacity: 1;
        }

        .activities-hub-card-media.has-image-fallback img {
            opacity: 0;
            pointer-events: none;
        }

        .activities-hub-card-fallback-icon {
            width: 3rem;
            height: 3rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: auto;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.18);
            font-size: 1.15rem;
        }

        .activities-hub-card-fallback-text {
            font-size: 1rem;
            font-weight: 900;
            line-height: 1.3;
            text-wrap: balance;
        }

        .activities-hub-card-body {
            padding: 1rem;
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            min-width: 0;
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

        .activities-hub-card h3 {
            margin: 0 0 0.45rem;
            font-size: 1.22rem;
            font-weight: 900;
            color: #163252;
            overflow-wrap: anywhere;
        }

        .activities-hub-card p {
            margin: 0 0 1rem;
            color: #607389;
            line-height: 1.7;
            flex: 1 1 auto;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 3;
            overflow: hidden;
            overflow-wrap: anywhere;
        }

        .activities-hub-card .btn {
            margin-top: auto;
        }

        .category-carousel {
            position: relative;
        }

        .category-carousel-viewport {
            overflow: hidden;
            padding: 0.25rem 0.1rem 0.55rem;
        }

        .category-carousel-track {
            display: flex;
            transition: transform 0.55s cubic-bezier(0.22, 0.61, 0.36, 1);
            will-change: transform;
        }

        .category-carousel-slide {
            flex: 0 0 33.333333%;
            min-width: 0;
            padding: 0 0.5rem;
        }

        .category-carousel-controls {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            flex-wrap: nowrap;
            gap: 1rem;
            margin-top: 0.45rem;
        }

        .category-carousel-controls [data-category-carousel-prev] {
            justify-self: start;
        }

        .category-carousel-controls [data-category-carousel-next] {
            justify-self: end;
        }

        .category-carousel-center-controls {
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
        }

        .category-carousel-control {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.6rem;
            height: 2.6rem;
            border: 1px solid #d7e3f4;
            border-radius: 999px;
            background: #fff;
            color: #23496f;
            transition: background-color 0.2s ease, color 0.2s ease, transform 0.2s ease;
        }

        .category-carousel-control:hover,
        .category-carousel-control:focus-visible {
            background: #123b73;
            color: #fff;
            transform: translateY(-1px);
        }

        .category-carousel-dots {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 3.5rem;
            height: 2.25rem;
            padding: 0 0.72rem;
            border-radius: 999px;
            background: #edf4ff;
            color: #315f91;
            font-size: 0.78rem;
            font-weight: 900;
        }

        .category-carousel-dot {
            display: none;
        }

        .category-carousel-all-link {
            display: inline-flex;
        }

        .category-carousel-all-link .btn {
            border: 0;
            padding: 0.45rem 0.2rem;
            color: #1849cb;
            font-weight: 800;
        }

        .category-carousel-all-link .btn:hover,
        .category-carousel-all-link .btn:focus-visible {
            background: transparent;
            color: #0d3a9d;
            text-decoration: underline;
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
            position: relative;
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

        .premium-step-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            margin-bottom: 0.75rem;
            border-radius: 999px;
            background: #edf4ff;
            color: #1849cb;
            font-size: 0.8rem;
            font-weight: 900;
            letter-spacing: 0.08em;
        }

        .premium-cta {
            padding: 1.2rem;
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

            .editorial-grid,
            .editorial-grid.is-two-items,
            .editorial-grid.is-one-item {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                justify-content: stretch;
            }

            .editorial-showcase {
                grid-template-columns: 1fr;
            }

            .category-carousel-slide {
                flex-basis: 50%;
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

            .editorial-section-shell {
                padding: 1rem;
                border-radius: 24px;
            }

            .editorial-section-header {
                flex-direction: column;
                align-items: stretch;
            }

            .editorial-section-cta {
                align-self: stretch;
            }

            .editorial-section-cta .btn {
                width: 100%;
            }

            .editorial-grid,
            .editorial-grid.is-two-items,
            .editorial-grid.is-one-item {
                grid-template-columns: 1fr;
            }

            .editorial-article-support .public-article-card {
                grid-template-columns: 1fr;
            }

            .editorial-article-support .public-article-card__media {
                height: auto;
                min-height: 0;
                aspect-ratio: 16 / 9;
            }

            .hero-stat-grid {
                grid-template-columns: 1fr;
            }

            .category-carousel-slide {
                flex-basis: 100%;
            }

        }
    </style>
@endpush

@section('content')
    <div class="container py-3 py-md-4 landing-shell">
        <section class="hero-premium" data-reveal aria-labelledby="landingHeroTitle">
            <div class="row">
                <div class="col-lg-8 col-xl-7">
                    <span class="badge bg-white text-primary px-3 py-2 mb-3">Portal Informasi Ekstrakurikuler</span>
                    <h1 class="hero-premium-title" id="landingHeroTitle">Temukan ruang terbaik untuk bertumbuh.</h1>
                    <p class="hero-premium-copy">
                        Jelajahi ekstrakurikuler, OSN, dan O2SN yang sesuai dengan minat dan potensimu, mulai dari pembinaan seperti Tilawatil Qur&#039;an hingga jalur akademik dan olahraga sekolah.
                    </p>
                    <div class="hero-actions">
                        <a href="{{ route('public.activities.index') }}" class="btn btn-light text-primary"><i class="bi bi-grid-3x3-gap"></i>Jelajahi Kegiatan</a>
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
                            <span class="label">Layanan Pendaftaran</span>
                            <span class="value">Online</span>
                            <span class="caption">Akses informasi dan pendaftaran kapan pun melalui akun siswa.</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="kategori" data-reveal>
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-3">
                <div>
                    <span class="section-kicker"><i class="bi bi-collection"></i>Kategori Kegiatan</span>
                    <h2 class="section-title">Pilih kategori yang paling sesuai</h2>
                    <p class="section-subtitle mb-0">Setiap kategori dirancang agar siswa baru bisa memahami jalur kegiatan dengan lebih cepat.</p>
                </div>
            </div>
            <div class="category-carousel" data-category-carousel aria-label="Carousel kategori kegiatan">
                <div class="category-carousel-viewport">
                    <div class="category-carousel-track">
                @foreach($categorySummaries as $summary)
                    <div class="category-carousel-slide">
                        @include('public._category-card', ['summary' => $summary, 'variant' => 'media'])
                    </div>
                @endforeach
                    </div>
                </div>
                <div class="category-carousel-controls">
                    <button class="category-carousel-control" type="button" data-category-carousel-prev aria-label="Kategori sebelumnya">
                        <i class="bi bi-arrow-left"></i>
                    </button>
                    <div class="category-carousel-center-controls">
                        <div class="category-carousel-dots" data-category-carousel-dots aria-live="polite"></div>
                        <span class="category-carousel-all-link">
                            <a href="{{ route('public.activities.index') }}" class="btn btn-outline-primary">
                                Lihat semua kategori<i class="bi bi-arrow-right"></i>
                            </a>
                        </span>
                    </div>
                    <button class="category-carousel-control" type="button" data-category-carousel-next aria-label="Kategori berikutnya">
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </div>
        </section>

        <section id="pengumuman" data-reveal>
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
                            <div class="empty-state py-4">
                                <div class="icon"><i class="bi bi-megaphone"></i></div>
                                <h3 class="h5 fw-bold text-dark mb-2">Belum ada pengumuman terbaru.</h3>
                                <p class="mb-0">Informasi baru dari sekolah dan pembina akan tampil di sini.</p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </section>

        <section id="berita" class="editorial-section-shell">
            <div class="editorial-section-header">
                <div class="editorial-section-copy" data-reveal style="--reveal-delay: 0ms;">
                    <span class="editorial-section-eyebrow" data-reveal style="--reveal-delay: 20ms;"><i class="bi bi-newspaper"></i>Berita & Artikel</span>
                    <h2 class="editorial-section-title" data-reveal style="--reveal-delay: 80ms;">Cerita terbaru dari kegiatan siswa</h2>
                    <p class="editorial-section-subtitle" data-reveal style="--reveal-delay: 130ms;">Dokumentasi prestasi, agenda penting, dan publikasi pembinaan terbaru tampil ringkas di beranda.</p>
                </div>
                <div class="editorial-section-cta" data-reveal style="--reveal-delay: 170ms;">
                    <a href="{{ route('public.articles.index') }}" class="btn btn-outline-primary"><i class="bi bi-arrow-right-circle"></i>Lihat Semua</a>
                </div>
            </div>

            @if($recentArticles->isNotEmpty())
                <div class="editorial-showcase">
                    <div class="editorial-article-featured">
                        @include('public._article-card', ['article' => $recentArticles->first(), 'variant' => 'featured', 'revealDelay' => 200])
                    </div>
                    @if($recentArticles->count() > 1)
                        <div class="editorial-article-support">
                            @foreach($recentArticles->skip(1)->take(3) as $article)
                                @include('public._article-card', ['article' => $article, 'variant' => 'compact', 'revealDelay' => 260 + (($loop->index % 3) * 60)])
                            @endforeach
                        </div>
                    @endif
                </div>
            @else
                <div class="card border-0 bg-transparent shadow-none" data-reveal style="--reveal-delay: 200ms;">
                    <div class="empty-state py-4">
                        <div class="icon"><i class="bi bi-newspaper"></i></div>
                        <h3 class="h5 fw-bold text-dark mb-2">Belum ada artikel terbaru</h3>
                        <p class="mb-3">Publikasi terbaru dari sekolah dan pembina akan muncul di sini begitu konten dipublikasikan.</p>
                        <div class="empty-state-actions">
                            <a href="{{ route('public.announcements') }}" class="btn btn-outline-primary"><i class="bi bi-megaphone"></i>Lihat Pengumuman</a>
                            <a href="{{ route('public.activities.index') }}" class="btn btn-outline-secondary"><i class="bi bi-grid-3x3-gap"></i>Lihat Kegiatan</a>
                        </div>
                    </div>
                </div>
            @endif
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

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const carousel = document.querySelector('[data-category-carousel]');
            const track = carousel?.querySelector('.category-carousel-track');
            const slides = track ? Array.from(track.children) : [];
            const dots = carousel?.querySelector('[data-category-carousel-dots]');

            if (!carousel || !track || !slides.length || !dots) {
                return;
            }

            const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
            let page = 0;
            let interval = null;
            let pageCount = 1;

            const perPage = () => window.matchMedia('(min-width: 992px)').matches ? 3 : (window.matchMedia('(min-width: 768px)').matches ? 2 : 1);

            const render = () => {
                const visible = perPage();
                pageCount = Math.max(1, Math.ceil(slides.length / visible));
                page = Math.min(page, pageCount - 1);
                track.style.transform = `translateX(-${page * 100}%)`;
                dots.textContent = `${page + 1} / ${pageCount}`;
            };

            const move = (direction) => {
                page = (page + direction + pageCount) % pageCount;
                render();
            };

            const stop = () => {
                window.clearInterval(interval);
                interval = null;
            };

            const start = () => {
                stop();
                if (pageCount > 1 && !reducedMotion.matches) {
                    interval = window.setInterval(() => move(1), 5000);
                }
            };

            const restart = () => start();

            carousel.querySelector('[data-category-carousel-prev]')?.addEventListener('click', () => {
                move(-1);
                restart();
            });
            carousel.querySelector('[data-category-carousel-next]')?.addEventListener('click', () => {
                move(1);
                restart();
            });
            carousel.addEventListener('mouseenter', stop);
            carousel.addEventListener('mouseleave', start);
            carousel.addEventListener('focusin', stop);
            carousel.addEventListener('focusout', (event) => {
                if (!carousel.contains(event.relatedTarget)) {
                    start();
                }
            });
            window.addEventListener('resize', () => {
                render();
                start();
            });
            reducedMotion.addEventListener('change', start);

            render();
            start();
        });
    </script>
@endpush
