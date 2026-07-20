@extends('layouts.app')

@section('page_title', 'Detail Pendaftar')
@section('page_subtitle', 'Lihat profil minat, bakat, dan histori tes siswa')

@section('content')
    <div class="split-actions mb-3">
        <a href="{{ route('coach.registrations.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i>Kembali ke daftar pendaftar</a>
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-5">
            <div class="card h-100">
                <div class="card-header">Ringkasan Siswa</div>
                <div class="card-body">
                    <div class="data-points">
                        <div class="data-point">
                            <div class="data-point-label">Nama siswa</div>
                            <p class="data-point-value mb-0">{{ $registration->student->user->name ?? '-' }}</p>
                        </div>
                        <div class="data-point">
                            <div class="data-point-label">Kelas dan NIS</div>
                            <p class="data-point-value mb-0">{{ $registration->student->class_name ?? '-' }} | NIS {{ $registration->student->nis ?? '-' }}</p>
                        </div>
                        <div class="data-point">
                            <div class="data-point-label">Ekstrakurikuler</div>
                            <p class="data-point-value mb-0">{{ $registration->extracurricular->name ?? '-' }}</p>
                        </div>
                        <div class="data-point">
                            <div class="data-point-label">Status saat ini</div>
                            <p class="data-point-value mb-0"><span class="badge" data-status="{{ $registration->status }}">{{ $registration->status }}</span></p>
                        </div>
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
        <div class="col-12">
            <div class="card">
                <div class="card-header">Histori Tes Bakat</div>
                <div class="card-body">
                    <div class="info-list">
                        @forelse($registration->talentTestParticipants as $participant)
                            <div class="info-item">
                                <div class="title">{{ $participant->schedule->title }}</div>
                                <div class="small text-muted mt-1">{{ optional($participant->schedule->activity_date)->format('d-m-Y') }} | {{ $participant->schedule->location }}</div>
                                <div class="small mt-2">Status hadir: <span class="badge" data-status="{{ $participant->attendance_status }}">{{ $participant->attendance_status }}</span></div>
                            </div>
                        @empty
                            <div class="empty-state py-3">
                                <p class="mb-0">Siswa ini belum dijadwalkan untuk tes bakat.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
