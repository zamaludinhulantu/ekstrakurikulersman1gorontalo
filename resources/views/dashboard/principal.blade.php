@extends('layouts.app')

@section('page_title', 'Dashboard Kepala Sekolah')
@section('page_subtitle', 'Ringkasan aktivitas sistem ekstrakurikuler')

@section('content')
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-4 col-xl-3">
            <div class="card h-100"><div class="card-body stat-card"><span class="stat-icon"><i class="bi bi-grid-1x2"></i></span><p class="label">Total Ekskul</p><p class="value">{{ $totalExtracurriculars }}</p><div class="trend">Program aktif</div></div></div>
        </div>
        <div class="col-6 col-md-4 col-xl-3">
            <div class="card h-100"><div class="card-body stat-card"><span class="stat-icon"><i class="bi bi-people"></i></span><p class="label">Total Peserta</p><p class="value">{{ $totalParticipants }}</p><div class="trend">Siswa terdaftar</div></div></div>
        </div>
        <div class="col-6 col-md-4 col-xl-3">
            <div class="card h-100"><div class="card-body stat-card"><span class="stat-icon"><i class="bi bi-check2-square"></i></span><p class="label">Total Presensi</p><p class="value">{{ $totalAttendances }}</p><div class="trend">Catatan kehadiran</div></div></div>
        </div>
        <div class="col-6 col-md-4 col-xl-3">
            <div class="card h-100"><div class="card-body stat-card"><span class="stat-icon"><i class="bi bi-award"></i></span><p class="label">Total Penilaian</p><p class="value">{{ $totalAssessments }}</p><div class="trend">Evaluasi siswa</div></div></div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header">Ringkasan Presensi</div>
                <div class="card-body">
                    <div class="info-list">
                        @foreach($attendanceSummary as $status => $total)
                            <div class="info-item d-flex justify-content-between align-items-center">
                                <div class="title text-capitalize">{{ $status }}</div>
                                <span class="badge" data-status="{{ $status }}">{{ $total }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header">Ringkasan Penilaian</div>
                <div class="card-body">
                    <div class="info-list">
                        @foreach($assessmentSummary as $status => $total)
                            <div class="info-item d-flex justify-content-between align-items-center">
                                <div class="title">{{ $status === 'achievement' ? 'Prestasi Kegiatan' : 'Penilaian Siswa' }}</div>
                                <span class="badge badge-status-secondary">{{ $total }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
