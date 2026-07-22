<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pemeliharaan Sistem</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="public-body">
    <section class="auth-page auth-page-public">
        <div class="container">
            <div class="auth-card auth-card-login">
                <div class="row g-0 align-items-stretch">
                    <div class="col-lg-5">
                        <div class="auth-hero">
                            <span class="auth-hero-badge"><i class="bi bi-tools"></i>Pemeliharaan</span>
                            <h1>Website sedang dalam pemeliharaan.</h1>
                            <p>{{ $message }}</p>
                            <div class="auth-helper-list">
                                <div class="auth-helper-item">
                                    <strong>Akses sementara dibatasi</strong>
                                    Silakan kembali beberapa saat lagi setelah pemeliharaan selesai.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="auth-form-wrap">
                            <div class="auth-form-header">
                                <span class="auth-section-kicker">Informasi</span>
                                <h2>Pemeliharaan Sedang Berlangsung</h2>
                                <p>Fitur publik dan dashboard non-super-admin untuk sementara tidak tersedia agar perubahan sistem tetap aman.</p>
                            </div>

                            <div class="auth-footer-links mt-4">
                                <span class="small text-muted">Sistem Informasi Ekstrakurikuler SMA Negeri 1 Gorontalo</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
</html>
