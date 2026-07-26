@extends('layouts.public')

@section('title', 'Verifikasi Email | Sistem Informasi Ekstrakurikuler SMA Negeri 1 Gorontalo')

@section('content')
    <section class="auth-page auth-page-public">
        <div class="container">
            <div class="auth-card auth-card-login">
                <div class="row g-0 align-items-stretch">
                    <div class="col-lg-5">
                        <div class="auth-hero">
                            <span class="auth-hero-badge"><i class="bi bi-envelope-check"></i>Verifikasi Akun</span>
                            <h1>Cek email aktif Anda sebelum login.</h1>
                            <p>Sistem akan mengirim tautan verifikasi ke email yang dipakai saat registrasi. Akun siswa belum dapat login sebelum verifikasi selesai.</p>
                            <div class="auth-helper-list">
                                <div class="auth-helper-item">
                                    <strong>Gunakan email aktif</strong>
                                    Pastikan alamat email benar, dapat diakses, dan bukan email yang sudah tidak dipakai.
                                </div>
                                <div class="auth-helper-item">
                                    <strong>Tidak menemukan email?</strong>
                                    Periksa folder inbox, spam, promosi, lalu kirim ulang tautan verifikasi jika diperlukan.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="auth-form-wrap">
                            <div class="auth-form-header">
                                <span class="auth-section-kicker">Verifikasi Email</span>
                                <h2>Aktifkan Akun Siswa</h2>
                                <p>Masukkan atau periksa email aktif Anda. Setelah email diverifikasi, Anda bisa login dan melanjutkan pendaftaran ekstrakurikuler.</p>
                            </div>

                            @include('partials.alerts')

                            <div class="alert alert-info app-alert" role="alert">
                                <i class="bi bi-envelope-paper-heart app-alert__icon"></i>
                                <div class="flex-grow-1">
                                    <strong>Email tujuan verifikasi:</strong>
                                    <div class="mt-1">{{ $email ?: 'Belum ada email yang dipilih.' }}</div>
                                </div>
                            </div>

                            <form method="post" action="{{ route('verification.send') }}" class="auth-form auth-form-compact">
                                @csrf
                                <div class="auth-input-group">
                                    <label for="email" class="form-label">Email aktif</label>
                                    <input type="email" id="email" name="email" value="{{ old('email', $email) }}" class="form-control" placeholder="contoh: siswa@email.com" required autofocus>
                                    <div class="form-text">Gunakan email aktif yang dapat menerima tautan verifikasi akun.</div>
                                </div>

                                <button class="btn btn-primary w-100" type="submit" data-loading-text="Mengirim ulang tautan...">
                                    <i class="bi bi-send-check"></i>
                                    Kirim Ulang Email Verifikasi
                                </button>
                            </form>

                            @if($verificationLinkPreview)
                                <div class="alert alert-warning app-alert mt-3" role="alert">
                                    <i class="bi bi-tools app-alert__icon"></i>
                                    <div class="flex-grow-1">
                                        <strong>Pratinjau lokal:</strong> email verifikasi sedang menggunakan mode pengujian lokal.
                                        <div class="mt-2">
                                            <a href="{{ $verificationLinkPreview }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-box-arrow-up-right"></i>Buka Tautan Verifikasi
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="auth-divider"><span>Langkah berikutnya</span></div>

                            <div class="auth-helper-list mb-3">
                                <div class="auth-helper-item">
                                    <strong>1. Cek inbox email</strong>
                                    Buka email verifikasi yang dikirim sistem.
                                </div>
                                <div class="auth-helper-item">
                                    <strong>2. Klik tautan verifikasi</strong>
                                    Setelah berhasil, kembali ke halaman login.
                                </div>
                            </div>

                            <a href="{{ route('login') }}" class="btn btn-outline-primary w-100">
                                <i class="bi bi-box-arrow-in-right"></i>
                                Kembali ke Login
                            </a>

                            <div class="auth-footer-links">
                                <a href="{{ route('register') }}"><i class="bi bi-arrow-left"></i>Kembali ke registrasi</a>
                                <span class="small text-muted">Akun siswa baru aktif setelah email diverifikasi.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
