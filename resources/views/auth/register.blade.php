@extends('layouts.public')

@section('title', 'Registrasi Siswa | Sistem Informasi Ekstrakurikuler SMA Negeri 1 Gorontalo')

@section('content')
    <section class="auth-page auth-page-public">
        <div class="container">
            <div class="auth-card auth-card-register">
                <div class="row g-0 align-items-stretch">
                    <div class="col-lg-4">
                        <div class="auth-hero">
                            <span class="auth-hero-badge">
                                <i class="bi bi-person-plus"></i>
                                Akun Siswa Baru
                            </span>

                            <h1>Buat akun siswa untuk mulai mengikuti ekstrakurikuler.</h1>

                            <p>Lengkapi data akun dan identitas siswa dengan benar. Setelah berhasil, kamu bisa masuk ke dashboard dan melanjutkan pendaftaran kegiatan.</p>

                            <div class="auth-helper-list">
                                <div class="auth-helper-item">
                                    <strong>Siapkan data penting</strong>
                                    Isi nama, email, nomor telepon, dan data wali agar mudah diverifikasi.
                                </div>

                                <div class="auth-helper-item">
                                    <strong>Setelah akun jadi</strong>
                                    Kamu bisa memilih kegiatan, mengirim pendaftaran, dan memantau statusnya.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="auth-form-wrap">
                            <div class="auth-form-header">
                                <span class="auth-section-kicker">Registrasi</span>
                                <h2>Registrasi Akun Siswa</h2>
                                <p>Isi data berikut dengan benar agar akun siswa dapat digunakan untuk mendaftar ekstrakurikuler.</p>
                            </div>

                            @include('partials.alerts')

                            <form method="post" action="{{ route('register.store') }}" class="auth-form" data-loading-scope>
                                @csrf

                                <div class="register-section">
                                    <div class="register-section-header">
                                        <h3>Data Akun</h3>
                                        <p>Informasi ini dipakai untuk login ke sistem.</p>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="name" class="form-label">Nama Lengkap</label>
                                            <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-control" placeholder="Nama lengkap siswa" required autofocus>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="contoh: siswa@email.com" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="password" class="form-label">Password</label>
                                            <input type="password" id="password" name="password" class="form-control" placeholder="Minimal 8 karakter" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Ulangi password" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="register-section">
                                    <div class="register-section-header">
                                        <h3>Profil Siswa</h3>
                                        <p>Lengkapi identitas dasar untuk membantu proses verifikasi.</p>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="class_name" class="form-label">Kelas</label>
                                            <select id="class_name" name="class_name" class="form-select">
                                                <option value="">Pilih kelas</option>
                                                @foreach(($classOptions ?? []) as $value => $label)
                                                    <option value="{{ $value }}" @selected(old('class_name') === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="gender" class="form-label">Jenis Kelamin</label>
                                            <select id="gender" name="gender" class="form-select" required>
                                                <option value="" disabled @selected(old('gender') === null)>Pilih jenis kelamin</option>
                                                <option value="L" @selected(old('gender') === 'L')>Laki-laki</option>
                                                <option value="P" @selected(old('gender') === 'P')>Perempuan</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="date_of_birth" class="form-label">Tanggal Lahir</label>
                                            <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}" class="form-control">
                                        </div>

                                        <div class="col-md-6">
                                            <label for="phone" class="form-label">No. Telepon</label>
                                            <input type="text" id="phone" name="phone" value="{{ old('phone') }}" class="form-control" placeholder="08xxxxxxxxxx">
                                        </div>

                                        <div class="col-md-6">
                                            <label for="parent_phone" class="form-label">No. Telepon Orang Tua</label>
                                            <input type="text" id="parent_phone" name="parent_phone" value="{{ old('parent_phone') }}" class="form-control" placeholder="08xxxxxxxxxx">
                                        </div>

                                        <div class="col-md-6">
                                            <label for="parent_name" class="form-label">Nama Orang Tua / Wali</label>
                                            <input type="text" id="parent_name" name="parent_name" value="{{ old('parent_name') }}" class="form-control" placeholder="Nama orang tua atau wali">
                                        </div>

                                        <div class="col-md-6">
                                            <label for="address" class="form-label">Alamat</label>
                                            <textarea id="address" name="address" class="form-control" rows="1" placeholder="Alamat siswa">{{ old('address') }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <button class="btn btn-primary w-100 mt-3" type="submit" data-loading-text="Memproses registrasi...">
                                    <i class="bi bi-person-check"></i>
                                    Daftar Akun Siswa
                                </button>
                            </form>

                            <div class="auth-divider">
                                <span>Sudah punya akun?</span>
                            </div>

                            <a href="{{ route('login') }}" class="btn btn-outline-primary w-100">
                                <i class="bi bi-box-arrow-in-right"></i>
                                Masuk ke Sistem
                            </a>

                            <div class="auth-footer-links">
                                <a href="{{ route('landing') }}">
                                    <i class="bi bi-arrow-left"></i>
                                    Kembali ke halaman publik
                                </a>

                                <span class="small text-muted">
                                    Registrasi ini hanya untuk akun siswa.
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
