@extends('layouts.app')

@section('page_title', 'Detail Ekstrakurikuler')
@section('page_subtitle', $extracurricular->name)

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                <h4 class="mb-0">{{ $extracurricular->name }}</h4>
                <span class="badge" data-status="{{ $extracurricular->is_active ? 'active' : 'inactive' }}">{{ $extracurricular->is_active ? 'Aktif' : 'Tidak Aktif' }}</span>
            </div>
            @if($extracurricular->image_path)
                <div class="mb-3">
                    <img src="{{ asset($extracurricular->image_path) }}" alt="{{ $extracurricular->name }}" style="width: 220px; max-width: 100%; height: 140px; object-fit: cover; border-radius: 16px; border: 1px solid #dbe5f0;">
                </div>
            @endif
            <div class="row g-3">
                <div class="col-md-6"><p class="text-muted small mb-1">Pembina</p><p class="mb-0">{{ $extracurricular->coach_names }}</p></div>
                <div class="col-md-6"><p class="text-muted small mb-1">Persyaratan</p><p class="mb-0">{{ $extracurricular->requirements ?? '-' }}</p></div>
                <div class="col-12"><p class="text-muted small mb-1">Deskripsi</p><p class="mb-0">{{ $extracurricular->description }}</p></div>
                <div class="col-md-6"><p class="text-muted small mb-1">Ringkasan Jadwal</p><p class="mb-0">{{ $extracurricular->schedule_overview ?? '-' }}</p></div>
                <div class="col-md-6"><p class="text-muted small mb-1">Ringkasan Prestasi</p><p class="mb-0">{{ $extracurricular->achievements_overview ?? '-' }}</p></div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header">Jadwal Kegiatan</div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($extracurricular->schedules as $schedule)
                            <li class="list-group-item">
                                <strong>{{ $schedule->title }}</strong>
                                <div class="small text-muted mt-1">{{ optional($schedule->activity_date)->format('d-m-Y') }} | {{ \Illuminate\Support\Str::substr($schedule->start_time, 0, 5) }} - {{ \Illuminate\Support\Str::substr($schedule->end_time, 0, 5) }} | {{ $schedule->location }}</div>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">Belum ada jadwal.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header">Data Pendaftaran</div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($extracurricular->registrations as $registration)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>{{ $registration->student->user->name ?? '-' }}</span>
                                <span class="badge" data-status="{{ $registration->status }}">{{ $registration->status }}</span>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">Belum ada pendaftaran.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <a href="{{ route('admin.extracurriculars.index') }}" class="btn btn-outline-secondary mt-3"><i class="bi bi-arrow-left"></i>Kembali</a>
@endsection
