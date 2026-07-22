@extends('layouts.public')

@section('title', 'Login | Sistem Informasi Ekstrakurikuler SMA Negeri 1 Gorontalo')

@section('content')
    <section class="auth-page auth-page-public">
        <div class="container">
            <div class="auth-card auth-card-login">
                <div class="row g-0 align-items-stretch">
                    <div class="col-lg-5">
                        <div class="auth-hero">
                            <span class="auth-hero-badge"><i class="bi bi-shield-lock"></i>Akses Terproteksi</span>
                            <h1>Masuk untuk melanjutkan pendaftaran ekstrakurikuler.</h1>
                            <p>Siswa dapat mendaftar dan memantau status pengajuan. Admin, pembina, dan kepala sekolah tetap menggunakan akses yang sama ke dashboard.</p>
                            <div class="auth-feature-list">
                                <div class="auth-feature-item">
                                    <i class="bi bi-check2-circle"></i>
                                    <div>
                                        <strong>Status pendaftaran selalu terpantau</strong>
                                        <div>Lihat progres verifikasi dan riwayat pengajuan langsung dari akun siswa.</div>
                                    </div>
                                </div>
                                <div class="auth-feature-item">
                                    <i class="bi bi-grid-1x2"></i>
                                    <div>
                                        <strong>Satu akses untuk semua peran</strong>
                                        <div>Masuk dari satu halaman yang sama tanpa memisahkan portal siswa dan pengelola.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="auth-helper-list">
                                <div class="auth-helper-item">
                                    <strong>Belum punya akun?</strong>
                                    Buat akun siswa terlebih dahulu sebelum memilih kegiatan.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="auth-form-wrap">
                            <div class="auth-form-header">
                                <span class="auth-section-kicker">Login</span>
                                <h2>Masuk ke Sistem</h2>
                                <p>Gunakan email dan password akun kamu untuk masuk ke dashboard dan melanjutkan pendaftaran.</p>
                            </div>

                            @if(request()->query('reason') === 'idle')
                                <div class="alert alert-warning alert-dismissible fade show app-alert" role="alert">
                                    <i class="bi bi-clock-history app-alert__icon"></i>
                                    <div class="flex-grow-1">Sesi Anda berakhir karena tidak ada aktivitas. Silakan login kembali untuk melanjutkan.</div>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            @include('partials.alerts')

                            <form method="post" action="{{ route('login.attempt') }}" class="auth-form auth-form-compact">
                                @csrf
                                <div class="auth-input-group">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="contoh: siswa@sekolah.sch.id" required autofocus>
                                </div>
                                <div class="auth-input-group">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password akun kamu" required>
                                </div>
                                <div class="auth-form-row">
                                    <div class="form-check auth-remember">
                                        <input class="form-check-input" type="checkbox" name="remember" value="1" id="remember" @checked(old('remember'))>
                                        <label class="form-check-label" for="remember">Ingat saya di perangkat ini</label>
                                    </div>
                                    <a href="{{ route('password.request') }}" class="auth-inline-link">Lupa password?</a>
                                </div>
                                <button class="btn btn-primary w-100" type="submit" data-loading-text="Masuk..."><i class="bi bi-box-arrow-in-right"></i>Masuk</button>
                            </form>

                            <div class="auth-divider"><span>ATAU</span></div>
                            <a href="{{ route('register') }}" class="btn btn-outline-primary w-100">
                                <i class="bi bi-person-plus"></i>Buat Akun Siswa
                            </a>

                            <div class="auth-footer-links">
                                <a href="{{ route('landing') }}"><i class="bi bi-arrow-left"></i>Kembali ke halaman publik</a>
                                <span class="small text-muted">Akses aman untuk admin, siswa, pembina, dan kepala sekolah.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
