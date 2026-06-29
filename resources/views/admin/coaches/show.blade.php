@extends('layouts.app')

@section('page_title', 'Detail Pembina')
@section('page_subtitle', $coach->user->name)

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6"><p class="text-muted small mb-1">Nama</p><p class="mb-0 fw-semibold">{{ $coach->user->name }}</p></div>
                <div class="col-md-6"><p class="text-muted small mb-1">NIP</p><p class="mb-0">{{ $coach->nip }}</p></div>
                <div class="col-md-6"><p class="text-muted small mb-1">Email</p><p class="mb-0">{{ $coach->user->email }}</p></div>
                <div class="col-md-6"><p class="text-muted small mb-1">Telepon</p><p class="mb-0">{{ $coach->user->phone ?? '-' }}</p></div>
                <div class="col-12"><p class="text-muted small mb-1">Bio</p><p class="mb-0">{{ $coach->bio ?? '-' }}</p></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Ekstrakurikuler Dibina</div>
        <div class="card-body p-0">
            <ul class="list-group list-group-flush">
                @forelse($coach->extracurriculars->sortBy('name') as $item)
                    <li class="list-group-item">{{ $item->name }}</li>
                @empty
                    <li class="list-group-item text-muted">Belum ada ekstrakurikuler.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <a href="{{ route('admin.coaches.index') }}" class="btn btn-outline-secondary mt-3"><i class="bi bi-arrow-left"></i>Kembali</a>
@endsection
