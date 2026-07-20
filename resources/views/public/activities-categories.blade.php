@extends('layouts.public')

@section('title', 'Kegiatan | Sistem Informasi Ekstrakurikuler SMA Negeri 1 Gorontalo')

@push('styles')
    <style>
        .activities-hub-hero,
        .activities-hub-card,
        .activities-hub-all {
            border-radius: 30px;
            border: 1px solid #dbe5f0;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.99), rgba(245, 250, 255, 0.96));
            box-shadow: 0 16px 30px rgba(16, 35, 63, 0.07);
        }

        .activities-hub-hero {
            padding: 1.2rem 1.3rem;
            margin: 1rem 0 1.1rem;
        }

        .activities-hub-card {
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
            transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
        }

        .activities-hub-card:hover {
            transform: translateY(-4px);
            border-color: #bfd3fb;
            box-shadow: 0 22px 38px rgba(16, 35, 63, 0.1);
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

        .activities-hub-card:hover .activities-hub-card-media img {
            transform: scale(1.03);
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

        .activities-hub-all {
            margin-top: 1rem;
            padding: 1rem 1.1rem;
        }

        @media (max-width: 767.98px) {
            .activities-hub-hero,
            .activities-hub-all {
                padding: 1rem;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container py-3 py-md-4">
        <section class="activities-hub-hero" data-reveal>
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <span class="section-kicker"><i class="bi bi-grid-3x3-gap"></i>Kategori Kegiatan</span>
                    <h1 class="section-title mb-2">Pilih kategori ekskul yang ingin dijelajahi</h1>
                    <p class="section-subtitle mb-0">Setiap kegiatan tetap berada dalam sistem ekstrakurikuler, lalu dipisahkan per kategori agar lebih mudah dipilih.</p>
                </div>
                <a href="{{ route('public.information') }}" class="btn btn-outline-primary"><i class="bi bi-signpost-2"></i>Lihat Alur Pendaftaran</a>
            </div>
        </section>

        <section data-reveal>
            <div class="row g-3">
                @foreach($categorySummaries as $summary)
                    <div class="col-12 col-lg-4">
                        @include('public._category-card', ['summary' => $summary, 'variant' => 'media'])
                    </div>
                @endforeach
            </div>
        </section>

        <section class="activities-hub-all" data-reveal>
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <span class="section-kicker"><i class="bi bi-collection"></i>Pilihan Tambahan</span>
                    <h2 class="section-title mb-1">Butuh melihat semua kegiatan sekaligus?</h2>
                    <p class="section-subtitle mb-0">Halaman semua kegiatan tetap tersedia sebagai opsi tambahan, tanpa mengganggu alur utama pemilihan kategori ekskul.</p>
                </div>
                <a href="{{ route('public.activities.all') }}" class="btn btn-outline-primary"><i class="bi bi-grid-3x3-gap"></i>Lihat Semua Kegiatan</a>
            </div>
        </section>
    </div>
@endsection
