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
            margin-top: 1.25rem;
        }
    </style>
@endpush

@section('content')
    <div class="container py-3 py-md-4">
        <section class="announcement-hero" data-reveal>
            <div class="row g-4 align-items-center">
                <div class="col-lg-8">
                    <span class="badge bg-white text-primary px-3 py-2 mb-3">Informasi Terbaru</span>
                    <h1 class="hero-title mb-3">Pengumuman resmi untuk calon peserta dan siswa.</h1>
                    <p class="hero-text mb-0">
                        Pantau informasi terbaru dari sekolah dan pembina, mulai dari jadwal penting, arahan pendaftaran,
                        hingga kabar terbaru dari kategori Ekstrakurikuler, OSN, dan O2SN.
                    </p>
                </div>
                <div class="col-lg-4">
                    <div class="d-grid gap-2">
                        <a href="{{ route('public.activities.index') }}" class="btn btn-light text-primary"><i class="bi bi-grid-3x3-gap"></i>Jelajahi Kategori</a>
                        <a href="{{ route('public.information') }}" class="btn btn-outline-light"><i class="bi bi-signpost-2"></i>Pahami Alur Pendaftaran</a>
                    </div>
                </div>
            </div>
        </section>

        <section data-reveal>
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
                <div>
                    <span class="section-kicker"><i class="bi bi-megaphone"></i>Pengumuman</span>
                    <h2 class="section-title">Informasi terbaru dari sekolah dan pembina</h2>
                    <p class="section-subtitle mb-0">Semua pengumuman aktif ditampilkan dalam satu halaman agar informasi penting lebih mudah ditemukan.</p>
                </div>
            </div>

            <div class="row g-3">
                @forelse($announcements as $announcement)
                    <div class="col-12 col-md-6 col-xl-4">
                        @include('public._announcement-card', ['announcement' => $announcement])
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
