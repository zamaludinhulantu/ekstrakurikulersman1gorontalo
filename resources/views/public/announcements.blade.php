@extends('layouts.public')

@section('title', 'Pengumuman | Sistem Informasi Ekstrakurikuler SMA Negeri 1 Gorontalo')

@push('styles')
    <style>
        .announcement-hero {
            position: relative;
            overflow: hidden;
            border-radius: 34px;
            padding: 1.5rem;
            margin: 1.25rem 0 1.5rem;
            color: #fff;
            background:
                radial-gradient(circle at 18% 18%, rgba(255, 255, 255, 0.16) 0%, rgba(255, 255, 255, 0) 24%),
                linear-gradient(135deg, #0f2f57 0%, #1f5eff 55%, #7bc8ff 100%);
            box-shadow: 0 28px 44px rgba(16, 35, 63, 0.16);
        }

        .announcement-hero::before,
        .announcement-hero::after {
            content: "";
            position: absolute;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.1);
        }

        .announcement-hero::before {
            width: 220px;
            height: 220px;
            top: -110px;
            right: -50px;
        }

        .announcement-hero::after {
            width: 170px;
            height: 170px;
            left: -40px;
            bottom: -60px;
        }

        .announcement-hero > * {
            position: relative;
            z-index: 1;
        }

        .announcement-card {
            height: 100%;
            border-radius: 20px;
            border: 1px solid #dbe5f0;
            background: #fff;
            box-shadow: 0 10px 18px rgba(16, 35, 63, 0.05);
            padding: 1rem;
        }

        .announcement-card-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .announcement-card-meta span {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 0.65rem;
            border-radius: 999px;
            background: #eef5ff;
            color: #355987;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .announcement-card h3 {
            margin-bottom: 0.55rem;
            font-size: 1.05rem;
            font-weight: 800;
            color: #19324d;
        }

        .announcement-card p {
            margin-bottom: 0;
            color: #4f6580;
            line-height: 1.7;
        }
    </style>
@endpush

@section('content')
    <div class="container py-3 py-md-4">
        <section class="announcement-hero">
            <div class="row g-4 align-items-center">
                <div class="col-lg-8">
                    <span class="badge bg-white text-primary px-3 py-2 mb-3">Informasi Terbaru</span>
                    <h1 class="hero-title mb-3">Pengumuman ekstrakurikuler untuk siswa.</h1>
                    <p class="hero-text mb-0">
                        Lihat informasi terbaru dari admin atau pembina, termasuk jadwal penting, kegiatan, dan arahan
                        yang perlu diketahui siswa.
                    </p>
                </div>
                <div class="col-lg-4">
                    <div class="d-grid gap-2">
                        <a href="{{ route('landing') }}#daftar-ekskul" class="btn btn-light text-primary"><i class="bi bi-grid-3x3-gap"></i>Lihat Daftar Ekskul</a>
                        <a href="{{ route('public.information') }}" class="btn btn-outline-light"><i class="bi bi-signpost-2"></i>Lihat Alur Pendaftaran</a>
                    </div>
                </div>
            </div>
        </section>

        <section>
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
                <div>
                    <span class="section-kicker"><i class="bi bi-megaphone"></i>Pengumuman</span>
                    <h2 class="section-title">Info terbaru dari sekolah dan pembina</h2>
                    <p class="section-subtitle mb-0">Semua pengumuman aktif ditampilkan di sini agar siswa lebih mudah mengikuti informasi.</p>
                </div>
            </div>

            <div class="row g-3">
                @forelse($announcements as $announcement)
                    <div class="col-12 col-md-6 col-xl-4">
                        <article class="announcement-card">
                            <div class="announcement-card-meta">
                                <span><i class="bi bi-calendar-event"></i>{{ $announcement->created_at?->translatedFormat('d F Y') ?? '-' }}</span>
                                <span><i class="bi bi-diagram-3"></i>{{ $announcement->extracurricular?->name ?? 'Semua ekstrakurikuler' }}</span>
                            </div>
                            <h3>{{ $announcement->title }}</h3>
                            <p class="mb-3">{{ $announcement->content }}</p>
                            <div class="small text-muted">
                                Dipublikasikan oleh {{ $announcement->publisher?->name ?? 'Admin' }}
                            </div>
                        </article>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card">
                            <div class="empty-state">
                                <div class="icon"><i class="bi bi-megaphone"></i></div>
                                <p class="mb-0">Belum ada pengumuman yang ditampilkan saat ini.</p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
@endsection
