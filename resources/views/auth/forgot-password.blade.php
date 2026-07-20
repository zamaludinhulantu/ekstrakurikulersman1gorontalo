@extends('layouts.public')

@section('title', 'Lupa Password | Sistem Informasi Ekstrakurikuler SMA Negeri 1 Gorontalo')

@section('content')
    <section class="auth-page auth-page-public">
        <div class="container">
            <div class="auth-card auth-card-login">
                <div class="row g-0 align-items-stretch">
                    <div class="col-lg-5">
                        <div class="auth-hero">
                            <span class="auth-hero-badge"><i class="bi bi-envelope-paper"></i>Reset Password</span>
                            <h1>Reset password akun melalui email.</h1>
                            <p>Masukkan email akun yang terdaftar. Sistem akan mengirim tautan aman untuk membuat password baru.</p>
                            <div class="auth-helper-list">
                                <div class="auth-helper-item">
                                    <strong>Cek kotak masuk email</strong>
                                    Tautan reset akan dikirim ke email yang terdaftar pada akun.
                                </div>
                                <div class="auth-helper-item">
                                    <strong>Gunakan tautan terbaru</strong>
                                    Jika meminta beberapa kali, pakai email reset yang paling baru.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="auth-form-wrap">
                            <div class="auth-form-header">
                                <span class="auth-section-kicker">Lupa Password</span>
                                <h2>Kirim Tautan Reset</h2>
                                <p>Masukkan email akun Anda. Jika email terdaftar, kami akan mengirim tautan reset password.</p>
                            </div>

                            @include('partials.alerts')

                            <form method="post" action="{{ route('password.email') }}" class="auth-form auth-form-compact">
                                @csrf
                                <div class="auth-input-group">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="contoh: siswa@email.com" required autofocus>
                                </div>
                                <button class="btn btn-primary w-100" type="submit" data-loading-text="Mengirim...">
                                    <i class="bi bi-send"></i>Kirim Tautan Reset
                                </button>
                            </form>

                            <div class="auth-footer-links">
                                <a href="{{ route('login') }}"><i class="bi bi-arrow-left"></i>Kembali ke login</a>
                                <span class="small text-muted">Reset password berlaku untuk semua akun yang memakai email.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
