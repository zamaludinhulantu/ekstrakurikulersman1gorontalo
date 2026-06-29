@extends('layouts.app')

@section('page_title', 'Ekstrakurikuler yang Dibina')
@section('page_subtitle', 'Daftar kegiatan yang menjadi tanggung jawab pembina')

@section('content')
    <div class="row g-3">
        @forelse($extracurriculars as $item)
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <h5 class="mb-0">{{ $item->name }}</h5>
                            <span class="badge" data-status="{{ $item->is_active ? 'active' : 'inactive' }}">{{ $item->is_active ? 'Aktif' : 'Tidak Aktif' }}</span>
                        </div>
                        <p class="text-muted small mb-2">{{ \Illuminate\Support\Str::limit($item->description, 110) }}</p>
                        <p class="mb-3">Peserta aktif: <strong>{{ $item->participants_count }}</strong></p>
                        <div class="quick-actions mt-auto">
                            <a href="{{ route('coach.registrations.index', ['extracurricular_id' => $item->id]) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-clipboard-check"></i>Lihat Pendaftar</a>
                            <a href="{{ route('coach.extracurriculars.participants', $item) }}" class="btn btn-primary btn-sm"><i class="bi bi-people"></i>Lihat Peserta</a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="empty-state">
                        <div class="icon"><i class="bi bi-inbox"></i></div>
                        <p class="mb-0">Belum ada ekstrakurikuler yang dibina.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
@endsection
