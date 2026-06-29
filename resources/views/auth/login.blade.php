<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Sistem Informasi Ekstrakurikuler</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="auth-page">
    <div class="auth-card auth-card-login">
        <div class="row g-0 align-items-stretch">
            <div class="col-lg-5">
                <div class="auth-hero">
                    <span class="auth-hero-badge"><i class="bi bi-shield-lock"></i>Akses Terproteksi</span>
                    <h1>Masuk untuk Melanjutkan Pendaftaran Ekstrakurikuler</h1>
                    <p>Siswa dapat mendaftar ekstrakurikuler dan memantau status pendaftaran. Admin dan pembina dapat mengelola data melalui dashboard.</p>
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
                                <div>Admin, pembina, siswa, dan kepala sekolah masuk dari halaman yang sama.</div>
                            </div>
                        </div>
                    </div>
                    <div class="auth-helper-list">
                        <div class="auth-helper-item">
                            <strong>Belum punya akun?</strong>
                            Daftar akun siswa terlebih dahulu sebelum memilih ekstrakurikuler.
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="auth-form-wrap">
                    <div class="auth-form-header">
                        <span class="auth-section-kicker">Login</span>
                        <h2>Masuk ke Sistem</h2>
                        <p>Gunakan email dan password akun kamu untuk masuk ke dashboard dan melanjutkan pendaftaran ekstrakurikuler.</p>
                    </div>

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
                        </div>
                        <button class="btn btn-primary w-100" type="submit"><i class="bi bi-box-arrow-in-right"></i>Masuk</button>
                    </form>

                    <div class="auth-divider"><span>ATAU</span></div>
                    <a href="{{ route('register') }}" class="btn btn-outline-primary w-100">
                        <i class="bi bi-person-plus"></i>Registrasi Akun Siswa
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
</body>
</html>
