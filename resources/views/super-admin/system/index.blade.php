@extends('layouts.app')

@section('page_title', 'Pengaturan Sistem')
@section('page_subtitle', 'Kelola konfigurasi sensitif seperti email pengiriman sistem langsung dari area super admin.')

@section('content')
    <div class="row g-3 mb-3">
        <div class="col-12 col-md-4">
            <div class="card h-100">
                <div class="card-body stat-card">
                    <span class="stat-icon"><i class="bi bi-shield-lock"></i></span>
                    <p class="label">Super Admin Aktif</p>
                    <p class="value">{{ $activeSuperAdmins }}</p>
                    <div class="trend">Kontrol penuh sistem</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card h-100">
                <div class="card-body stat-card">
                    <span class="stat-icon"><i class="bi bi-people"></i></span>
                    <p class="label">Admin Aktif</p>
                    <p class="value">{{ $activeAdmins }}</p>
                    <div class="trend">Operator harian aktif</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card h-100">
                <div class="card-body stat-card">
                    <span class="stat-icon"><i class="bi bi-envelope"></i></span>
                    <p class="label">Mailer Aktif</p>
                    <p class="value">{{ strtoupper($mailMailer ?: '-') }}</p>
                    <div class="trend">{{ $mailFromAddress ?: 'Alamat pengirim belum diatur' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-4">
            <div class="card h-100">
                <div class="card-header">Ringkasan Kontrol</div>
                <div class="card-body">
                    <div class="info-list">
                        <div class="info-item">
                            <div class="title">Super Admin Aktif</div>
                            <div class="small text-muted mt-1">{{ $activeSuperAdmins }} akun aktif memiliki kontrol penuh sistem.</div>
                        </div>
                        <div class="info-item">
                            <div class="title">Admin/Kesiswaan Aktif</div>
                            <div class="small text-muted mt-1">{{ $activeAdmins }} akun aktif menangani operasional harian.</div>
                        </div>
                        <div class="info-item">
                            <div class="title">Environment</div>
                            <div class="small text-muted mt-1">{{ strtoupper($appEnvironment) }}</div>
                        </div>
                        <div class="info-item">
                            <div class="title">Alamat Pengirim</div>
                            <div class="small text-muted mt-1">{{ $mailFromAddress ?: '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-8">
            <div class="card mb-3">
                <div class="card-header">Konfigurasi Email Sistem</div>
                <div class="card-body">
                    <div class="page-summary-banner mb-3">
                        <div class="data-point-label">Mode aman</div>
                        <p class="data-point-value mb-2">Pengaturan email disimpan di database dan dimuat saat aplikasi berjalan.</p>
                        <div class="helper-text mb-0">Password SMTP tidak ditampilkan kembali. Kosongkan password jika tidak ingin mengubahnya.</div>
                    </div>

                    <form method="post" action="{{ route('super-admin.system.email.update') }}" class="row g-3">
                        @csrf
                        @method('put')

                        <div class="col-md-4">
                            <label class="form-label" for="mail_mailer">Mailer</label>
                            <input type="text" id="mail_mailer" name="mail_mailer" value="{{ old('mail_mailer', $emailSettings['mail_mailer']) }}" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="mail_smtp_host">SMTP Host</label>
                            <input type="text" id="mail_smtp_host" name="mail_smtp_host" value="{{ old('mail_smtp_host', $emailSettings['mail_smtp_host']) }}" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="mail_smtp_port">SMTP Port</label>
                            <input type="number" id="mail_smtp_port" name="mail_smtp_port" value="{{ old('mail_smtp_port', $emailSettings['mail_smtp_port']) }}" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="mail_smtp_username">SMTP Username</label>
                            <input type="text" id="mail_smtp_username" name="mail_smtp_username" value="{{ old('mail_smtp_username', $emailSettings['mail_smtp_username']) }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="mail_smtp_password">SMTP Password Baru</label>
                            <input type="password" id="mail_smtp_password" name="mail_smtp_password" class="form-control" placeholder="Kosongkan jika tidak diubah">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="mail_smtp_encryption">Encryption</label>
                            <input type="text" id="mail_smtp_encryption" name="mail_smtp_encryption" value="{{ old('mail_smtp_encryption', $emailSettings['mail_smtp_encryption']) }}" class="form-control" placeholder="tls / ssl">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="mail_from_address">From Address</label>
                            <input type="email" id="mail_from_address" name="mail_from_address" value="{{ old('mail_from_address', $emailSettings['mail_from_address']) }}" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="mail_from_name">From Name</label>
                            <input type="text" id="mail_from_name" name="mail_from_name" value="{{ old('mail_from_name', $emailSettings['mail_from_name']) }}" class="form-control" required>
                        </div>

                        <div class="col-12">
                            <div class="form-actions mt-2">
                                <button class="btn btn-primary" type="submit" data-loading-text="Menyimpan konfigurasi...">
                                    <i class="bi bi-save"></i>Simpan Konfigurasi Email
                                </button>
                                <a href="{{ route('super-admin.audit-logs.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-journal-text"></i>Lihat Audit Log
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card h-100">
                <div class="card-header">Uji Pengiriman Email</div>
                <div class="card-body">
                    <div class="data-point mb-3">
                        <div class="data-point-label">Cek hasil konfigurasi terbaru</div>
                        <p class="data-point-value mb-2">Kirim satu email uji untuk memastikan mailer, host, port, username, dan pengirim sudah berfungsi.</p>
                        <div class="helper-text mb-0">Email uji akan memakai konfigurasi aktif yang sedang tersimpan di sistem.</div>
                    </div>

                    <form method="post" action="{{ route('super-admin.system.email.test') }}" class="row g-3">
                        @csrf
                        <div class="col-md-8">
                            <label class="form-label" for="test_email">Tujuan Email Uji</label>
                            <input type="email" id="test_email" name="test_email" value="{{ old('test_email', $mailFromAddress) }}" class="form-control" placeholder="contoh: admin@sekolah.sch.id" required>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button class="btn btn-outline-primary w-100" type="submit" data-loading-text="Mengirim email uji...">
                                <i class="bi bi-send-check"></i>Kirim Email Uji
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
