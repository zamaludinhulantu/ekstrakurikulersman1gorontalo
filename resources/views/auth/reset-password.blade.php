@extends('layouts.public')

@section('title', 'Reset Password | Sistem Informasi Ekstrakurikuler SMA Negeri 1 Gorontalo')

@section('content')
    <section class="auth-page auth-page-public">
        <div class="container">
            <div class="auth-card auth-card-login">
                <div class="row g-0 align-items-stretch">
                    <div class="col-lg-5">
                        <div class="auth-hero">
                            <span class="auth-hero-badge"><i class="bi bi-key"></i>Password Baru</span>
                            <h1>Buat password baru untuk akun Anda.</h1>
                            <p>Gunakan password yang kuat dan mudah Anda ingat. Setelah berhasil diubah, Anda bisa langsung login kembali.</p>
                            <div class="auth-helper-list">
                                <div class="auth-helper-item">
                                    <strong>Minimal 8 karakter</strong>
                                    Sebaiknya gunakan kombinasi huruf dan angka agar password lebih aman.
                                </div>
                                <div class="auth-helper-item">
                                    <strong>Login kembali setelah reset</strong>
                                    Selesai reset, Anda akan diarahkan kembali ke halaman login.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="auth-form-wrap">
                            <div class="auth-form-header">
                                <span class="auth-section-kicker">Reset Password</span>
                                <h2>Atur Password Baru</h2>
                                <p>Masukkan email akun dan password baru untuk menyelesaikan proses reset.</p>
                            </div>

                            @include('partials.alerts')

                            <form method="post" action="{{ route('password.update') }}" class="auth-form auth-form-compact">
                                @csrf
                                <input type="hidden" name="token" value="{{ $token }}">
                                <div class="auth-input-group">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" id="email" name="email" value="{{ old('email', $email) }}" class="form-control" placeholder="contoh: siswa@email.com" required autofocus>
                                </div>
                                <div class="auth-input-group">
                                    <label for="password" class="form-label">Password Baru</label>
                                    <input type="password" id="password" name="password" class="form-control" placeholder="Minimal 8 karakter" required>
                                </div>
                                <div class="auth-input-group">
                                    <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Ulangi password baru" required>
                                </div>
                                <button class="btn btn-primary w-100" type="submit" data-loading-text="Menyimpan...">
                                    <i class="bi bi-shield-check"></i>Simpan Password Baru
                                </button>
                            </form>

                            <div class="auth-footer-links">
                                <a href="{{ route('login') }}"><i class="bi bi-arrow-left"></i>Kembali ke login</a>
                                <span class="small text-muted">Tautan reset hanya berlaku sementara demi keamanan akun.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
