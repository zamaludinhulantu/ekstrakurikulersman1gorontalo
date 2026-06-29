<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrasi Siswa | Sistem Informasi Ekstrakurikuler</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            background: #eef3f9;
        }

        .auth-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 18px;
        }

        .auth-card-register {
            width: 100%;
            max-width: 1180px;
            background: #ffffff;
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.12);
        }

        .auth-hero {
            min-height: 100%;
            padding: 42px 34px;
            color: #ffffff;
            background: linear-gradient(145deg, #1746c7, #3b82f6);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .auth-hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            width: fit-content;
            padding: 9px 14px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
            background: rgba(255, 255, 255, 0.16);
            margin-bottom: 22px;
        }

        .auth-hero h1 {
            font-size: 32px;
            line-height: 1.18;
            font-weight: 800;
            margin-bottom: 16px;
            letter-spacing: -0.5px;
        }

        .auth-hero p {
            font-size: 15px;
            line-height: 1.65;
            margin-bottom: 22px;
            color: rgba(255, 255, 255, 0.92);
        }

        .auth-helper-list {
            display: grid;
            gap: 12px;
        }

        .auth-helper-item {
            padding: 15px;
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.13);
            color: #ffffff;
            font-size: 14px;
            line-height: 1.5;
            border: 1px solid rgba(255, 255, 255, 0.12);
        }

        .auth-helper-item strong {
            display: block;
            font-size: 14px;
            margin-bottom: 4px;
        }

        .auth-form-wrap {
            padding: 30px 34px;
            background: #ffffff;
        }

        .auth-form-header {
            margin-bottom: 18px;
        }

        .auth-section-kicker {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #2563eb;
            margin-bottom: 4px;
        }

        .auth-form-header h2 {
            font-size: 28px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 6px;
        }

        .auth-form-header p {
            font-size: 15px;
            color: #64748b;
            margin-bottom: 0;
            line-height: 1.5;
        }

        .register-section {
            padding: 18px;
            border: 1px solid #e5eaf3;
            border-radius: 16px;
            background: #ffffff;
            margin-bottom: 14px;
        }

        .register-section-header {
            margin-bottom: 14px;
        }

        .register-section-header h3 {
            font-size: 18px;
            font-weight: 800;
            margin-bottom: 3px;
            color: #0f172a;
        }

        .register-section-header p {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 0;
        }

        .form-label {
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 6px;
            color: #334155;
        }

        .form-control,
        .form-select {
            height: 48px;
            font-size: 15px;
            border-radius: 11px;
            border: 1px solid #dbe3ef;
            padding-left: 14px;
            padding-right: 14px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.15);
        }

        textarea.form-control {
            min-height: 48px;
            height: 48px;
            resize: vertical;
            padding-top: 12px;
        }

        .auth-form .btn,
        .btn-outline-primary {
            height: 48px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 15px;
        }

        .auth-divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 16px 0;
            color: #64748b;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .auth-divider::before,
        .auth-divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #e5eaf3;
        }

        .auth-footer-links {
            margin-top: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            font-size: 14px;
        }

        .auth-footer-links a {
            text-decoration: none;
            font-weight: 700;
            color: #2563eb;
        }

        .auth-footer-links a:hover {
            color: #1d4ed8;
        }

        @media (max-width: 991px) {
            .auth-page {
                align-items: flex-start;
                padding: 12px;
            }

            .auth-card-register {
                border-radius: 18px;
            }

            .auth-hero {
                padding: 28px 24px;
            }

            .auth-hero h1 {
                font-size: 26px;
            }

            .auth-form-wrap {
                padding: 24px;
            }
        }

        @media (max-width: 576px) {
            .auth-page {
                padding: 8px;
            }

            .auth-hero {
                padding: 24px 18px;
            }

            .auth-hero h1 {
                font-size: 24px;
            }

            .auth-hero p {
                font-size: 14px;
            }

            .auth-form-wrap {
                padding: 20px 16px;
            }

            .register-section {
                padding: 14px;
            }

            .auth-footer-links {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>

<body>
<div class="auth-page">
    <div class="auth-card auth-card-register">
        <div class="row g-0 align-items-stretch">

            {{-- Bagian kiri --}}
            <div class="col-lg-4">
                <div class="auth-hero">
                    <span class="auth-hero-badge">
                        <i class="bi bi-person-plus"></i>
                        Akun Siswa Baru
                    </span>

                    <h1>Daftar akun siswa untuk mulai mengikuti ekstrakurikuler.</h1>

                    <p>
                        Lengkapi data akun dan identitas siswa dengan benar.
                        Setelah berhasil, kamu bisa masuk ke dashboard dan melanjutkan pendaftaran ekskul.
                    </p>

                    <div class="auth-helper-list">
                        <div class="auth-helper-item">
                            <strong>Siapkan data penting</strong>
                            Isi nama, email, nomor telepon, dan data wali agar mudah diverifikasi.
                        </div>

                        <div class="auth-helper-item">
                            <strong>Setelah akun jadi</strong>
                            Kamu bisa memilih ekskul, mengirim pendaftaran, dan memantau statusnya.
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bagian kanan --}}
            <div class="col-lg-8">
                <div class="auth-form-wrap">
                    <div class="auth-form-header">
                        <span class="auth-section-kicker">Registrasi</span>
                        <h2>Registrasi Akun Siswa</h2>
                        <p>
                            Isi data berikut dengan benar agar akun siswa dapat digunakan untuk mendaftar ekstrakurikuler.
                        </p>
                    </div>

                    @include('partials.alerts')

                    <form method="post" action="{{ route('register.store') }}" class="auth-form">
                        @csrf

                        {{-- Data Akun --}}
                        <div class="register-section">
                            <div class="register-section-header">
                                <h3>Data Akun</h3>
                                <p>Informasi ini dipakai untuk login ke sistem.</p>
                            </div>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Nama Lengkap</label>
                                    <input
                                        type="text"
                                        id="name"
                                        name="name"
                                        value="{{ old('name') }}"
                                        class="form-control"
                                        placeholder="Nama lengkap siswa"
                                        required
                                        autofocus
                                    >
                                </div>

                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input
                                        type="email"
                                        id="email"
                                        name="email"
                                        value="{{ old('email') }}"
                                        class="form-control"
                                        placeholder="contoh: siswa@email.com"
                                        required
                                    >
                                </div>

                                <div class="col-md-6">
                                    <label for="password" class="form-label">Password</label>
                                    <input
                                        type="password"
                                        id="password"
                                        name="password"
                                        class="form-control"
                                        placeholder="Minimal 8 karakter"
                                        required
                                    >
                                </div>

                                <div class="col-md-6">
                                    <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                                    <input
                                        type="password"
                                        id="password_confirmation"
                                        name="password_confirmation"
                                        class="form-control"
                                        placeholder="Ulangi password"
                                        required
                                    >
                                </div>
                            </div>
                        </div>

                        {{-- Profil Siswa --}}
                        <div class="register-section">
                            <div class="register-section-header">
                                <h3>Profil Siswa</h3>
                                <p>Lengkapi identitas dasar untuk membantu proses verifikasi.</p>
                            </div>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label for="gender" class="form-label">Jenis Kelamin</label>
                                    <select id="gender" name="gender" class="form-select" required>
                                        <option value="" disabled @selected(old('gender') === null)>
                                            Pilih jenis kelamin
                                        </option>
                                        <option value="L" @selected(old('gender') === 'L')>
                                            Laki-laki
                                        </option>
                                        <option value="P" @selected(old('gender') === 'P')>
                                            Perempuan
                                        </option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="date_of_birth" class="form-label">Tanggal Lahir</label>
                                    <input
                                        type="date"
                                        id="date_of_birth"
                                        name="date_of_birth"
                                        value="{{ old('date_of_birth') }}"
                                        class="form-control"
                                    >
                                </div>

                                <div class="col-md-6">
                                    <label for="phone" class="form-label">No. Telepon</label>
                                    <input
                                        type="text"
                                        id="phone"
                                        name="phone"
                                        value="{{ old('phone') }}"
                                        class="form-control"
                                        placeholder="08xxxxxxxxxx"
                                    >
                                </div>

                                <div class="col-md-6">
                                    <label for="parent_phone" class="form-label">No. Telepon Orang Tua</label>
                                    <input
                                        type="text"
                                        id="parent_phone"
                                        name="parent_phone"
                                        value="{{ old('parent_phone') }}"
                                        class="form-control"
                                        placeholder="08xxxxxxxxxx"
                                    >
                                </div>

                                <div class="col-md-6">
                                    <label for="parent_name" class="form-label">Nama Orang Tua / Wali</label>
                                    <input
                                        type="text"
                                        id="parent_name"
                                        name="parent_name"
                                        value="{{ old('parent_name') }}"
                                        class="form-control"
                                        placeholder="Nama orang tua atau wali"
                                    >
                                </div>

                                <div class="col-md-6">
                                    <label for="address" class="form-label">Alamat</label>
                                    <textarea
                                        id="address"
                                        name="address"
                                        class="form-control"
                                        rows="1"
                                        placeholder="Alamat siswa"
                                    >{{ old('address') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <button class="btn btn-primary w-100 mt-3" type="submit">
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
</body>
</html>