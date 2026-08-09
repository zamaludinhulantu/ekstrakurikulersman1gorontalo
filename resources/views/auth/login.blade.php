@extends('layouts.public')

@section('title', 'Login | Sistem Informasi Ekstrakurikuler SMA Negeri 1 Gorontalo')

@section('content')
    <section class="auth-page auth-page-public">
        <div class="container">
            <div class="auth-card auth-card-login">
                <div class="row g-0 align-items-stretch">
                    <div class="col-lg-6">
                        <div class="auth-form-wrap auth-form-wrap-login">
                            <div class="auth-form-header">
                                <span class="auth-section-kicker">Login</span>
                                <h2>Masuk ke Sistem</h2>
                                <p>Gunakan email dan password akun Anda untuk melanjutkan.</p>
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

                            <a href="{{ route('register') }}" class="btn btn-outline-primary w-100 auth-register-button">
                                <i class="bi bi-person-plus"></i>Buat Akun Siswa
                            </a>

                            <div class="auth-footer-links auth-footer-login">
                                <a href="{{ route('landing') }}"><i class="bi bi-arrow-left"></i>Kembali ke halaman publik</a>
                                <span class="small text-muted">Akses aman untuk admin, siswa, pembina, dan kepala sekolah.</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <aside class="auth-login-intro">
                            <span class="auth-login-brand"><i class="bi bi-mortarboard-fill"></i>Sistem Informasi Ekstrakurikuler</span>
                            <div class="auth-login-intro-copy">
                                <span class="auth-section-kicker">SMA NEGERI 1 GORONTALO</span>
                                <h1>Selamat datang kembali</h1>
                                <p>Masuk untuk menjelajahi kegiatan, membaca informasi sekolah, dan melanjutkan pendaftaran ekstrakurikuler.</p>
                                <a href="{{ route('register') }}" class="auth-intro-link">Belum punya akun? Buat akun siswa</a>
                            </div>
                        </aside>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
