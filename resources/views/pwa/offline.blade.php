<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Offline | Sistem Informasi Ekstrakurikuler</title>
    @vite(['resources/css/app.css'])
</head>
<body class="public-body">
    <main class="offline-page">
        <div class="offline-card">
            <div class="offline-card__icon"><i class="bi bi-wifi-off"></i></div>
            <h1>Perangkat sedang offline</h1>
            <p>Sebagian informasi yang sebelumnya sudah dibuka mungkin masih tersedia. Pendaftaran, presensi, verifikasi, upload, dan perubahan data tetap memerlukan koneksi internet.</p>
            <div class="offline-card__actions">
                <button type="button" class="btn btn-primary" onclick="window.location.reload()">Coba lagi</button>
                <a href="{{ route('landing') }}" class="btn btn-outline-secondary">Buka beranda publik</a>
            </div>
        </div>
    </main>
</body>
</html>
