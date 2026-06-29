@extends('layouts.public')

@section('title', 'Alur Pendaftaran | Sistem Informasi Ekstrakurikuler SMA Negeri 1 Gorontalo')

@push('styles')
    <style>
        .info-hero {
            position: relative;
            overflow: hidden;
            border-radius: 34px;
            padding: 1.5rem;
            margin: 1.25rem 0 1.5rem;
            color: #fff;
            background:
                radial-gradient(circle at 20% 20%, rgba(255, 255, 255, 0.15) 0%, rgba(255, 255, 255, 0) 24%),
                linear-gradient(135deg, #0f2f57 0%, #1f5eff 55%, #7bc8ff 100%);
            box-shadow: 0 28px 44px rgba(16, 35, 63, 0.16);
        }

        .info-hero::before,
        .info-hero::after {
            content: "";
            position: absolute;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.1);
        }

        .info-hero::before {
            width: 220px;
            height: 220px;
            top: -110px;
            right: -50px;
        }

        .info-hero::after {
            width: 170px;
            height: 170px;
            left: -40px;
            bottom: -60px;
        }

        .info-hero > * {
            position: relative;
            z-index: 1;
        }
    </style>
@endpush

@section('content')
    <div class="container py-3 py-md-4">
        <section class="info-hero">
            <div class="row g-4 align-items-center">
                <div class="col-lg-8">
                    <span class="badge bg-white text-primary px-3 py-2 mb-3">Panduan Siswa Baru</span>
                    <h1 class="hero-title mb-3">Pahami alur pendaftaran ekstrakurikuler sebelum mulai mendaftar.</h1>
                    <p class="hero-text mb-0">
                        Halaman ini merangkum langkah yang perlu dilakukan siswa baru, mulai dari melihat daftar ekskul
                        sampai mengikuti jadwal latihan setelah pendaftaran diterima.
                    </p>
                </div>
                <div class="col-lg-4">
                    <div class="d-grid gap-2">
                        <a href="{{ route('landing') }}#daftar-ekskul" class="btn btn-light text-primary"><i class="bi bi-grid-3x3-gap"></i>Lihat Daftar Ekskul</a>
                        @guest
                            <a href="{{ route('register') }}" class="btn btn-outline-light"><i class="bi bi-person-plus"></i>Daftar Akun Siswa</a>
                        @endguest
                    </div>
                </div>
            </div>
        </section>

        <section id="alur-sistem" class="mb-4">
            <span class="section-kicker"><i class="bi bi-signpost-split"></i>Alur Step-by-Step</span>
            <h2 class="section-title">Alur Penggunaan Sistem</h2>
            <p class="section-subtitle">Ikuti urutan ini agar proses pendaftaran lebih mudah dipahami dan tidak membingungkan.</p>
            <div class="step-flow-grid">
                <div class="step-card"><span class="step-number">1</span><h3>Lihat daftar ekstrakurikuler</h3><p>Buka katalog ekskul untuk melihat pilihan kegiatan yang tersedia.</p></div>
                <div class="step-card"><span class="step-number">2</span><h3>Pilih sesuai minat</h3><p>Baca detail ekskul, pembina, jadwal, dan syarat bergabung.</p></div>
                <div class="step-card"><span class="step-number">3</span><h3>Isi formulir pendaftaran</h3><p>Masuk sebagai siswa lalu kirim pendaftaran melalui sistem.</p></div>
                <div class="step-card"><span class="step-number">4</span><h3>Tunggu konfirmasi</h3><p>Admin atau pembina akan memeriksa pendaftaran yang sudah masuk.</p></div>
                <div class="step-card"><span class="step-number">5</span><h3>Ikuti jadwal latihan</h3><p>Jika diterima, siswa dapat memantau jadwal latihan dari dashboard.</p></div>
            </div>
        </section>

        <section class="mb-4">
            <div class="row g-3">
                <div class="col-md-6 col-xl-4">
                    <div class="feature-card">
                        <span class="feature-icon"><i class="bi bi-grid"></i></span>
                        <h3>Informasi lebih mudah dicari</h3>
                        <p>Nama ekskul, pembina, dan jadwal latihan ditampilkan lebih ringkas supaya siswa baru cepat paham.</p>
                    </div>
                </div>
                <div class="col-md-6 col-xl-4">
                    <div class="feature-card">
                        <span class="feature-icon"><i class="bi bi-send-check"></i></span>
                        <h3>Pendaftaran online lebih praktis</h3>
                        <p>Siswa tidak perlu proses manual yang panjang karena pendaftaran dapat dikirim langsung dari akun.</p>
                    </div>
                </div>
                <div class="col-md-6 col-xl-4">
                    <div class="feature-card">
                        <span class="feature-icon"><i class="bi bi-phone"></i></span>
                        <h3>Nyaman dibuka dari HP</h3>
                        <p>Tampilan dibuat lebih responsif agar tetap mudah digunakan saat diakses melalui smartphone.</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="manfaat-sistem">
            <span class="section-kicker"><i class="bi bi-stars"></i>Manfaat Sistem</span>
            <h2 class="section-title">Kenapa sistem ini membantu siswa dan sekolah</h2>
            <p class="section-subtitle">Bukan hanya mempermudah pendaftaran, tetapi juga membantu sekolah menjaga proses tetap rapi dan terpantau.</p>
            <div class="row g-3">
                <div class="col-md-6 col-xl-3">
                    <div class="feature-card">
                        <span class="feature-icon"><i class="bi bi-collection"></i></span>
                        <h3>Informasi terpusat</h3>
                        <p>Semua data penting tersimpan dalam satu portal yang mudah diakses.</p>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="feature-card">
                        <span class="feature-icon"><i class="bi bi-clipboard-check"></i></span>
                        <h3>Proses lebih cepat</h3>
                        <p>Pendaftaran dan verifikasi berjalan lebih efisien daripada pencatatan manual.</p>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="feature-card">
                        <span class="feature-icon"><i class="bi bi-calendar3"></i></span>
                        <h3>Jadwal lebih jelas</h3>
                        <p>Siswa bisa langsung melihat latihan terdekat tanpa kebingungan mencari informasi.</p>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="feature-card">
                        <span class="feature-icon"><i class="bi bi-bar-chart"></i></span>
                        <h3>Pemantauan lebih rapi</h3>
                        <p>Admin, pembina, dan sekolah lebih mudah melihat perkembangan kegiatan.</p>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
