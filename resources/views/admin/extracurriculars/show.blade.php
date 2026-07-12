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
                <div class="col-md-6"><p class="text-muted small mb-1">Prestasi Tercatat</p><p class="mb-0">{{ $extracurricular->achievements->count() }} prestasi</p></div>
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
                <div class="card-header">Prestasi Ekstrakurikuler</div>
                <div class="card-body">
                    <form method="post" action="{{ route('admin.extracurricular-achievements.store', $extracurricular) }}" class="row g-3 mb-3">
                        @csrf
                        <div class="col-12">
                            <label class="form-label" for="achievement_title">Judul Prestasi</label>
                            <input id="achievement_title" type="text" name="title" value="{{ old('title') }}" class="form-control" placeholder="Contoh: Juara 1 Lomba PBB Tingkat Kota" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="achievement_date">Tanggal</label>
                            <input id="achievement_date" type="date" name="achievement_date" value="{{ old('achievement_date') }}" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="achievement_description">Deskripsi</label>
                            <textarea id="achievement_description" name="description" class="form-control" rows="3" placeholder="Keterangan tambahan">{{ old('description') }}</textarea>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary" type="submit"><i class="bi bi-plus-circle"></i>Tambah Prestasi</button>
                        </div>
                    </form>

                    <div class="info-list">
                        @forelse($extracurricular->achievements as $achievement)
                            <div class="info-item">
                                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                                    <div>
                                        <div class="title">{{ $achievement->title }}</div>
                                        @if($achievement->achievement_date)
                                            <div class="small text-muted mt-1">{{ optional($achievement->achievement_date)->format('d-m-Y') }}</div>
                                        @endif
                                        @if($achievement->description)
                                            <div class="small text-muted mt-2">{{ $achievement->description }}</div>
                                        @endif
                                    </div>
                                    <form method="post" action="{{ route('admin.extracurricular-achievements.destroy', [$extracurricular, $achievement]) }}" onsubmit="return confirm('Hapus prestasi ini?')">
                                        @csrf
                                        @method('delete')
                                        <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i>Hapus</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state py-3">
                                <div class="icon"><i class="bi bi-award"></i></div>
                                <p class="mb-0">Belum ada prestasi ekstrakurikuler.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
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
