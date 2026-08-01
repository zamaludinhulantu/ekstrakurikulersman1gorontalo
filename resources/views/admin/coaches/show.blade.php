@extends('layouts.app')

@section('page_title', 'Detail Pembina')
@section('page_subtitle', $coach->user?->name ?? 'Profil pembina')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <a href="{{ route('admin.coaches.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i>Kembali</a>
        <a href="{{ route('admin.coaches.edit', $coach) }}" class="btn btn-primary"><i class="bi bi-pencil-square" aria-hidden="true"></i>Edit dan Atur Penugasan</a>
    </div>

    <div class="coach-detail-grid">
        <section class="card">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h2 class="section-title mb-0">Profil Pembina</h2>
                <span class="badge" data-status="{{ $coach->user?->is_active ? 'active' : 'inactive' }}">{{ $coach->user?->is_active ? 'Akun Aktif' : 'Akun Tidak Aktif' }}</span>
            </div>
            <div class="card-body">
                <x-coach.identity :coach="$coach" class="coach-detail-identity mb-4" />
                <dl class="coach-detail-list">
                    <div><dt>NIP</dt><dd class="coach-nip">{{ $coach->nip ?: 'Belum diisi' }}</dd></div>
                    <div><dt>Email</dt><dd class="text-break">{{ $coach->user?->email ?: '-' }}</dd></div>
                    <div><dt>Telepon</dt><dd>{{ $coach->user?->phone ?: 'Belum diisi' }}</dd></div>
                    <div><dt>Alamat</dt><dd>{{ $coach->user?->address ?: 'Belum diisi' }}</dd></div>
                    <div class="coach-detail-list__wide"><dt>Bio</dt><dd>{{ $coach->bio ?: 'Belum diisi' }}</dd></div>
                </dl>
            </div>
        </section>

        <aside class="card">
            <div class="card-header">
                <h2 class="section-title mb-1">Ringkasan Operasional</h2>
                <p class="text-muted small mb-0">Riwayat ini dipertahankan untuk menjaga data laporan.</p>
            </div>
            <div class="card-body coach-operation-summary">
                <div><strong>{{ $coach->extracurriculars->count() }}</strong><span>Kegiatan binaan</span></div>
                <div><strong>{{ $coach->schedules_count }}</strong><span>Jadwal</span></div>
                <div><strong>{{ $coach->assessments_count }}</strong><span>Penilaian</span></div>
                <div><strong>{{ $coach->talent_test_results_count }}</strong><span>Hasil tes bakat</span></div>
            </div>
        </aside>
    </div>

    <section class="card mt-3">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h2 class="section-title mb-1">Kegiatan Binaan</h2>
                <p class="text-muted small mb-0">Daftar lengkap kegiatan yang ditugaskan kepada pembina.</p>
            </div>
            <a href="{{ route('admin.coaches.edit', $coach) }}#coach_extracurricular_picker" class="btn btn-sm btn-outline-primary"><i class="bi bi-diagram-3" aria-hidden="true"></i>Atur Penugasan</a>
        </div>
        <div class="card-body">
            @if($coach->extracurriculars->isNotEmpty())
                <div class="coach-detail-activities">
                    @foreach($coach->extracurriculars->sortBy('name') as $activity)
                        <a href="{{ route('admin.extracurriculars.show', $activity) }}" class="student-activity-link">{{ $activity->name }}</a>
                    @endforeach
                </div>
            @else
                <div class="empty-state py-4">
                    <div class="icon"><i class="bi bi-diagram-3" aria-hidden="true"></i></div>
                    <p class="mb-2">Pembina belum memiliki kegiatan binaan.</p>
                    <a href="{{ route('admin.coaches.edit', $coach) }}#coach_extracurricular_picker" class="btn btn-outline-primary">Atur Penugasan</a>
                </div>
            @endif
        </div>
    </section>
@endsection
