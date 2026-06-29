@extends('layouts.app')

@section('page_title', 'Detail Siswa')
@section('page_subtitle', $student->user->name)

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6"><p class="text-muted small mb-1">Nama</p><p class="mb-0 fw-semibold">{{ $student->user->name }}</p></div>
                <div class="col-md-6"><p class="text-muted small mb-1">NIS</p><p class="mb-0">{{ $student->nis }}</p></div>
                <div class="col-md-6"><p class="text-muted small mb-1">Kelas</p><p class="mb-0">{{ $student->class_name }}</p></div>
                <div class="col-md-6"><p class="text-muted small mb-1">Email</p><p class="mb-0">{{ $student->user->email }}</p></div>
                <div class="col-md-6"><p class="text-muted small mb-1">Jenis Kelamin</p><p class="mb-0">{{ $student->gender }}</p></div>
                <div class="col-md-6"><p class="text-muted small mb-1">Tanggal Lahir</p><p class="mb-0">{{ optional($student->date_of_birth)->format('d-m-Y') ?? '-' }}</p></div>
                <div class="col-md-6"><p class="text-muted small mb-1">Orang Tua</p><p class="mb-0">{{ $student->parent_name ?? '-' }}</p></div>
                <div class="col-md-6"><p class="text-muted small mb-1">Telepon Orang Tua</p><p class="mb-0">{{ $student->parent_phone ?? '-' }}</p></div>
                <div class="col-12"><p class="text-muted small mb-1">Alamat</p><p class="mb-0">{{ $student->address ?? '-' }}</p></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Riwayat Pendaftaran</div>
        <div class="card-body p-0">
            <ul class="list-group list-group-flush">
                @forelse($student->registrations as $registration)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>{{ $registration->extracurricular->name ?? '-' }}</span>
                        <span class="badge" data-status="{{ $registration->status }}">{{ $registration->status }}</span>
                    </li>
                @empty
                    <li class="list-group-item text-muted">Belum ada pendaftaran.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary mt-3"><i class="bi bi-arrow-left"></i>Kembali</a>
@endsection
