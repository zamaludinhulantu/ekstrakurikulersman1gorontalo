@extends('layouts.app')

@section('page_title', 'Detail Pendaftar')
@section('page_subtitle', 'Pantau data minat, bakat, dan histori tes siswa')

@section('content')
    <div class="split-actions mb-3">
        <a href="{{ route('admin.registrations.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i>Kembali ke daftar pendaftar</a>
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-5">
            <div class="card h-100">
                <div class="card-header">Ringkasan Pendaftaran</div>
                <div class="card-body">
                    <div class="data-points">
                        <div class="data-point"><div class="data-point-label">Nama siswa</div><p class="data-point-value mb-0">{{ $registration->student->user->name ?? '-' }}</p></div>
                        <div class="data-point"><div class="data-point-label">Ekstrakurikuler</div><p class="data-point-value mb-0">{{ $registration->extracurricular->name ?? '-' }}</p></div>
                        <div class="data-point"><div class="data-point-label">Cabang dipilih</div><p class="data-point-value mb-0">{{ $registration->selected_branch_label }}</p></div>
                        <div class="data-point"><div class="data-point-label">Status</div><p class="data-point-value mb-0"><span class="badge" data-status="{{ $registration->status }}">{{ $registration->status }}</span></p></div>
                        <div class="data-point"><div class="data-point-label">Verifikator</div><p class="data-point-value mb-0">{{ $registration->verifier->name ?? '-' }}</p></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-7">
            <div class="card h-100">
                <div class="card-header">Data Minat dan Bakat</div>
                <div class="card-body">
                    @include('partials.registration-talent-summary', ['registration' => $registration])
                </div>
            </div>
        </div>
    </div>
@endsection
