{{-- Tidak digunakan oleh route utama. Halaman publik menggunakan resources/views/public/landing.blade.php --}}
@extends('layouts.public')

@section('title', 'Sistem Informasi Ekstrakurikuler SMA Negeri 1 Gorontalo')

@section('content')
    <div class="container py-5">
        <div class="public-card p-4 p-md-5 text-center">
            <h1 class="h3 fw-bold mb-3">Sistem Informasi Ekstrakurikuler SMA Negeri 1 Gorontalo</h1>
            <p class="text-muted mb-4">Gunakan halaman publik utama untuk informasi ekstrakurikuler atau login untuk mengakses dashboard sesuai role.</p>
            <div class="d-flex justify-content-center gap-2 flex-wrap">
                <a href="{{ route('landing') }}" class="btn btn-primary"><i class="bi bi-house"></i>Halaman Publik</a>
                <a href="{{ route('login') }}" class="btn btn-outline-primary"><i class="bi bi-box-arrow-in-right"></i>Login</a>
            </div>
        </div>
    </div>
@endsection
